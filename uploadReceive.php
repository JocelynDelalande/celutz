<?php
 
require_once('class/FormValidator.class.php');
require_once('class/site_point.class.php');
require_once('class/utils.class.php');
require_once('constants.inc.php');

class UploadReceiveError extends Exception {}


////////////////////// actions //////////////////////////////////////////

function handle_upload() {
  if (! is_dir(UPLOAD_PATH)) {
    if (! mkdir(UPLOAD_PATH)) {
      throw new UploadReceiveError(
        'Dossier "'.UPLOAD_PATH.'" non inscriptible ou inexistant.');
    }
  }
  foreach ($_FILES['files']['name'] as $i => $file) {
    $file_err = $_FILES['files']['error'][$i];
    $file_tmp = $_FILES['files']['tmp_name'][$i];
    $file_finalpath = utils::get_unique_filepath(UPLOAD_PATH.'/'.basename($file));

    if(!empty($file)) {
      if(isset($file) && UPLOAD_ERR_OK === $file_err) {
	      move_uploaded_file($file_tmp, $file_finalpath);
        return $file_finalpath;
      } else {
        throw new UploadReceiveError(
          'Une erreur interne a empêché l\'envoi de l\'image :'. $file_err);
      }
    } else {
      throw new UploadReceiveError(
        'Veuillez passer par le formulaire svp !');
    }
  }
}

function existant_and_set($list, $keys) {
  /** For HTTP data : keys of $keys are set within $list and they are not empty
  * or false nor empty
  */
  foreach($keys as $key) {
    if (!isset($list[$key]) || !$list[$key]) {
      return false;
    }
  }
  return true;
}

////////////////////// main //////////////////////////////////////////

$fields_spec = array('lat'         => array('numeric', 'positive'),
                     'lon'         => array('numeric', 'positive'),
                     'alt'  => array('numeric', 'positive'),
                     'loop'  => array('boolean'),
);

$validator = new FormValidator($fields_spec);

////// STEP 1 : UPLOAD ////////

$upload_success = false;
$uploaded_filepath = '';

if ($validator->validate($_REQUEST)) {
  try {
    $uploaded_filepath = handle_upload();
    $upload_success = true;
    $message = sprintf("transfert de %s réalisé", basename($uploaded_filepath));
  } catch (UploadReceiveError $e) {
    $message = $e->getMessage();
  }
} else {
  $message = 'paramètres invalides';
}

////// STEP 2 : PARAMETERS ////////

$params_success = false;

if ($upload_success) {
  $vals = $validator->sane_values();
  // There is no point setting a part of the parameters only ; check that all
  // are present.  
  if (existant_and_set($vals, array('lat', 'alt', 'lon'))) {
    try {
      $panorama = site_point::create($uploaded_filepath);
      $panorama->set_param('titre', 'Sans nom 1');//FIXME
      $panorama->set_param('latitude',  $vals['lat']);
      $panorama->set_param('longitude', $vals['lon']);
      $panorama->set_param('altitude',  $vals['alt']);
      $panorama->set_param('image_loop', $vals['loop']);
      $panorama->save_params();
      $params_success = true;
    } catch (Exception $e) {
      $message = 'erreur à la création du panorama : '.$e->getMessage();
    }
  }
}


?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
   <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
   <title>Transfert de panoramique</title>
</head>
<body>
<?php
if (isset($message)) {
  echo "<h2>$message</h2>\n";
  if ($validator->errors()) {
    foreach($validator->errors() as $key => $error) {
      printf('<p>"%s" : %s</p>', $_REQUEST[$key], $error);
    }

  } else {
?>
    <p>Pour acceder à la liste des images transférées afin de convertir en panorama <a href="creerPano.php">cliquer ici</a></p>
<?php 
  }
}
?>
</body>
</html>
