<?php
require_once(dirname(__FILE__).'/../constants.inc.php');
require_once(dirname(__FILE__).'/utils.class.php');
//
class PanoramaFormatException extends Exception {
	/** If the files organization is not correct for a panorama, we can't let it go...
	 */
}

class site_point {
  /** Defines a point, with a panorama
  */
  private $base_dir;        // dir of tiles for that panorama
  private $name = false;
  private $prefix = false;
  private $params = false;
  private $zooms;

  public static $REF_KEY = 'reference';

  public function __construct($dir) {
    $this->base_dir = $dir;
    $this->prefix = basename($dir);
  }

  public function params_path() {
	  return $this->base_dir.'/'.$this->prefix.'.params';
  }

  public function tiles_path() {
	  return $this->base_dir;
  }

  public function tiles_prefix() {
	  return $this->base_dir.'/'.$this->get_prefix();
  }

  private function parse_and_cache_params() {
    if (is_file($this->params_path())) {
	    $params = parse_ini_file($this->params_path());
	    if ($params) {
		    $this->params = $params;
		    if (isset($params[self::$REF_KEY])) {
			    foreach ($params[self::$REF_KEY] as $ref => $vals) {
				    $bits = explode(',',$vals);
				    $this->params[self::$REF_KEY][$ref] = array(floatval($bits[0]),
				                                                floatval($bits[1]));
			    }
		    }
		    if (isset($params['image_loop'])) {
			    $this->params['image_loop'] = (bool)($params['image_loop']);
		    }
		    return $this->params;
	    }
    }
    return array();
  }

  public function get_params() {
	  // the params are cached
	  if (isset($this->params) && $this->params) {
		  return $this->params;
	  } else {
		  return $this->parse_and_cache_params();
	  }
  }

  public function save_params() {
	  $o = '';
	  $p = $this->get_params();
	  foreach ($this->get_params() as $k => $v) {
		  if ($k == self::$REF_KEY) {
			  foreach ($v as $refk => $refv) {
				  $o.= sprintf("%s[\"%s\"] = %.5f,%.5f\n",
				               self::$REF_KEY, $refk,
				               $refv[0], $refv[1]);
			  }
		  } else {
			  $o.= "$k = ".utils::php2ini($v)."\n";
		  }
	  }
	  file_put_contents($this->params_path(), $o);
  }

  public function set_param($key, $value) {
	  $p = $this->get_params();
	  $this->params[$key] = $value;
	  if ($key == 'titre') {
		  $this->name = $value;
	  }
  }

  public function has_params(){
	  $p = $this->get_params();
	  return (isset($p['latitude'], $p['longitude'],
	                $p['altitude'], $p['titre']));
  }

  public function get_name() {
    return basename($this->base_dir);
  }

  public function get_prefix() {
    return $this->prefix;
  }

  public function get_magnifications() {
    $dir_fd = opendir($this->base_dir);
    $zoom_array = array();
    while (false !== ($file = readdir($dir_fd))) {                // extraction des paramètres de grossissement par le serveur
       //echo $file;
       if (preg_match('/(.*)_([0-9]+)_([0-9]+)_([0-9]+)\.jpg$/', $file, $reg)) {
	 $prefix = $reg[1];
	 if ($prefix == $this->prefix) {
	   $zoom = (int)$reg[2];
	   $posx = (int)$reg[3]+1;
	   $posy = (int)$reg[4]+1;
	   if (!isset($zoom_array[$zoom]['nx']) || $zoom_array[$zoom]['nx'] < $posx) $zoom_array[$zoom]['nx'] = $posx;
	   if (!isset($zoom_array[$zoom]['ny']) || $zoom_array[$zoom]['ny'] < $posy) $zoom_array[$zoom]['ny'] = $posy;
	 }
       }
    }
    $this->zooms = $zoom_array;
    return $this->zooms;
  }

  public function coordsToCap($lat, $lon, $alt) {
    $params = $this->get_params();
    if (!isset($params['latitude']) || !isset($params['longitude'])) return false;
    $rt = 6371;  // Rayon de la terre
    $alt1 = isset($params['altitude']) ? $params['altitude'] : $alt;
    $lat1 = $params['latitude']*M_PI/180;
    $lon1 = $params['longitude']*M_PI/180;
    $alt2 = $alt;
    $lat2 = $lat * M_PI/180;
    $lon2 = $lon * M_PI/180;

    $dLat = $lat2-$lat1;
    $dLon = $lon2-$lon1;

    $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);  //
    $angle = 2 * atan2(sqrt($a), sqrt(1-$a));
    $d = $angle * $rt;                    // distance du point en Kms

    $y = sin($dLon)*cos($lat2);
    $x = cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($dLon);
    $cap = atan2($y, $x);                 // cap pour atteindre le point en radians

    $e = atan2(($alt2 - $alt1)/1000 - $d*$d/(2*$rt), $d);  // angle de l'élévation en radians
    //    printf("%s, %s, %s, %s\n",$lat1, $params['latitude'], $lat, $dLat);
    return array($d, $cap*180/M_PI, $e*180/M_PI);   // les résultats sont en degrés
  }

  public function get_url($cap=false, $ele=false) {
	  $o = sprintf('panorama.php?dir=%s&panorama=%s',
	                  PANORAMA_FOLDER, $this->get_name());
	  if ($cap && $ele) {
		  $o .= sprintf("&to_cap=%.3f&to_ele=%.3f", $cap, $ele);
	  }
	  return $o;
  }

  public function set_reference($ref_point, $x, $y) {
	  /**
	   * Registers (for saving) the position of a reference point within a
	   * panorama. It sets or overwrite a reference.
	   *
	   * @param $ref_point a RefPoint instance
	   * @param $x the relative x position of the RefPoint
	   * @param $x the relative y position of the RefPoint
	   */
	  $p = $this->get_params();

	  if (!isset($this->params[self::$REF_KEY]) ||
	      !is_array($this->params[self::$REF_KEY])) {
		  $this->params[self::$REF_KEY] = array();
	  }
	  $ref_name = $ref_point->name;
	  $dict = $this->params[self::$REF_KEY];
	  //$dddd = $this->params[self::$REF_KEY][$ref_name];
	  $this->params[self::$REF_KEY][$ref_name] = array($x, $y);
  }

  public function unset_reference($ref_point) {
	  /**
	   * Unregisters a reference, within a panorama.
	   * does nothing if the RefPoint is not registered.
	   *
	   * @param $ref_point a RefPoint instance
	   */
	  $p = $this->get_params();
	  $ref_name = $ref_point->name;
	  if (isset($p[self::$REF_KEY]) &&
	      isset($p[self::$REF_KEY][$ref_name])) {
		  unset($this->params[self::$REF_KEY][$ref_name]);
	  }
  }



  public static function get($name) {
	  /** Instantiate a site_point, given its name
	   */
	  $pano_dir = PANORAMA_PATH.'/'.$name;
	  return new site_point($pano_dir);
  }

  public static function create($filepath) {
	  /** creates a new panorama, given its name, from an uploaded file.
	   */
	  $name = utils::strip_extension(basename($filepath));
	  $pano_dir = PANORAMA_PATH.'/'.$name;
	  $pano = new site_point($pano_dir);
	  if (!mkdir($pano->tiles_path())) {
		  return false;
	  } else {
		  return $pano;
	  }
  }

  public function to_geoJSON() {
	  $prm = $this->get_params();
		$name = $this->get_name();
		$lat = floatval($prm['latitude']);
		$lon = floatval($prm['longitude']);
		//$alt = $prm['altitude'];
		//$title = $prm['titre'];

		return array("type" => "Feature",
		             "geometry" => array(
		                                 "type" => "Point",
		                                 "coordinates" => array($lon, $lat)
		                                 ),
		             "properties" => array("name" => $name,
		                                   "type" => 'pano_point',
		                                   "view_url"  => $this->get_url())
		             );
  }


  public static function get_all($only_with_params=false) {
	  /**
	   * @param $only_with_params : filters out the panoramas which
	   *        are not parametrized
	   */
	  $panos = array_diff(scandir(PANORAMA_PATH), array('..', '.'));
	  $pano_instances = array();

	  foreach ($panos as $pano_name) {
		  $pano =  site_point::get($pano_name);
		  if (! $only_with_params || $pano->has_params() ) {
			  $pano_instances[] = $pano;
		  }
	  }
	  return $pano_instances;
  }

}
