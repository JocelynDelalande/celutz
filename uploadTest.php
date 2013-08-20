<?php
 
// Constantes
define('TARGET', '/var/www/data/tsf2/vpongnian/panorama/upload/');    // Repertoire cible
define('MAX_SIZE', 300000000);    // Taille max en octets du fichier
define('WIDTH_MAX', 200000);    // Largeur max de l'image en pixels
define('HEIGHT_MAX', 100000);    // Hauteur max de l'image en pixels


// Creation du repertoire cible si inexistant

/*if( !is_dir(TARGET) ) {
  if( !mkdir(TARGET, 0755) ) {
    exit('Erreur : le répertoire cible ne peut-être créé ! Vérifiez que vous diposiez des droits suffisants pour le faire ou créez le manuellement !');
  }
}
*/

// Script d'upload

	// Variables
	$extension = '';
	$message = '';
	$nomImage = '';
	$url='';
	$already=false;
	
	// Tableaux de donnees
	$tabExt = array('jpeg','tif','jpg');    // Extensions autorisees
	$infosImg = array();
	$stats = array();
	
	
	if(!empty($_POST))
	{
		
	  // On verifie si le champ est rempli
	  if( !empty($_FILES['file']['name']) )
	  {
	  	
	    // Recuperation de l'extension du fichier
	    $extension  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	 
	    // On verifie l'extension du fichier
	    if(in_array(strtolower($extension),$tabExt))
	    {
	      // On recupere les dimensions du fichier
	      $infosImg = getimagesize($_FILES['file']['tmp_name']);
	 
	      // On verifie les dimensions et taille de l'image
	      if(($infosImg[0] <= WIDTH_MAX) && ($infosImg[1] <= HEIGHT_MAX) && (filesize($_FILES['file']['tmp_name']) <= MAX_SIZE))
	      {
	        // Parcours du tableau d'erreurs
	        if(isset($_FILES['file']['error']) 
	          && UPLOAD_ERR_OK === $_FILES['file']['error'])
	        {
	          // On bouge le fichier uploadé dans un répertoire a son nom
	          // on garde en cache la variable $url qui permettra de passer sur une page envoyer.php plus avancée.
	 	
	          $err = $_FILES['file']['error'];
		    echo "<script>alert(\"$err\");</script>";
	            $url ="envoyer.php?dir=upload/".$_FILES['file']['name']."&img=".$_FILES['file']['name'];
	            move_uploaded_file($_FILES['file']['tmp_name'],TARGET.basename($_FILES['file']['name']));
				apc_store('link', $url);
				
	        }
	        else
	        {
	        	$message = 'Une erreur interne a empêché l\'uplaod de l\'image : '. $_FILES['file']['error'];
	        }
	      }
	      else
	      {
	        // Sinon erreur sur les dimensions et taille de l'image
	        $message = 'Erreur dans les dimensions de l\'image !';  
	      }
	    }
	    else
	    {
	      // Sinon on affiche une erreur pour l'extension
	      $message = 'L\'extension du fichier est incorrecte !';
	    }
	  }
	  else
	  {
	    // Sinon on affiche une erreur pour le champ vide
	 	$message = 'Veuillez remplir le formulaire svp !';
	  }
	  // On met en cache un message d'erreur.
	  apc_store('info', $message);
	}
?>