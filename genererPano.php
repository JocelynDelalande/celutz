<?php
require_once 'class/utils.class.php';
require_once 'class/site_point.class.php';
require_once 'class/TilesGenerator.php';
require_once 'constants.inc.php';


$fields_spec = array(
  'name'   => array('required', 'basename'), // name of the field within uploads dir
  'wizard' => array('boolean')
);

$validator = new FormValidator($fields_spec);
$is_valid = $validator->validate($_GET);

if ($is_valid) {
  $input = $validator->sane_values();
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
  <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  <title>convertiseur image vers panorama</title>
</head>

<body>

<?

if ($is_valid) {
  $image_path = UPLOAD_PATH.'/'.$input['name'];
  // We init the panorama with the same name as image.
  $pano_name = utils::strip_extension($input['name']);
  $panorama = site_point::get($pano_name);

  $tiles_generator = new TilesGenerator($image_path, $panorama);

  try {
    $tiles_generator->prepare();
    printf("<h2>Exécution de la commande :</h2>\n");
    printf("<p class=\"cmd\"><samp>%s</samp></p>\n",
           $tiles_generator->mk_command());

    echo "<pre>\n";
    $tiles_generator->process();
    print("</pre>\n");


    print("<h4><span class=\"success\">Opération réussie</span></h4>\n");
    printf("<p>Pour acceder directement au panorama <a href=\"%s\">cliquer ici</a></p>\n",
           $panorama->get_url());
    print("<p>Pour acceder à la liste des panoramas <a href=\".\">cliquer ici</a></p>\n") ;


    // Redirect in js to sumary page
    if ($input['wizard']) {
      printf('<script>window.location=\'panoInfo.php?name=%s\'</script>\n', $pano_name);
    }

  } catch (TilesGeneratorRightsException $e) {
    printf("<p class=\"error\">%s</p>\n", $e->getMessage());
  } catch (TilesGeneratorScriptException $e) {
    printf("<h4><span class=\"error\">%s</span></h4>\n", $e->getMessage());
    print("</pre>\n");
  }
} else { 
  $validator->print_errors(); 
}
?>
</body>
</html>
