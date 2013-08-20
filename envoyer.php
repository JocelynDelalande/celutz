<?php $uid = md5(uniqid(rand())); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
	<head>
    	
    	<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    	<link type="image/x-icon" rel="shortcut icon" href="images/tsf.png"/>
  		<link rel="stylesheet" media="screen" href="css/view.css" />
    	<title>Upload d'une image sur le serveur !</title>
    	        <!-- suivi de l'upload par ajax. Peut être utile pour certains navigateurs qui ne renseigne pas l'avancement.
		<script type="text/javascript">
			var HttpRequestObject = false;
			if(window.XMLHttpRequest) {
			   HttpRequestObject = new XMLHttpRequest();
			}
			else if(window.ActiveXObject) {
			   HttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			function startProgress(uid) {
			  
				
			   <!--document.getElementById('upload').style.display = 'none';-->
			   document.getElementById('pb_outer').style.display = 'block';
			   setTimeout('getProgress("' + uid + '")', 1000);
			  
			   
			   
			}
			function getProgress(uid) {
			   if(HttpRequestObject) {
			      HttpRequestObject.open('GET', 'getprogress.php?uid=' + uid, true);
			      HttpRequestObject.onreadystatechange = function() {
			         if(HttpRequestObject.readyState == 4 && HttpRequestObject.status == 200) {
			            var response = HttpRequestObject.responseText;
			            var elem = response.split('#');
			            var progress = elem[0];
			            var url =elem[1];
			            var message=elem[2];
			            
			            document.getElementById('pb_inner').style.width = progress + '%';
			            document.getElementById('pb_inner').innerHTML = progress + '%';
			           
			            if(progress < 100) {
			               
			               setTimeout('getProgress("' + uid + '")', 100);
			                
			            } else {
				            if(message !=='') {
				            	document.getElementById('pb_outer').style.display = 'none';
				            	document.getElementById('pb_inner').style.width = 'none';
				           		alert(message);
				            } else {
					            document.getElementById('pb_inner').innerHTML = 'Upload Complete!';
					            document.location.href= url; 
				            }
			            }
			         }
			      }
			      
			      HttpRequestObject.send(null);
			   }
			}
			
			function showLoader() {
				document.getElementById('loader').innerHTML = "Veuillez patienter ...  ";
				
				
			}
			
		</script>
	</head>
	<body id="main_body">
  		<img id="top" src="images/top.png" alt="">
		<div id="form_container">
			<h1><img src="images/tetaneutral.png"></h1>
		
	    	<form onSubmit="startProgress('<?php echo $uid; ?>');" action="uploadTest.php" method="post" class="appnitro" enctype="multipart/form-data" name="upload" id="upload" target="upload_frame">
				 <h2>Ajouter un nouveau panorama</h2>
										
			<ul>
				<li id="li_1" >
					<label for="file" class="description">Envoyer le fichier :</label>
					<div>
			        <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="<?php echo $uid; ?>" />
			        <input type="file" name="file" id="file" />
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
		<div id="infoSize">
		<img src="images/bulle.png" id="bulle"/>
		<label id="txtInfo">Le fichier à envoyer doit etre une image de type .tif ou .jpeg<br />
        Taille maximale : 300 Mo</label>
    	</div>
		<!-- Barre d'upload -->
		<div id="pb_outer">
  		<div id="pb_inner"></div>
  		</div>
  		<p id="info"></p>
	  
	  <iframe style="display: none" id="upload_frame" name="upload_frame"></iframe>
	 
    <?php 
    
    /*******************************************************************
     * Permet d'afficher l'image uploadée sur le serveur
     *******************************************************************/

    if(isset($_GET['img']) && isset($_GET['dir'])){
  
    	apc_delete('link');   // Suppression de la variable cache.
    	
    	$image_name = htmlspecialchars($_GET['img']);
    	$dir_image = htmlspecialchars($_GET['dir']);
    	
        $basename = basename($_GET['img']);
    	$filePartArr = explode('.', $basename);  
        $ext = $filePartArr[count($filePartArr) - 1];
	if($ext=="tif"){
		$basename = basename($_GET['img'],".tif");
	}else if($ext=="jpeg"){
	      $basename = basename($_GET['img'],".jpeg");
       	} else {
	      $basename = basename($_GET['img'],".jpg");
	} 
    	        //Permet d'afficher l'image après uptload ( Conversion des .tiff et .jpg pour affichage dans le navigateur )
		$input = 'convert /var/www/data/tsf2/vpongnian/panorama/upload/'.$_GET['img'].' -resize 10% /var/www/data/tsf2/vpongnian/panorama/upload/'.$basename.'.jpg'; // Adapter les chemins absolus ou relatifs
		$escaped_command = escapeshellcmd($input);
		$output = shell_exec($escaped_command);
		echo "<pre>$output</pre>\n";
		
    ?><div id="genererPano">
		<form enctype="multipart/form-data" action="<?php echo "envoyer.php"/*htmlspecialchars($_SERVER['PHP_SELF'])*/; ?>" method="post" onSubmit="showLoader()">
		  <p>
		    <label id="l_generer" for="no" title="Génerer le panoramique">Generer le panoramique :</label>
		    <input type="hidden" name="image_name" value="<?php echo $image_name; ?>" />
		    <input type="submit" name="no" value="pas maintenant"/>
		    <input type="submit" name="yes" value="oui" />
		    <label id="loader"></label>
		  </p>
		</form>
	  </div>
    <?php 
   
    echo "<img src=/data/tsf2/vpongnian/panorama/upload/".$basename.".jpg id='imageUpload'/>";
    }  
    if (isset($_POST['no'])) {
		header("Location: ./index.php"); /* Redirection du navigateur */
		exit;
	} 
	if (isset($_POST['yes'])) {
		header("Location: ./genererPano.php?name=".$_POST['image_name']);
		exit;
	}
    ?>
   
  </body>
</html>