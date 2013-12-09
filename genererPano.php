<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <title>convertiseur image vers panorama</title>
</head>

<?php
require_once 'class/utils.class.php';
require_once 'constants.inc.php';
utils::init();
if (isset($_GET['name'])) {
  $pano_name = $_GET['name'];
  $pano_basename = preg_replace('/\.[^.]+$/', '', $pano_name);
  
  //Partie exécutante du script gen_tiles qui gènere les tuiles à partir d'une image.
  $pano_dest = PANORAMA_PATH.'/'.$pano_basename;
  if (! is_dir(PANORAMA_PATH)) {
    if (! mkdir(PANORAMA_PATH)) {
        echo "<p class=\"error\">le répertoire \"PANORAMA_PATH\" n'est pas accessible et ne peut être créé</p>\n";
    }
  } else if (file_exists($pano_dest)) {
    echo "<p class=\"error\">le nom de répertoire \"$pano_dest\" est déjà pris</p>\n";
  } else {
    mkdir($pano_dest);
    $escaped_command = escapeshellcmd('./to_tiles/gen_tiles.sh -p '.$pano_dest.'/'.$pano_basename.' ./upload/'.$pano_name);
                
    printf("<h2>Exécution de la commande :</h2>\n<p class=\"cmd\"><samp>%s</samp></p>\n<pre>", htmlspecialchars($escaped_command));
    if ($fp = popen($escaped_command, 'r')) {
      while (!feof($fp)) {
        //set_time_limit (20); 
        $results = fgets($fp, 4096);
        if (strlen($results) == 0) {
          // stop the browser timing out
          flush();
        } else {
          $tok = strtok($results, "\n");
          while ($tok !== false) {
            echo htmlspecialchars(sprintf("%s\n",$tok))."<br/>";
            flush(); 
            $tok = strtok("\n");
          }
        }
      }
      print("</pre>\n");
      if (pclose($fp) === 0) {
        print("<h4><span class=\"success\">Opération réussie</span></h4>\n");
        printf("<p>Pour acceder directement au panorama <a href=\"panorama.php?dir=%s&amp;panorama=%s\">cliquer ici</a></p>\n", 
               PANORAMA_PATH, $pano_basename);
      } else {
        print("<h4><span class=\"error\">Opération en échec durant l'exécution du script !</span></h4>\n");
      }
    } else {
      print("<h4><span class=\"error\">Opération en échec à l'ouverture du script !</span></h4>\n");
    }
  }
  print("<p>Pour acceder à la liste des panoramas <a href=\".\">cliquer ici</a></p>\n") ;
}
?>
