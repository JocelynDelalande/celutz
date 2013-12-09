<?php
require_once('class/site_point.class.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
   <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
   <link rel="stylesheet" media="screen" href="css/base.css" />
   <title>Positionnerment dun panoramique</title>

<?php
   // tableau de vérification de conformité
 $params = array('title' => array('name' => 'titre',
				  'pattern' => '^.{1,50}$',
				  'required' => true),
		 'latitude' => array('name' => 'latitude',
				     'type' => 'numeric',
				     'min' => -180,
				     'max' => 180,
				     'required' => true),
		 'longitude' => array('name' => 'longitude',
				     'type' => 'numeric',
				      'min' => -180,
				      'max' => 180,
				      'required' => true),
		 'altitude' => array('name' => 'altitude',
				     'type' => 'numeric',
				     'min' => -400,
				     'required' => true),
		 'loop' => array('name' => 'image_loop',
				 'type' => 'boolean',
				 'required' => false),
		 'dir' => array('required' => true),
		 'panorama' => array('required' => true));
$wrong = array();
$values = array();
// vérification de la conformité
foreach($params as $param => $check) {
  if (isset($_REQUEST['param_'.$param])) {
    $tst = $_REQUEST['param_'.$param];
    if ((isset($check['min']) || isset($check['max'])) && ! is_numeric($tst)) $wrong[$param] = "<em>$tst</em> ne correspond pas à une valeur numérique";
    else if (isset($check['min']) && $tst < $check['min']) $wrong[$param] = "<em>$tst</em> trop bas";
    else if (isset($check['max']) && $tst > $check['max']) $wrong[$param] = "<em>$tst</em> trop haut";
    else if (isset($check['pattern']) && preg_match('/'.preg_quote($check['pattern']).'/', $tst)) $wrong[$param] = "<em>$tst</em> non conforme";
    else $values[$param] = $tst;
  } else if (isset($check['required']) && $check['required']) {
    $wrong[$param] = '<em>$tst</em> est un paramètre manquant';
  }
}


if (count($wrong) == 0) {
	$pano = site_point::get($values['panorama']);

  // On vérifie qu'on a bien créée un nouveau fichier .params et on écrit dedans.
	echo '<p>Les valeurs suivantes sont utilisées.</p>'."\n";
	echo "<dl>\n";
	foreach ($values as $k => $v) {
		if (isset($params[$k]['name'])) {
			$nm = $params[$k]['name'];
			if (isset($params[$k]['type']) && $params[$k]['type'] == 'numeric') {
				$vf = $v;
			} else if (isset($params[$k]['type']) && $params[$k]['type'] == 'boolean') {
				$vf = $v ? "true" : "false";
			} else {
				$vf = "\"$v\"";
			}
			$pano->set_param($nm, $vf);
			printf("<dt>%s</dt>\n<dd>%s</dd>\n", $nm, $vf);
		}
	}
	$pano->save_params();

	echo "</dl>\n";
	echo '<p class="succes">Paramétrage terminé.</p>'."\n";
  printf('<a href="%s">Retour au panorama</a></p>'."\n", $panorama->get_url());


 } else {
	echo '<p class="error">Les valeurs suivantes sont incorrectes.</p>'."\n";
	echo "<dl>\n";
	foreach ($wrong as $k => $v) {
		printf("<dt>%s</dt>\n<dd>%s</dd>\n", $k, $v);
	}
	echo "</dl>\n";
}
?>
</html>
