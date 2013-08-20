function showForm() { 
  
    var displayAddParams = document.getElementById('addParams');
    var displayAdding = document.getElementById ('adding');
    displayAddParams.style.visibility = 'hidden';
    displayAdding.style.visibility = 'visible'; 
} 
  
function hideForm() { 
      
    var displayAddParams = document.getElementById('addParams');
    var displayAdding = document.getElementById ('adding');
    displayAddParams.style.visibility = 'visible';
    displayAdding.style.visibility = 'hidden';
} 
  
function showLoca() { 
      
    var displayloca = document.getElementById('loca');
    var putDraw = document.getElementById ('locadraw');
    displayloca.style.visibility = 'hidden';
    putDraw.style.visibility = 'visible'; 
} 
  
function hideLoca() { 
      
    var displayloca = document.getElementById('loca');
    var putDraw = document.getElementById ('locadraw');
    displayloca.style.visibility = 'visible';
    putDraw.style.visibility = 'hidden';
} 