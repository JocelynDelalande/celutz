<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <title>Liste des panoramas</title>
    <link rel="stylesheet" media="screen" href="css/base.css"/>
  </head>
  <body>
    <header>
      <h1><img src="images/tetaneutral.svg" alt="tetaneutral.net"/></h1>
    </header>
    <section id="main">
      <h2>Liste des panoramas</h2>
      <?php
require 'class/utils.class.php';
utils::init();

if(isset($_GET['dir']) && is_dir($_GET['dir'])) {
  $base_dir = $_GET['dir'];
} else {
  $base_dir='tiles';
}

$dir = new sites_dir($base_dir);
try {
  $sites_list = $dir->get_sites();

  echo "<ul id=\"pano-list\">\n";

  foreach($sites_list as $pt) {
    $params = $pt->get_params();
    $pos_file = sprintf('%s/%s', $pt->get_name(), $pt->get_prefix());
    if (isset($params['titre'])) {
      $cmt = $params['titre'];
      $title = sprintf(' title="fichier : %s"', $pos_file);
    } else {
      $cmt = sprintf('<samp>%s</samp>', $pos_file);
      $title = '';
    }
    printf ('<li%s><a href="%s">%s</a></li>'."\n", $title, $pt->get_url(), $cmt);
  }
  echo "</ul>\n";
  } catch (Exception $e) {
  printf("<h3 class=\"warning\">désolé mais aucun site n'est disponible...</h3>\n");
}
?>
      <p id="interaction">
	<a href="envoyer.php" title="Envoyer une image sur le site">Ajouter un panorama</a>
	<a href="creerPano.php" title="Générer un panorama à partir d\'une image déjà envoyée">Générer un panorama</a>
      </p>
    </section>
    <footer class="validators"><samp>
      page validée par
      <a href="http://validator.w3.org/check?uri=referer"><img src="images/valid_xhtml.svg"
							       alt="Valid XHTML" title="xHTML validé !"/></a>
      <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="images/valid_css.svg"
								      alt="CSS validé !" title="CSS validé !"/></a>
    </samp></footer>
  </body>
</html>
