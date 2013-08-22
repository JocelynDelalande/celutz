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
  <link rel="stylesheet" media="screen" href="css/all.css" />
  <script src="js/hide_n_showForm.js"></script> 
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
      
     if ($params && isset($params['latitude']) && isset($params['longitude'])) {
       print("<div id=\"params\">\n");
       printf ("<p>latitude :   <em><span id=\"pos_lat\">%.5f</span>°</em></p>\n", $params['latitude']);
       printf ("<p>longitude : <em><span id=\"pos_lon\">%.5f</span>°</em></p>\n", $params['longitude']);
       if (isset($params['altitude'])) printf ("<p>altitude : <em><span id=\"pos_alt\">%d</span> m</em></p>\n", $params['altitude']); 
       print("</div>\n");
       ?>
       <span id="loca"><img src="images/locapoint.svg" id="icone" onclick="showLoca()"/></span>
  <fieldset id="locadraw"><legend id="" onclick="hideLoca()">Localiser un point</legend>
    <form id="form_loca" method="post" name="form_localate" action="panorama.php?dir=<?php echo $_GET['dir'];?>&amp;panorama=<?php echo $_GET['panorama'];?>">
      <label class="form_col" title="La latitude ϵ [-90°, 90°]. Ex: 12.55257">Latitude :
	<input  name="loca_latitude" type="number" min="-90" max="90"  id="loca_latitude"/></label>
	         
      <label class="form_col" title="La longitude ϵ [-180°, 180°]. Ex: 144.14723">Longitude :
	<input name="loca_longitude" type="number" min="-180" max="180" id="loca_longitude"/></label>
	      
      <label class="form_col" title="L'altitude positive Ex: 170">Altitude :
	<input  name="loca_altitude" type="number" min="-400" id="loca_altitude"/></label>
	       
      <div class="answer">
	<input type="button" value="Localiser" id="loca_button" class="form_button"/> 
	<input type="reset" value="Reset" class="form_button"/>
	<input type="button" value="Effacer" class="form_button" id="loca_erase"/>
      </div>
	      
    </form>
  </fieldset>
       <?php
     } elseif ($params == false ){
     	$dir   = $_GET['dir'];
        $name  = $_GET['panorama'];
     	?>
       <div id="addParams">		
     	  <label onclick="showForm()" value="Hide label">Paramétrer le panorama</label>	
       </div>
       <fieldset id="adding"><legend id="lgd" onclick="hideForm()">Paramètrer le panorama</legend>
	 <form action="addParams.php?dir=<?php echo $dir;?>&amp;panorama=<?php echo $name;?>" id="form_param" method="post">
	   	  
	   <label class="form_col" for="param_title" title="Au moins 4 caractères">Titre: </label>
	   <input type="text" id="param_title" name="param_title"/>
	   
	   <label class="form_col" for="param_latitude" title="La latitude ϵ [-90°, 90°]. Ex: 12.55257">Latitude: </label>
	   <input  name="param_latitude" type="text" id="param_latitude" />
	         
	   <label class="form_col" for="param_longitude" title="La longitude ϵ [-180°, 180°]. Ex: 144.14723">Longitude: </label>
	   <input name="param_longitude" type="text" id="param_longitude" />
	   
	   <label class="form_col" for="param_altitude" title="L'altitude positive Ex: 170">Altitude: </label>
	   <input  name="param_altitude" type="text" id="param_altitude" />
	  
	   <label class="form_col" for="param_elevation" title="élévation ϵ [-10,10] ( valeur par défaut : 0)">Elévation: </label>
	   <input  name="param_elevation" type="text" id="param_elevation" />
	   
	   <label class="form_col" for="param_loop" title="L'image fait elle 360° ? ">Rebouclage: </label>
	   <input class="radio" type="radio" name="param_loop" value="true" checked="checked"> Oui
           <input class="radio" type="radio" name="param_loop" value="false"> Non
	   
	   <input type="hidden" value="Localiser" id="loca_button" class="form_button" style="width:70px" /> 
	   <input type="hidden" value="Effacer" class="form_button" id="loca_erase"/>
	  
	   <div class="answer">
	     <input type="submit" value="Submit" class="form_button"/> 
	     <input type="reset" value="Reset" class="form_button"/>
	   </div>
	      
  	 </form>
       </fieldset>
  
 
       <script src="js/pano_deroulant.js"></script> 
      
     	<?php
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
