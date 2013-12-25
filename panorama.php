<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <?php
   require 'class/utils.class.php';
   require_once 'constants.inc.php';

  $fields_spec = array(
    'panorama'   => array('basename'),
    'dir'        => array(),//fixme
    'to_cap'     => array('numeric'),
    'to_ele'     => array('numeric'),
    'to_zoom'     => array('numeric')
  );
  
  $validator = new FormValidator($fields_spec);
  $is_valid = $validator->validate($_GET);
  
  if ($is_valid) {
    $input = $validator->sane_values();
  } else {
    $validator->print_errors();
    die();//fixme, could be cleaner
  }
  
   $form_extpoint = file_get_contents('html/form_extpoint.html');

   $form_param = file_get_contents('html/form_param.html');

   if (isset($input['dir']) && isset($input['panorama'])) {
     $dir   = $input['dir'];
     $name  = $input['panorama'];
   } else {
     $dir   = PANORAMA_PATH;
     $name  = 'ttn_mediatheque';
   }
   $opt_vals = array();
   foreach(array('to_cap', 'to_ele', 'to_zoom') as $val) {
     if (!empty($input[$val])) $opt_vals[$val] = $input[$val];
   }

   $pt = site_point::get($input['panorama']);
   $base_dir = $pt->tiles_url_prefix();
   if(!$pt) die("impossible d'accéder à ".$base_dir." !\n");
   $params = $pt->get_params();
   $prefix = $pt->get_prefix();
  ?>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <?php
     $titre = 'panorama';
     if ($params && isset($params['titre'])) $titre .= ' : '.$params['titre'];
     printf ("<title>%s</title>\n", $titre);
  ?>
  <script>
   <?php
     printf ("var title = \"%s\";\n", $titre);
     printf ("var img_prefix = '%s/%s';\n", $base_dir, $prefix);
     if (is_array($params)) $opt_vals = array_merge($params, $opt_vals);
     foreach(array('to_cap', 'to_ele', 'to_zoom', 'image_loop') as $val) {
        if (isset($opt_vals[$val])) 
        printf ('var '.$val.' = '.utils::php2ini($opt_vals[$val]).";\n"); // correction du décalage angulaire par rapport au Nord
     }
  ?>
  </script>
  <script src="js/pano.js"></script>
  <script>window.onload = load_pano</script>
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
  foreach(site_point::get_all() as $opt) {
     $prm = $opt->get_params();
     $oname = $opt->get_name();
     if (($oname != $name) && $opt->has_params()) {
       list($dist, $cap, $ele) = $pt->coordsToCap($prm['latitude'], $prm['longitude'], $prm['altitude']);
       // Looks back at the point from which we come.
       $lnk = $opt->get_url($cap + 180, -$ele);
       printf('point_list[%d] = new Array("%s", %03lf, %03lf, %03lf, "%s");'."\n", $ipt++, $prm['titre'], $dist, $cap, $ele, $lnk);
     }
   }

   $ref_points = array ();
   $ref_points_filename = 'ref_points.local.php';
   if (file_exists($ref_points_filename)) {
     include $ref_points_filename;
   }
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
	 list($px, $py) = $val;
	 printf("ref_points[\"%s\"] = {x:%.5f, cap:%.5f, y:%.5f, ele:%.5f};\n", $nm, $px, $cap, $py, $ele);
       }
     }
   }

   $localLat = (isset($_POST["loca_latitude"])) ? $_POST["loca_latitude"] : NULL;
   $localLon = (isset($_POST["loca_longitude"])) ? $_POST["loca_longitude"] : NULL;
   $localAlt = (isset($_POST["loca_altitude"])) ? $_POST["loca_altitude"] : NULL;

   if ($localLat && $localLon && $localAlt) {
     list($localDistance, $localCap, $localEle) = $pt->coordsToCap($localLat, $localLon, $localAlt);
     $n = "point temporaire";
     printf('point_list[%d] = new Array("%s", %03lf, %03lf, %03lf, "temporary");'."\n",$ipt++, $n, $localDistance, $localCap, $localEle);
   }
  ?>
  </script>
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <link rel="stylesheet" media="screen" href="css/map.css" />
  <script src="js/hide_n_showForm.js"></script>
</head>
<body>
  <canvas id="mon-canvas">
    Ce message indique que ce navigateur est vétuste car il ne supporte pas <samp>canvas</samp> (IE6, IE7, IE8, ...)
  </canvas>

  <fieldset id="control"><legend>contrôle</legend>
      <label>Zoom : <input type="range" min="0" max="2" value="2" id="zoom_ctrl"/></label>
      <label>Cap : <input type="number" min="0" max="360" step="10" value="0" autofocus="" id="angle_ctrl"/></label>
      <label>Élévation : <input type="number" min="-90" max="90" step="1" value="0" autofocus="" id="elvtn_ctrl"/></label>
  </fieldset>

  <?php

     if ($params && isset($params['latitude']) && isset($params['longitude'])) {
       print("<div id=\"params\">\n");
       printf ("<p>latitude :   <em><span id=\"pos_lat\">%.5f</span>°</em></p>\n", $params['latitude']);
       printf ("<p>longitude : <em><span id=\"pos_lon\">%.5f</span>°</em></p>\n", $params['longitude']);
       if (isset($params['altitude'])) printf ("<p>altitude : <em><span id=\"pos_alt\">%d</span> m</em></p>\n", $params['altitude']);
       print("</div>\n");
       echo $form_extpoint;
     } elseif ($params == false ) {
     	$dir   = $input['dir'];
        $name  = $input['panorama'];
        printf($form_param, $name, $name);
     }
     echo '<p id="info"></p>'."\n";

     echo "<p id=\"insert\">";
     if (count($extra_names) > 1) {
       echo "<select id=\"sel_point\" name=\"known_points\">\n";
       foreach ($extra_names as $nm) {
	     echo '<option>'.$nm."</option>\n";
       }
       echo "</select>\n";
       echo "<input type=\"button\" id=\"do-insert\" value=\"insérer\"/>\n";
       echo "<input type=\"button\" id=\"do-delete\" value=\"suppimer\"/>\n";
       echo "<input type=\"button\" id=\"show-cap\" value=\"visualiser cet axe sur OSM\"/>\n";
     } else {
       echo "Pas de point de reférénce connu, lisez le <em>README.md</em> pour en ajouter. \n";
     }
     echo "<input type=\"button\" id=\"do-cancel\" value=\"annuler\"/>\n";
     echo "</p>\n";
  ?>
  <p id="res"></p>
  <div class="validators">
    page validée par
       <a href="http://validator.w3.org/check?uri=referer"><img src="images/valid_xhtml.svg" alt="Valid XHTML" title="xHTML validé !"/></a>
       <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="images/valid_css.svg" alt="CSS validé !" title="CSS validé !"/></a>
  </div>
</body>
</html>
