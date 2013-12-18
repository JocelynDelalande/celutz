<?php
require_once(dirname(__FILE__).'/../constants.inc.php');

//
class RefPoint {
  static $all_ref_points_cache;

  public static function load_if_needed() {
    if (!isset(self::$all_ref_points_cache)) {
      $ref_points_filename = '../ref_points.local.php';
	  if (file_exists($ref_points_filename)) {
	    require($ref_points_filename);
        self::$all_ref_points_cache = $ref_points;
	    return $ref_points;
	  } else {
	    return array();
      }
    }
   return self::$all_ref_points_cache;
  }

  public static function get($name) {
    self::load_if_needed();
    return self::$all_ref_points_cache[$name];
  }

}
?>
