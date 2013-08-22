<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <title>Test des classes</title>
  <link rel="stylesheet" media="screen" href="css/all.css" />
  <script src="js/pano.js"></script>
</head>
<body>
  <h1>Test des classes</h1>   
  <?php
   require 'site_point.class.php';
   require 'sites_dir.class.php';

   $base_dir = '../tiles';
   $dir = new sites_dir($base_dir);

   foreach($dir->get_sites() as $pt) {
     printf("<h2>Nom : <em>%s</em></h2>\n", $pt->get_name());
     echo '<pre>';
     print_r($pt->get_params());
     print_r($pt->get_magnifications());
     echo '</pre>';
     $lat = 43.61034;
     $lon = 1.45553;
     $alt = 200;
     $res = $pt->coordsToCap($lat, $lon, $alt);
     printf("<h3>Cap vers %lf, %lf, %lf = (dist : %lf, cap : %lf, élévation : %lf)</h3>\n", $lat, $lon, $alt, $res[0], $res[1], $res[2]);
     $lat = 43.60698;
     $lon = 1.46725;
     $alt = 300;
     $res = $pt->coordsToCap($lat, $lon, $alt);
     printf("<h3>Cap vers %lf, %lf, %lf = (dist : %lf, cap : %lf, élévation : %lf)</h3>\n", $lat, $lon, $alt, $res[0], $res[1], $res[2]);
   }
  ?>
  <p><a href="http://validator.w3.org/check?uri=referer">page xHTML validé !</a></p>
</body>
</html>
