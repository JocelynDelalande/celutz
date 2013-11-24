<head>
  <title>Visualisation axe horizontal sur OSM</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <link rel="stylesheet" type="text/css" href="css/layers.css" />
<?php
if (isset($_REQUEST['cap']) && isset($_REQUEST['org_lat']) && isset($_REQUEST['org_lon'])) {
  $cap = $_REQUEST['cap'];
  $org_lat = $_REQUEST['org_lat'];
  $org_lon = $_REQUEST['org_lon'];
  $complete = true;
} else {
  $complete = false;
}
if (isset($_REQUEST['title'])) {
  $pt_comment = htmlentities(base64_decode($_REQUEST['title']), ENT_QUOTES);
} else {
  $pt_comment = 'Le point de départ';
}
if (isset($_REQUEST['dist'])) {
  $dist = $_REQUEST['dist'];
} else {
  $dist = 120000;
}
if ($complete) {
  echo <<< EOS
<script src="http://maps.google.com/maps/api/js?v=3&amp;sensor=false"></script>
<script src="http://openlayers.org/api/OpenLayers.js"></script>
    <script>
    zoom = 12;
  var get_lon_lat = false;
  var scale_line = true;

  var def_points_style = {
  showPopup: false,
  icon_width: 24,
  icon_height: 24,
  icon_shiftX: -12,
  icon_shiftY: -24,
  opacity: 0.7}

  var ref_point = {
  lon: $org_lon,
  lat: $org_lat,
  icon_url: 'images/ptref.png',
  descr: '<div id="bulle">$pt_comment</div>',
  showPopup: true,
  icon_width: 24,
  icon_height: 24,
  icon_shiftX: -12,
  icon_shiftY: -24,
  title: 'chez nous'
};

var ref_line = {
 lon1: $org_lon,
 lat1: $org_lat,
 cap: $cap,
 width: 2,
 length: $dist,
 color: '#F00'
};
var base_layers = [
		   new OpenLayers.Layer.OSM(),
		   new OpenLayers.Layer.Google(
					       "Google Satellite",
  {type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
					       ),
		   new OpenLayers.Layer.Google(
					       "Google Relief",
  {type: google.maps.MapTypeId.TERRAIN, visibility: false}
					       ),
		   new OpenLayers.Layer.Google(
					       "Google plan",
  {numZoomLevels: 20, visibility: false}
					       ),
		   new OpenLayers.Layer.Google(
					       "Google Hybrid",
  {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 22, visibility: false}
					       )];

</script>
<script src="js/utils_osm.js">
</script>
EOS;
}
?>
<script>
if (typeof addLoadEvent == 'function') addLoadEvent(draw_cap_map);
else window.onload = draw_cap_map;
</script>
</head>
<body>
<?php
if ($complete) {
  echo '<div id="map"></div>'."\n";
  echo '<div id="panel"></div>'."\n";
  echo '<div id="position"></div>'."\n";
  echo '<div id="extra">'."\n";
  echo '<p>Autres contrôles'."\n";
  echo '<label><input type="checkbox" id="tilt" checked="checked"/>vision à 45°</label>'."\n";
  echo '<label><input type="checkbox" id="clic_pos"/>Position afichée sur clic</label>'."\n";
  echo '</div>'."\n";
} else {
  echo "<h1>Il faut indiquer des coordonnées.</h1>\n";
}
?>
</body>
</html>
