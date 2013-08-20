	
	function getInfoBox(element) {
	    
        while (element = element.nextSibling) {
            if (element.className === 'infobox') {
                return element;
            }
        }
         
        return false;
     
    }
	
	 // Fonctions de vérification du formulaire, elles renvoient « true » si tout est OK
    
    var check = {}; // On met toutes nos fonctions dans un objet littéral
     
	check['param_title'] = function() {
	        
	        var title = document.getElementById('param_title');
	        
	        if (title.value.length >= 4) {
	            title.className = 'correct';
	            
	            return true;
	        } else {
	            title.className = 'incorrect';
	            
	            return false;
	        }
	};
        
    check['param_latitude'] = function() {
        
        var latitude = document.getElementById('param_latitude');
        var lat = parseFloat(latitude.value);
       
        if (!isNaN(lat) && lat >= -90 &&  lat <= 90) { 
            latitude.className = 'correct';
           
            return true;
        
        } else {
            latitude.className = 'incorrect';
           
            return false;
        }
     
    };
    
    check['param_longitude'] = function() {
        
        var longitude = document.getElementById('param_longitude');
        var lon = parseFloat(longitude.value);
        	
        if (!isNaN(lon) && lon >= -180 &&  lon <= 180) {
            longitude.className = 'correct';
            
            return true;
        
        } else {
            longitude.className = 'incorrect';
            
            return false;
        }
     
    };
    
    check['param_altitude'] = function() {
        
        var altitude = document.getElementById('param_altitude');
        var alt = parseInt(altitude.value);
        
        if (!isNaN(alt) && alt >= 0 && alt <= 10000) {
            altitude.className = 'correct';
            
            return true;
        
        } else {
            altitude.className = 'incorrect';
            
            return false;
        }
     
    };
    
    check['param_elevation'] = function() {
        
    	
        var elevation = document.getElementById('param_elevation');
        var ele = parseFloat(elevation.value);
        
        if (!isNaN(ele) && ele >= -10 &&  ele <= 10) {
            elevation.className = 'correct';
           
            return true;
        
        } else {
            elevation.className = 'incorrect';
            
            return false;
        }
     
    };
    
    (function() { // Utilisation d'une fonction anonyme pour éviter les variables globales.
        
        var form_param = document.getElementById('form_param'),
            inputs = document.getElementsByTagName('input'),
            inputsLength = inputs.length;
    
        for (var i = 0 ; i < inputsLength ; i++) {
        	
	            if (inputs[i].type == 'text') {
	     		
	                inputs[i].onkeyup = function() {
	                    check[this.id](this.id); // « this » représente l'input actuellement modifié
	                };
	     
	            }
        	
        }
     
        form_param.onsubmit = function() {
     
            var result = true;
     
            for (var i in check) {
       		
                result = check[i](i) && result;	
            }
     
            if (result) {
                alert('Le formulaire est bien rempli.');
                return true;
            } else {
            	
            return false;
            }
     
        };
     
        form_param.onreset = function() {
     
            for (var i = 0 ; i < inputsLength ; i++) {
                if (inputs[i].type == 'text' || inputs[i].type == 'number') {
                    inputs[i].className = '';
                }
            }
     
        };
     
    })();