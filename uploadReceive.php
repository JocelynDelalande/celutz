<?php
 
require_once('class/FormValidator.class.php');
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
    $file_finalpath = UPLOAD_PATH.'/'.basename($file);

    if(!empty($file)) {
      if(isset($file) && UPLOAD_ERR_OK === $file_err) {
	      move_uploaded_file($file_tmp, $file_finalpath);
        return sprintf("transfert de %s réalisé",  $file);
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

////////////////////// main //////////////////////////////////////////

$fields_spec = array('lat'         => array('required', 'numeric', 'positive'),
                     'lon'         => array('numeric', 'positive'),
                     'alt'  => array('numeric'),
);

$validator = new FormValidator($fields_spec);
$upload_success = false;

////// STEP 1 : UPLOAD ////////

if ($validator->validate($_REQUEST)) {
  try {
    $message = handle_upload();
    $upload_success = true;
  } catch (UploadReceiveError $e) {
    $message = $e->getMessage();
  }
} else {
  $message = 'paramètres invalides';
}

////// STEP 2 : PARAMETERS ////////

$params_success = false;

if ($upload_success) {
  //pass
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
