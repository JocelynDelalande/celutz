<?php
require_once('class/site_point.class.php');
require_once('class/FormValidator.class.php');

$fields_spec = array('name' => array('required', 'basename'));

$validator = new FormValidator($fields_spec);

$is_valid = $validator->validate($_GET);



if ($is_valid) {
  $input = $validator->sane_values();
  $pano = site_point::get($input['name']);
  
  if ($pano->has_params()) {
    $params = $pano->get_params();
    $title = $params['titre'];
    $lat = $params['latitude'];
    $lon = $params['longitude'];
  } else {
    $title = $input['name'];
  }


  $has_tiles = $pano->has_tiles();//TODO
  $has_params = $pano->has_params();
  $src_path = $pano->src_path();
} else {
  $validation_errors = $validator->errors();
}
 ?>

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
<?php if ($is_valid) { ?>
      <h2><?php echo $title ?></h2>
      <ul id="pano-list">
        <li>
          <?php if ($has_tiles) { ?>
          <a href="<?php echo $pano->get_url();?>">Visualiser</a>
          (généré)
          <?php } else { ?>
          Tuiles non générées 
            <?php if ($src_path) {?>
              <a href="<?php echo $pano->get_generate_url(basename($src_path))?>">Générer</a>
            <?php } else  {?>
              (la source n'est plus disponible)
            <?php } ?>
          <?php } ?>
        </li>
        <li>
          <?php if ($has_params) { ?>
          <a href="<?php echo $pano->get_map_url();?>">Voir sur la carte</a>
          (<?php printf('%.5f,%.5f', $lat, $lon) ?>)
          <?php } else { ?>
          Non paramétré
            <?php if ($has_tiles) {?>
              <a href="<?php echo $pano->get_url();?>">Paramétrer</a>
            <?php } ?>
          <?php } ?>
        </li>
      </ul>
<?php } else { 
  $validator->print_errors(); 
}?>
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
