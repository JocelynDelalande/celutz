<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <?php
   require 'class/site_point.class.php';
   require 'class/sites_dir.class.php';

   if (isset($_GET['dir']) && isset($_GET['panorama'])) {
     $dir   = $_GET['dir'];
     $name  = $_GET['panorama'];
   } else {
     $dir   = 'tiles';
     $name  = 'ttn_mediatheque';
   }
   $opt_vals = array();
   foreach(array('to_cap', 'to_ele', 'to_zoom') as $val) {
     if (!empty($_GET[$val])) $opt_vals[$val] = $_GET[$val];
   }

   $base_dir = $dir.'/'.$name;
   $pt = new site_point($base_dir);
   if(!$pt) die("impossible d'accéder à ".$base_dir." !\n");
   $params = $pt->get_params();
   $prefix = $pt->get_prefix();
  ?>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
   <?php
     $titre = 'panorama';
     if ($params && isset($params['titre'])) $titre .= ' ; '.$params['titre'];
     printf ("<title>%s</title>\n", $params['titre']);
  ?>
  <script>
   <?php
     printf ("var img_prefix = '%s/%s'\n", $base_dir, $prefix);
     if (is_array($params)) $opt_vals = array_merge($params, $opt_vals);
     foreach(array('to_cap', 'to_ele', 'to_zoom', 'image_loop') as $val) {
       if (isset($opt_vals[$val])) printf ('var '.$val.' = '.$opt_vals[$val].";\n"); // correction du décalage angulaire par rapport au Nord
     }
  ?>
  </script>
  <script src="js/pano.js"></script>
  <script>
  <?php
     $zoom_array = $pt->get_magnifications();
     foreach($zoom_array as $zoom => $val) {
       echo "zooms[$zoom] = new tzoom($zoom);\n";
       echo "zooms[$zoom].ntiles.x = ".$val['nx'].";\n";
       echo "zooms[$zoom].ntiles.y = ".$val['ny'].";\n";
       $size = getimagesize(sprintf($base_dir.'/'.$prefix.'_%03d_%03d_%03d.jpg', $zoom, 0, 0));
       echo "zooms[$zoom].tile.width = ".$size[0].";\n";
       echo "zooms[$zoom].tile.height = ".$size[1].";\n";
       $size = getimagesize(sprintf($base_dir.'/'.$prefix.'_%03d_%03d_%03d.jpg', $zoom, $val['nx']-1, $val['ny']-1));
       echo "zooms[$zoom].last_tile.width = ".$size[0].";\n";
       echo "zooms[$zoom].last_tile.height = ".$size[1].";\n";
     }

   $dir_list = new sites_dir($dir);

   $ipt = 0;
   $scrname = getenv('SCRIPT_NAME');
   foreach($dir_list->get_sites() as $opt) {
     $prm = $opt->get_params();
     $oname = $opt->get_name();
     if ($oname != $name && isset($prm['latitude']) && isset($prm['longitude']) && isset($prm['altitude']) && isset($prm['titre'])) {
       list($dist, $cap, $ele) = $pt->coordsToCap($prm['latitude'], $prm['longitude'], $prm['altitude']);
       $lnk = sprintf("%s?dir=%s&panorama=%s&to_cap=%.3f&to_ele=%.3f", $scrname, $dir, $oname, $cap + 180, -$ele);
       printf('point_list[%d] = new Array("%s", %03lf, %03lf, %03lf, "%s");'."\n", $ipt++, $prm['titre'], $dist, $cap, $ele, $lnk);
     }
   }

   include 'ref_points.php';
   $extra_names = array();
   $ref_names = array();
   if (is_array($ref_points)) {
     foreach ($ref_points as $name => $vals) {
       $extra_names[] = $name;
       list($dist, $cap, $ele) = $pt->coordsToCap($vals[0], $vals[1], $vals[2]);
       $ref_names[$name] = array($dist, $cap, $ele);
       printf('point_list[%d] = new Array("%s", %03lf, %03lf, %03lf, "");'."\n", $ipt++, $name, $dist, $cap, $ele);
     }
   }

   if (isset($params['reference'])) {
     echo "ref_points = new Array();\n";
     foreach ($params['reference'] as $nm => $val) {
       if (isset($ref_names[$nm])) {
	 list($dist, $cap, $ele) = $ref_names[$nm];
	 list($px, $py) = explode(',', $val);
	 printf("ref_points[\"%s\"] = {x:%.5f, cap:%.5f, y:%.5f, ele:%.5f};\n", $nm, $px, $cap, $py, $ele);
       }
     }
   }
  ?>
  </script>
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <link rel="stylesheet" media="screen" href="css/all.css" />
</head>
<body>
  <canvas id="mon-canvas">
    Ce message indique que ce navigateurs est vétuste car il ne supporte pas <samp>canvas</samp> (IE6, IE7, IE8, ...)
  </canvas>
  <fieldset id="control"><legend>contrôle</legend>
      <label>Zoom : <input type="range" min="0" max="2" value="2" id="zoom_ctrl"/></label>
      <label>Cap : <input type="number" min="0" max="360" step="10" value="0" autofocus="" id="angle_ctrl"/></label>
      <label>Élévation : <input type="number" min="-90" max="90" step="1" value="0" autofocus="" id="elvtn_ctrl"/></label>
  </fieldset>

  <?php
      //phpinfo();exit;
     if ($params && isset($params['latitude']) && isset($params['longitude'])) {
       print("<div id=\"params\">\n");
       printf ("<p>latitude :   <em>%.3f°</em></p>\n", $params['latitude']);
       printf ("<p>longitude : <em>%.3f°</em></p>\n", $params['longitude']);
       if (isset($params['altitude'])) printf ("<p>altitude : <em>%d m</em></p>\n", $params['altitude']);
       print("</div>\n");
     }
     echo '<p id="info"></p>'."\n";
     if (count($extra_names) > 1) {
       echo "<p id=\"insert\">\n<select id=\"sel_point\" name=\"known_points\">\n";
       foreach ($extra_names as $nm) {
	 echo '<option>'.$nm."</option>\n";
       }
       echo "</select>\n<br/>";
       echo "<input type=\"button\" id=\"do-insert\" value=\"insérer\"/>\n";
       echo "<input type=\"button\" id=\"do-delete\" value=\"suppimer\"/>\n";
       echo "<input type=\"button\" id=\"do-cancel\" value=\"annuler\"/>\n";
       echo "</p>\n";
     }
  ?> 
  <p class="validators"><a href="http://validator.w3.org/check?uri=referer">page xHTML validé !</a></p>
  <p id="res"></p>
</body>
</html>
