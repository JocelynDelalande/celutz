<?php
require_once('class/site_point.class.php');

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
		 'panorama' => array('required' => true));


class FormValidationError extends Exception {}

function ini_value($k, $v, $params_format) {
  /** According to the $params global reference table, format the value for
  storing in an ini file and returns it.
  */
  if (isset($params_format[$k]['type']) && $params_format[$k]['type'] == 'numeric') {
	$ini_val = $v;
  } else if (isset($params_format[$k]['type']) && $params_format[$k]['type'] == 'boolean') {
	$ini_val = $v ? "true" : "false";
  } else { //string
	$ini_val = "\"$v\"";
  }
  return $ini_val;
}

function is_ini_key($k, $params_format) {
  /** Do we need to store that information in the params ini file ?
  */
  return isset($params_format[$k]['name']);
}

function ini_key($k, $params_format) {
  /** For a given form key, returns the key for ini file
  */
  if (isset($params_format[$k]['name'])) {
    return $params_format[$k]['name'];
  } else {
    throw (new FormValidationError('"'.$k.'" is an unknown key.'));
  }
}

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
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
   <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
   <link rel="stylesheet" media="screen" href="css/base.css" />
   <title>Positionnerment dun panoramique</title>

<?php
if (count($wrong) == 0) {
  $pano = site_point::get($values['panorama']);

  // On vérifie qu'on a bien créée un nouveau fichier .params et on écrit dedans.
  echo '<p>Les valeurs suivantes sont utilisées.</p>'."\n";
  echo "<dl>\n";

  foreach ($values as $k => $v) {
    if (is_ini_key($k, $params)) {
      $storable_key = ini_key($k, $params);
      $storable_val = ini_value($k, $v, $params);

	  $pano->set_param($storable_key, $storable_val);
	  printf("<dt>%s</dt>\n<dd>%s</dd>\n", $storable_key, $storable_val);
    }
  }
  $pano->save_params();

  echo "</dl>\n";
  echo '<p class="succes">Paramétrage terminé.</p>'."\n";
  printf('<a href="%s">Retour au panorama</a></p>'."\n", $pano->get_url());


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
