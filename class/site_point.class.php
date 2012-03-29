<?php
class site_point {
  private $base_dir;
  private $name = false;
  private $prefix = false;
  private $params = false;
  private $zooms;

  public function __construct($dir) {
    $this->base_dir = $dir;
    $dir_fd = opendir($this->base_dir);
    while (false !== ($file = readdir($dir_fd))) {
       if (preg_match('/(.*)_[0-9]+_[0-9]+_[0-9]+\.jpg$/', $file, $reg)) {
	 $this->prefix = $reg[1];
	 break;
       }
    }
    closedir($dir_fd);
    if ($this->prefix === false) return false;
    $pfname = $this->base_dir.'/'.$this->prefix.'.params';
    if (is_file($pfname)) {
      $this->params = @parse_ini_file($pfname);
    }
  }
  
  public function get_params() {
    return $this->params;
  }
  
  public function get_name() {
    return basename($this->base_dir);
  }

  public function get_prefix() {
    return $this->prefix;
  }
  
  public function get_magnifications() {
    $dir_fd = opendir($this->base_dir);
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
    if (!isset($this->params['latitude']) || !isset($this->params['longitude'])) return false;
    $rt = 6371;  // Rayon de la terre
    $alt1 = isset($this->params['altitude']) ? $this->params['altitude'] : $alt;
    $lat1 = $this->params['latitude']*M_PI/180;
    $lon1 = $this->params['longitude']*M_PI/180;
    $alt2 = $alt;
    $lat2 = $lat * M_PI/180;
    $lon2 = $lon * M_PI/180;

    $dLat = $lat2-$lat1;
    $dLon = $lon2-$lon1;
    
    $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2); 
    $angle = 2 * atan2(sqrt($a), sqrt(1-$a));
    $d = $angle * $rt;                    // distance du point en Kms
    
    $y = sin($dLon)*cos($lat2);
    $x = cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($dLon);
    $cap = atan2($y, $x);                 // cap pour atteindre le point en radians

    $e = atan2(($alt2 - $alt1)/1000 - $d*$d/(2*$rt), $d);  // angle de l'élévation en radians
    //    printf("%s, %s, %s, %s\n",$lat1, $this->params['latitude'], $lat, $dLat);

    return array($d, $cap*180/M_PI, $e*180/M_PI);   // les résultats sont en degrés
  }
  
}
