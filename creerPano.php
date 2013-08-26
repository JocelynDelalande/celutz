<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <title>Liste des images transformables en panoramas</title>
    <link rel="stylesheet" media="screen" href="css/base.css"/>
  </head>
  <body>
    <header>
      <h1><img src="images/tetaneutral.svg" alt="tetaneutral.net"/></h1>
    </header>
    <section id="main">
      <h2>Liste des images transformables en panoramas</h2>
      <?php
require 'class/utils.class.php';
utils::init();

if(isset($_GET['dir']) && is_dir($_GET['dir'])) {
  $base_dir = $_GET['dir']; 
} else {
  $base_dir='upload';
}

try {
  $finfo = finfo_open(FILEINFO_MIME_TYPE); // Retourne le type mime du fichier
  $did = opendir($base_dir);

  echo "<ul id=\"pano-list\">\n";
	
  while(false !== ($filename = readdir($did))) {
    if (!preg_match('/^\.\.?$/', $filename)) {
	$ftype = finfo_file($finfo, $base_dir.'/'.$filename);
	if (isset($ftype)) {
	  $cmt = $filename;
	  $title = sprintf(' title="fichier de type %s"', $ftype);
	} else {
	  $cmt = sprintf('<samp>%s</samp>', $filename);
	  $title = ''; 
	}
	printf ('<li%s><a href="genererPano.php?dir=%s&amp;name=%s">%s</a></li>'."\n", $title, $base_dir, $filename, $cmt);
      }
  }
  echo "</ul>\n";
  finfo_close($finfo);
} catch (Exception $e) {
  printf("<h3 class=\"warning\">désolé mais aucun site n'est disponible...</h3>\n");
}
?>
      <p id="interaction">
	<a href="." title="Revenir à la liste des panoramas">Retour</a>
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
