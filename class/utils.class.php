<?php

abstract class utils {
  static public function init() {
    function __autoload($class) {
      $class_loc = 'class/'.$class.'.class.php';
      if (is_readable($class_loc)) {
	require_once($class_loc);
      }
    }

    function errorToException($code, $msg, $file, $line) {
      throw new Exception($msg);
    }
    set_error_handler('errorToException');
  }

  static public function list_available_panos($base_dir) {
	  /** Lists all that can be turned into a panorama
	   */
	  $dir = opendir($base_dir);
	  $ret = array();
	  $finfo = finfo_open(FILEINFO_MIME_TYPE); // Retourne le type mime du fichier

	  while(false !== ($filename = readdir($dir))) {
		  if (!preg_match('/^\.\.?$/', $filename)) {
			  $ftype = finfo_file($finfo, $base_dir.'/'.$filename);
			  if (isset($ftype)) {
				  $pano = array(
				    'comment' =>  $filename,
				    'title' => sprintf('fichier de type %s', $ftype)
				  );
			  } else {
				  $pano = array(
				    'comment' =>  sprintf('<samp>%s</samp>', $filename),
				    'title' => ''
				  );
			  }
			  $pano['filename'] = $filename;
			  $ret[] = $pano;
		  }
	  }
	  return $ret;
  }
  public static function strip_extension($filename) {
	  /** Removes the extension from a file name
	   * @return the stripped name
	   */
	  return preg_replace('/\.[^.]+$/', '', $filename);
  }

  public static function php2ini($v) {
	  /** convert php var to a string representing it in an ini file.
	   * @return a string, ready to be inserted into a ini file.
	   */
    if (is_numeric($v)) {
      return $v;
    }
    $type = gettype($v);
    switch($type) {
      case 'boolean': return $v ? "true" : "false";
      default: return '"'.$v.'"';
    }
    return $v;
  }

  public static function get_unique_filepath($path) {
    /** To handle uploads with same name : for a given path, suffix it with _<n>
    (keeping trailing extension)
    * till it find a non-preexistant_path and returns it.
    */
    if (file_exists($path)) {
      $info = pathinfo($path);
      $extension = $info['extension'];
      $remain = self::strip_extension($path);
      $n = 0;
      do {
        $n++;
        $fn = sprintf('%s_%d.%s', $remain, $n, $extension);
      } while (file_exists($fn));
      return $fn;

    } else {
      return $path;
    }
  }

}

?>
