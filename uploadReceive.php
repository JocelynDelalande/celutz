<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
   <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
   <link rel="stylesheet" media="screen" href="css/index_style.css" />
   <title>Transfert de panoramique</title>
</head>
<body>
<?php
 
$dest_dir ='upload';

if(!empty($_POST) && !empty($_FILES)) {
  foreach (array_keys($_FILES['files']['name']) as $i) {
    if(!empty($_FILES['files']['name'][$i])) {
      if(isset($_FILES['files']['error'][$i]) && UPLOAD_ERR_OK === $_FILES['files']['error'][$i]) {
	move_uploaded_file($_FILES['files']['tmp_name'][$i],$dest_dir.'/'.basename($_FILES['files']['name'][$i]));
	printf("<h2>transfert de %s réalisé</h2>\n", $_FILES['files']['name'][$i]);
      } else {
	$message = 'Une erreur interne a empêché l\'upload de l\'image : '. $_FILES['files']['error'][$i];
      }
    } else {
      $message = 'Veuillez passer par le formulaire svp !';
    }
  }
  if (isset($message)) {
    echo "<h2>$message</h2>\n";
  }
}
?>
<p>Pour acceder à la liste des images transférées afin de convertir en panorama <a href="creerPano.php">cliquer ici</a></p>
</body>
</html>
