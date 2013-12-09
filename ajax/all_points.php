<?php
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


$json = array(
              "type" => "FeatureCollection",
              "features"=> array()
              );

foreach (get_ref_points() as $name => $vals) {
	$json['features'][] = ref_point_to_geoJSONFeature($name, $vals);
}


foreach(site_point::get_all(true) as $site_point) {
	$json['features'][] = $site_point->to_geoJSON();
}

echo json_encode($json);
?>
