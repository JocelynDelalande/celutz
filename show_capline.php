<head>
  <title>Visualisation axe horizontal sur OSM</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <link rel="stylesheet" type="text/css" href="style.css" />
<?php
   if (isset($_REQUEST['cap']) && isset($_REQUEST['org_lat']) && isset($_REQUEST['org_lon'])) {
     $cap = $_REQUEST['cap'];
     $org_lat = $_REQUEST['org_lat'];
     $org_lon = $_REQUEST['org_lon'];
     $complete = true;
   } else {
     $complete = false;
   }
$pt_comment = 'Le point de départ';
if ($complete) {
  echo <<< EOS
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
  length: 120000,
  color: '#F00'
  };
  </script>
  <script src="http://openlayers.org/api/OpenLayers.js"></script>
  <script src="js/utils_osm.js"></script>
EOS;
 }
?>
</head>
<body>
<?php
if ($complete) {
  echo '<div id="map"></div>'."\n";
 } else {
  echo "<h1>Il faut indiquer des coordonnées.</h1>\n";
 }
?>
</body>
</html>
