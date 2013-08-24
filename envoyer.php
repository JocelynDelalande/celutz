<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
    <link rel="stylesheet" media="screen" href="css/view.css" />
    <title>Envoi d'une image sur le serveur</title>
  </head>
  <body id="main_body">
    <img id="top" src="images/top.png" alt="">
    <div id="form_container">
      <h1><img src="images/tetaneutral.svg"></h1>
      <form action="uploadReceive.php" method="post" enctype="multipart/form-data" id="upload">
	<h2>Ajouter un nouveau panorama</h2>
	<ul>
	  <li id="li_1" >
	    <label for="file" class="description">Envoyer le fichier :</label>
	    <div>
	      <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="panoramas"/>
	      <input type="file" name="files[]" id="file" multiple="multiple"
		     title="Le fichier à envoyer doit être une image de taille maximale 300 Mo"/>
	    </div>  
	  </li>
	  
	  <li class="buttons">
	    <input type="submit" name="submit" id="submit" value="Submit" />
	  </li>
	</ul>
      </form>
      <!-- Fin du formulaire -->
      <div id="footer">
	<a href="./index.php">Retour liste</a>
      </div>
    </div>
  </body>
</html>
