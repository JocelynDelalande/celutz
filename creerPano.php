<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
		<link rel="stylesheet" media="screen" href="css/ttn_style_1.css" />
    	<title>creation d'un panoramique</title>
    	<script type="text/javascript">
    	
		function showLoad(outgoingLink){
 
		var link = document.getElementById(outgoingLink);
		var loader = document.createElement('img');
		loader.id = 'loader';
		loader.src ='images/loader2.gif';
		var li = link.parentNode;
		li.appendChild(loader);
		}
    	</script>
    </head>
    <body>
	    <img id="top" src="images/top.png" alt="">
		<div id="page_container">
		    <h1><img src="images/tetaneutral.png"></h1>
		  	<h2>Listes des photos sur le serveur</h2> 
		  	<p>Cliquez pour générer un panorama</p>  
       		<div id="containerList">
		    	<ul>
		    	
	       		<?php
                        
	       		$base_dir = "/var/www/data/tsf2/vpongnian/panorama/upload/"; // modifier selon l'arborescence.
				    try
					{
                                            // On ouvre le dossier ou se trouve les images
					    $dir_fd = opendir($base_dir); 
					    
					    $i=0;         // Garantir l'unicité du id des liens.
					    while (false !== ($image_name = readdir($dir_fd))) {
					    	$dir = $base_dir.$image_name;   
					    	
							if ($image_name != "." && $image_name != "..")   // N'affiche pas les répertoires parents et courant.
							{
							     printf('<li><a href="genererPano.php?name=%s" id="link_'.$i.'" onclick="showLoad(this.id);return true;">%s</a></li>'."\n",$image_name,$image_name);
							     $i++; 
							}  
							      
					        	
					    }
					}
					catch(Exception $e)
					{
						die('Erreur : '.$e->getMessage());
					} 
	       		?>
       			</ul>
    		</div>
    		<div id="footer"><a href="./index.php">Retour à l'index</a></div>
			
    	</div>
    	<img id="bottom" src="images/bottom.png" alt="">
    </body>
</html>
