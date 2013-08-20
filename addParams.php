<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<?php
if(isset($_GET['dir']) && isset($_GET['panorama'])){
	
	$_GET['dir'] = htmlspecialchars($_GET['dir']);              //Protection des variables GET.
	$_GET['panorama'] = htmlspecialchars($_GET['panorama']);    // ...
	
	if (isset($_POST['param_latitude']) && isset($_POST['param_longitude']) 
	&& isset($_POST['param_altitude']) && isset($_POST['param_elevation']) 
	&& isset($_POST['param_title']) && isset($_POST['param_loop'])) {
		
		foreach ($_POST as $value)                              //Protection des variables POST.
		{	
		    $value = htmlspecialchars($value);
		}

		/* --- Vérification des inputs avec des regex. ---*/
	    // Pour la latitude : ( Le javascript bride entre -90 et 90)
	    if (preg_match("#^(\-?[1-9]+[0-9]?[.,]{0,1}[0-9]{0,6}|\-?[0-9]{1}[.,]{0,1}[0-9]{0,6})$#", $_POST['param_latitude']))
	    {
	    	$lat = $_POST['param_latitude'];
	        //echo 'Le ' . $_POST['param_latitude'] . ' est un numéro <strong>valide</strong> !';
	    }
	    else
	    {
	        echo 'Le ' . $_POST['param_latitude'] . ' n\'est pas valide, recommencez !';
	        
	    }
	    
	    // Pour la longitude : ( Le javascript bride entre -180 et 180)
	    if (preg_match("#^(\-?[1-9]+[0-9]?[.,]{0,1}[0-9]{0,6}|\-?[0-9]{0,1}[.,]{1}[0-9]{0,6})$#", $_POST['param_longitude']))
	    {
	    	$lon = $_POST['param_longitude'];
	        //echo 'Le ' . $_POST['param_longitude'] . ' est un numéro <strong>valide</strong> !';
	    }
	    else
	    {
	        echo 'Le ' . $_POST['param_longitude'] . ' n\'est pas valide, recommencez !';
	        
	    }
	    
	    // Pour l'altitude ( Le javascript bride entre 0 et 500)
	    if (preg_match("#^([1-9]+[0-9]{0,4}|0)$#", $_POST['param_altitude']))
	    {
	    	$alt = $_POST['param_altitude'];
	        //echo 'Le ' . $_POST['param_altitude'] . ' est un numéro <strong>valide</strong> !';
	    }
	    else
	    {
	        echo 'Le ' . $_POST['param_altitude'] . ' n\'est pas valide, recommencez !';
	        
	    }
	    
	    // Pour l'élévation  ( Le javascript bride entre -10 et 10)
	    if (preg_match("#^(\-?[1-9]+[0-9]?|0)$#", $_POST['param_elevation']))
	    {
	    	$ele = $_POST['param_elevation'];
	        //echo 'Le ' . $_POST['param_elevation'] . ' est un numéro <strong>valide</strong> !';
	    }
	    else
	    {
	        echo 'Le ' . $_POST['param_elevation'] . ' n\'est pas valide, recommencez !';
	        
	    }
	    
	    $loop = $_POST['param_loop'];   // Variable radio automatiquement présente
	    if(isset($lat) && isset($lon) && isset($alt) && isset($ele) && isset($loop)) {
 	
	    	// On recherche le dossier correspondant au panorama en question
	    	$dir_file = "/var/www/data/tsf2/vpongnian/panorama/".$_GET['dir']."/".$_GET['panorama'];
	    	$dir_open = opendir($dir_file);
	    	while (false !== ($file = readdir($dir_open))) {
    	               // Si on trouve bien des tuiles
		       if (preg_match('/(.*)_[0-9]+_[0-9]+_[0-9]+\.jpg$/', $file, $reg)) {
			 $prefix = $reg[1];
			 $new_param_file = $prefix.".params";
			 break;   // On sort à la première tuile trouvée
		       }
		    }
		    closedir($dir_open);
		    
		    $retour = "\n"; 
                    // On vérifie qu'on a bien crée un nouveau fichier .params et on écrit dedans.
		    if(isset($new_param_file)){
		    	$param_file = fopen($dir_file."/".$new_param_file,'a+');
		    	fputs($param_file,"titre = \"" . $_POST['param_title'] . "\"");
		    	fputs($param_file,$retour);
		    	fputs($param_file,"latitude = " . $lat);
		    	fputs($param_file,$retour);
		    	fputs($param_file,"longitude = " . $lon);
		    	fputs($param_file,$retour);
		    	fputs($param_file,"altitude = " . $alt);
		    	fputs($param_file,$retour);
		    	fputs($param_file,"elevation = " . $alt);
		    	fputs($param_file,$retour);
		    	fputs($param_file,"image_loop =" . $loop);
		    	fputs($param_file,$retour);
		    	fclose($param_file);
		    	
		    	echo 'Paramétrage OK. Retour au panorama';
		    	header("Refresh: 1; URL=index.php");  
		    } else {
		    	
		    	echo "<script>alert(\"impossible d'écrire dans le fichier\")</script>";
		    }
	    }
	} else {
		echo '<script>alert(\'$_POST manquant\')</script>';
		header("Refresh: 2; URL=javascript:history.back();"); 	
	}
} else {
echo '<script>alert(\'La destinaton est manquante\')</script>';
header("Refresh: 2; URL=javascript:history.back();");
}

?>
</html>
