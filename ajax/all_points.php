<?php
require_once('../class/sites_dir.class.php');
require_once('../class/site_point.class.php');

function get_ref_points() {
	$ref_points_filename = '../ref_points.local.php';
	if (file_exists($ref_points_filename)) {
		include $ref_points_filename;
		return $ref_points;
	} else {
		return array();
	}
}


function ref_point_to_geoJSONFeature($name, $values) {
	return array("type" => "Feature",
	             "geometry" => array(
	                                 "type" => "Point",
	                                 "coordinates" => [$values[1],$values[0]]
	                                 ),
	             "properties" => array("name" => $name, "type" => 'loc_point')
	             );
}


function get_site_points() {
	$dir = "../tiles";//FIXME
	return (new sites_dir($dir))->get_sites();
}


function site_point_to_geoJSONFeature($sp) {
	$prm = $sp->get_params();
	$name = $sp->get_name();
	$lat = floatval($prm['latitude']);
	$lon = floatval($prm['longitude']);
	//$alt = $prm['altitude'];
	//$title = $prm['titre'];

	return array("type" => "Feature",
	             "geometry" => array(
	                                 "type" => "Point",
	                                 "coordinates" => [$lon, $lat]
	                                 ),
	             "properties" => array("name" => $name,
	                                   "type" => 'pano_point',
	                                   "view_url"  => $sp->get_url())
	             );
}


$json = array(
              "type" => "FeatureCollection",
              "features"=> array()
              );

foreach (get_ref_points() as $name => $vals) {
	$json['features'][] = ref_point_to_geoJSONFeature($name, $vals);
}


foreach(get_site_points() as $site_point) {
	$json['features'][] = site_point_to_geoJSONFeature($site_point);
}

echo json_encode($json);
?>
