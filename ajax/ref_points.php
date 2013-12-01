<?php


/**
 *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any
 *  origin.
 *
 *  In a production environment, you probably want to be more restrictive, but this gives you
 *  the general idea of what is involved.  For the nitty-gritty low-down, read:
 *
 *  - https://developer.mozilla.org/en/HTTP_access_control
 *  - http://www.w3.org/TR/cors/
 *
 */
function cors() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

cors();

function ref_point_to_geoJSONFeature($name, $values) {
	return array("type" => "Feature",
	             "geometry" => array(
	                                 "type" => "Point",
	                                 "coordinates" => [$values[1],$values[0]]
	                                 ),
	             "properties" => array("name" => $name)
	             );
}

function get_ref_points() {
	$ref_points_filename = '../ref_points.local.php';
	if (file_exists($ref_points_filename)) {
		include $ref_points_filename;
		return $ref_points;
	} else {
		return array();
	}
}

$json = array(
              "type" => "FeatureCollection",
              "features"=> array()
              );

foreach (get_ref_points() as $name => $vals) {
	$json['features'][] = ref_point_to_geoJSONFeature($name, $vals);
}

echo json_encode($json);

?>
