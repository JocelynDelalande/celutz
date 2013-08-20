<?php
	if (isset($_GET['name'])) {
		// Protection de la variable GET
		$pano_name = htmlspecialchars($_GET['name']);
		$filePartArr = explode('.',$pano_name);  
		$ext = $filePartArr[count($filePartArr) - 1];
		if($ext=="tif"){
			$pano_basename = basename($pano_name,".tif");
		}else if($ext=="jpeg"){
		      $pano_basename = basename($pano_name,".jpeg");
		} else {
			$pano_basename = basename($pano_name,".jpg");
		} 

		//Partie exécutante du script gen_tiles qui gènere les tuiles à partir d'une image.
		$input = './to_tiles/gen_tiles.sh -p '.$pano_basename.' /var/www/data/tsf2/vpongnian/panorama/upload/'.$pano_name;
		$escaped_command = escapeshellcmd($input);
		$output = shell_exec($escaped_command);
		$log_file = fopen('./log/'.$pano_basename.'.log','a+');
		fputs($log_file, $output);   // verbose intégré dans un .log.
		fclose($log_file);
		
                // Ouverture d'un nouveau dossier qui contiendra toutes les tuiles.
		$dir = '/var/www/data/tsf2/tiles/'.$pano_basename;
		
		mkdir($dir,0777);
		
		$dir_fd = opendir('/var/www/data/tsf2/vpongnian/panorama');
		
		while (false !== ($image_name = readdir($dir_fd))) {
	    	        // Déplacement des tuiles dans le nouveau dossier à partir de dir_fd.
			if(preg_match('/(.*)_[0-9]+_[0-9]+_[0-9]+\.jpg$/', $image_name, $reg)) {
	        	rename("./".$image_name, $dir."/".$image_name);
	    	}
	    	
	    }
	    closedir($dir_fd);
	    header("Location: ./index.php?");
	}
?>