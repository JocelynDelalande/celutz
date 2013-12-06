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
}

?>
