<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
    <link rel="stylesheet" media="screen" href="css/base.css" />
    <title>Envoi d'une image sur le serveur</title>
  </head>
  <body id="main_body">
    <header>
      <h1><img src="images/tetaneutral.svg" alt="tetaneutral.net"/></h1>
    </header>
    <section id="main">
      <h2>Ajouter un nouveau panorama</h2>
      <form action="uploadReceive.php" method="post" enctype="multipart/form-data" id="upload">
	<ul>
	  <li>
	    <label for="file" class="description">Envoyer le fichier :</label>
	    <div>
	    <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="panoramas"/>
	    <input type="file" name="files[]" id="file" multiple="multiple"
		   title="Le fichier à envoyer doit être une image de taille maximale 300 Mo"/>
	    </div>  
	  </li>
	  
	  <li>
	    <input type="submit" name="submit" value="Envoyer" />
	  </li>
	</ul>
      </form>
      <a href="./index.php">Retour liste</a>
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
