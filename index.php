<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <title>Liste des panoramas</title>
  <link rel="stylesheet" media="screen" href="css/all.css" />
  <script src="js/pano.js"></script>
</head>
<body>
  <h1>Liste des panoramas</h1>   
  <?php
   require 'class/site_point.class.php';
   require 'class/sites_dir.class.php';
   echo '<ul>';
   if(isset($_GET['dir']) && is_dir($_GET['dir'])) {
     $base_dir = $_GET['dir'];
   } else {
     $base_dir = 'tiles';
   }

   $dir = new sites_dir($base_dir);

   foreach($dir->get_sites() as $pt) {
     $params = $pt->get_params();
     if (isset($params['titre'])) {
       $cmt = $params['titre'];
     } else {
       $cmt = sprintf('fichier <samp>%s/%s</samp>', $pt->get_name(), $pt->get_prefix());
     }
     printf ('<li><a href="panorama.php?dir=%s&amp;panorama=%s">%s</a></li>'."\n", $base_dir, $pt->get_name(), $cmt);
   }
   echo '</ul>';
  ?>
  <p><a href="http://validator.w3.org/check?uri=referer">page xHTML validé !</a></p>
</body>
</html>
