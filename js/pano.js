if (img_prefix == undefined) var img_prefix = 'http://pano.tetaneutral.net/data/tsf2/vpongnian/tiles/ttn_mediatheque/mediatheque_70';
if (to_cap == undefined) var to_cap = 0;
if (to_ele == undefined) var to_ele = 0;
if (to_zoom == undefined) var to_zoom = 0;
if (cap == undefined) var cap = 0;
if (cap_min == undefined) var cap_min = cap;
if (cap_max == undefined) var cap_max = cap_min+360;
if (ref_points == undefined) var ref_points = new Array();
if (image_loop == undefined) var image_loop = true;


var debug_mode = false;
var canvas;
var cntext;
var point_list = new Array();
var zoom = 0;
var zooms = new Array();
var prev_zm;
var zm;
var tile = {width:256, height:256};
var ntiles = {x:228, y:9};
var border_width = 2;
var imageObj = new Array();

var last  = {x:0,y:0};
var shift = {x:0,y:0};
var mouse = {x:0,y:0};
var speed = {x:0,y:0};
var canvas_pos = {x:0,y:0};
var tmt;
var is_located = false;
var point_colors = {'pano_point' : '255,128,128',
		    'ref_point'  : '128,128,255',
		    'loc_point'  : '128,255,128',
		    'temporary'  : '255,255,128',
		    'unlocated'  : '255,255,255'};
var test = {x:0, y:0, i:100};


function nmodulo(val, div) {                // pour obtenir un modulo dans l'espace des nombres naturels N.
    return Math.floor((val%div+div)%div);   // il y a peut être plus simple, mais en attendant .... 
}

function fmodulo(val, div) {                // pour obtenir un modulo dans l'espace des nombres réels positifs.
    return (val%div+div)%div;               // il y a peut être plus simple, mais en attendant .... 
}

function distort_canvas(p, x, y) {
    if (p == 0) distort = 0;
    else {
	cntext.save();
	distort++;
        cntext.clearRect(0, 0, canvas.width, 2*canvas.height);
	var ratio = (canvas.width-2*distort)/canvas.width;
	var shift = canvas.height/2*(1-ratio);
	cntext.scale(1, ratio);
	if (p == 1) cntext.translate(0, 0);
	else if (p == -1) cntext.translate(0, 0);
	draw_image(x, y);
	cntext.restore();
	document.getElementById('res').innerHTML = 'distort : ' + distort + ' shift ' + shift + ' ratio : ' + ratio + '<br/>';
    }
}

function draw_image(ox, oy) {
    var ref_vals  = {x:last.x, y:last.y, zoom:zoom};
    ox = nmodulo(ox-canvas.width/2, zm.im.width);        // pour placer l'origine au centre du canvas 
    oy = Math.floor(oy-canvas.height/2);                 // pas de rebouclage vertical

    cntext.clearRect(0, 0, canvas.width, canvas.height);
    cntext.fillStyle = "rgba(128,128,128,0.8)";
    
    if (canvas.height > zm.im.height) {
	var fy = Math.floor((oy+canvas.height/2-zm.im.height/2)/(tile.height*zm.ntiles.y))*zm.ntiles.y;
	if (fy < 0) fy = 0; 
	var ly = fy + zm.ntiles.y;
    } else {
	var fy = Math.floor(oy/tile.height);
	var ly = Math.floor((oy+canvas.height+tile.height-1)/tile.height+1);
	if (fy < 0) fy = 0; 
	if (ly > zm.ntiles.y) ly = zm.ntiles.y; 
    }

    for (var j=fy; j<ly; j++) {
	var delta_y = (Math.floor(j/zm.ntiles.y) - Math.floor(fy/zm.ntiles.y)) * (tile.height - zm.last_tile.height);
	var dy = j*tile.height - oy - delta_y;
	var ny = j%ntiles.y;
	var wy = zm.tile.width;
	if (ny == zm.ntiles.y - 1) wy = zm.last_tile.height;

	var cpx = 0;
	var i = 0;
	var Nx = zm.ntiles.x;
	while (cpx < ox+canvas.width) {
	    var cur_width = zm.tile.width;
	    if (i%Nx == zm.ntiles.x-1) cur_width = zm.last_tile.width;
	    if (cpx >= ox-cur_width) {
		var nx = i%Nx;
		var idx = nx+'-'+ny+'-'+ref_vals.zoom;
		if (imageObj[idx] && imageObj[idx].complete) {
		    draw_tile(idx, cpx-ox, dy); // l'image est déja en mémoire, on force le dessin sans attendre.
		} else {
		    var fname = get_file_name(nx, ny, ref_vals.zoom);
		    imageObj[idx] = new Image();
		    imageObj[idx].src = fname;
		    var ts = zm.get_tile_size(nx, ny);
		    cntext.fillRect(cpx-ox, dy, ts.width, ts.height);
		    imageObj[idx].addEventListener('load', (function(ref, idx, dx, dy, ox, oy, ts) {
			return function() {        // closure nécéssaire pour gestion assynchronisme !!!
			    draw_tile_del(ref, idx, dx, dy, ox, oy, ts.width, ts.height);
			};
		    })(ref_vals, idx, cpx-ox, dy, ox, oy, ts), false);
		}
//		load_image(zoom, nx, ny, shx, shy, cpx-ox, dy, ox, oy);
	    }
	    cpx += cur_width;
	    i++;
	}
    }
    drawDecorations(ox, oy);
}

function draw_tile_del(ref, idx, tx, ty, ox, oy, twidth, theight) {
    if (ref.zoom == zoom && ref.x == last.x && ref.y == last.y) {
	draw_tile(idx, tx, ty);
	drawDecorations(ox, oy, tx, ty, twidth, theight);
    }
}

function draw_tile(idx, ox, oy) {
    var img = imageObj[idx];
    cntext.drawImage(img, ox, oy);
}

function drawDecorations(ox, oy, tx, ty, twidth, theight) {
    if (twidth) {
	cntext.save();
	cntext.beginPath();
        cntext.rect(tx, ty, twidth, theight);
        cntext.clip();
    } 
    var wgrd = zm.im.width/360;
    var od = ((ox+canvas.width/2)/wgrd)%360;
    var el = (zm.im.height/2 - (oy+canvas.height/2))/wgrd;
    cntext.fillStyle = "rgba(0,128,128,0.9)";
    cntext.strokeStyle = "rgb(255,255,255)";
    cntext.lineWidth = 1;
    cntext.fillRect(canvas.width/2-5, canvas.height/2-5, 10, 10);
    cntext.strokeRect(canvas.width/2-5, canvas.height/2-5, 10, 10);
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (zm.pt_list[i]['type'] != 'unlocated') {
	    cntext.fillStyle = 'rgba('+point_colors[zm.pt_list[i]['type']]+',0.5)';
	    var cx = nmodulo(zm.pt_list[i]['xc'] - ox, zm.im.width);
	    var cy = zm.pt_list[i]['yc'] - oy;
	    cntext.beginPath();
	    cntext.arc(cx, cy, 20, 0, 2*Math.PI, true);
	    cntext.fill();
	}
    }

    //cntext.font = "20pt Arial";
    //cntext.fillRect(0, 0, 200, 20);
    //cntext.fillStyle = "rgb(255,0,0)";
    //cntext.fillText(od.toFixed(2), 5, 20);
    //for (i=0; i<canvas.width/wgrd; i++) {
	//cntext.strokeRect(i*wgrd, 0, wgrd, 20);
    //}
    if (twidth) {
	cntext.restore();
    }
    
}

function insert_drawn_point(lat,lon,alt) {
	
	var rt = 6371;  // Rayon de la terre
    var alt1 = document.getElementById('pos_alt').childNodes[0].nodeValue;
    var lat1 = document.getElementById('pos_lat').childNodes[0].nodeValue*Math.PI/180;
    var lon1 = document.getElementById('pos_lon').childNodes[0].nodeValue*Math.PI/180;
    var alt2 = alt;
    var lat2 = lat*Math.PI/180;
    var lon2 = lon*Math.PI/180;
    
    var dLat = lat2-lat1;
    var dLon = lon2-lon1; 
   
    var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.sin(dLon/2)*Math.sin(dLon/2)*Math.cos(lat1)*Math.cos(lat2);  // 
    var angle = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = angle*rt;                    // distance du point en Kms
   
    var y = Math.sin(dLon) * Math.cos(lat2);
    var x = Math.cos(lat1)*Math.sin(lat2) - Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
    var cap = Math.atan2(y,x);                 // cap pour atteindre le point en radians
    var e = Math.atan2((alt2 - alt1)/1000 - d*d/(2*rt),d);  // angle de l'élévation en radians
    
    return {d:d, cap:cap*180/Math.PI, ele:e*180/Math.PI};   // les résultats sont en degrés
}

localate_point = function () {
	
    var lat = document.getElementById("loca_latitude").value;
    var lon = document.getElementById("loca_longitude").value;
    var alt = document.getElementById("loca_altitude").value;
    if (lat == '' || isNaN(lat) || lat <= -90 || lat > 90) {
	alert("La latitude "+lat+"n'est pas correcte");
	return;
    }
    if (lat == '' || isNaN(lon) || lon <= -180 || lon > 180) {
	alert("La longitude "+lon+"n'est pas correcte");
	return;
    }
    if (lat == '' || isNaN(alt) || alt < -400) {
	alert("l'altitude "+alt+"n'est pas correcte");
	return;
    }
	    var opt_ced = new Array();
	    opt_dce = insert_drawn_point(lat,lon,alt);
	    // -----Première solution : afficher dynamiquement le point !
	    var d = opt_dce.d;
	    var cap = opt_dce.cap;
	    var ele = opt_dce.ele;
	    
	    display_temp(d, cap, ele);
	   
}

function display_temp(d,cap,ele) {
   
    point_list[point_list.length] = new Array("point temporaire", d,cap,ele, "temporary");
    reset_zooms();
    putImage(last.x, last.y);
}

function arrayUnset(array, value){
    array.splice(array.indexOf(value), 1);
}

erase_point = function() {
	
	for (var i=0; i<point_list.length; i++) {
		if(point_list[i][0] == "point temporaire"){
			arrayUnset(point_list,point_list[i]);
			loop = erase_point();
		}	
	}	
	reset_zooms();
    putImage(last.x, last.y);   
}

function get_file_name(x, y, z) { // recherche du fichier correspondant au zoom et à la position
    var prm = [z, x, y];
    var fname = img_prefix;
    for (var i = 0; i < prm.length; i++) {
	fname += '_';
	if (prm[i] < 10) fname += '00';
	else if (prm[i] < 100) fname += '0';
	fname += prm[i];
    }
    fname += '.jpg';
    return fname;
}

function keys(key) {
	
    hide_links();
    evt = key || event;
    //evt.preventDefault();
    //evt.stopPropagation();
    if (!key) {
	key = window.event;
	key.which = key.keyCode;
    }
//    alert(key);
//    if (!evt.shiftKey) return;
    switch (key.which) {
    /*case 66: // b
	alert(key.pageX);
	test.x=tile.width*(ntiles.x-3);
	test.y=0;
	putImage(test.x, test.y);
	return;
    case 67: // c
	test.x=0;
	test.y=tile.height*(ntiles.y-3);
	putImage(test.x, test.y);
	return;*/
    case 36: // home
	putImage(0, zm.im.height/2);
	return;
    case 35: // end
	putImage(last.x+zm.im.width/2, last.y);
	return;
    case 39: // left
	putImage(last.x+40, last.y);
	return;
    case 40: // up
	putImage(last.x, last.y+20);
	return;
    case 37: // right
	putImage(last.x-40, last.y);
	return;
    case 38: // down
	putImage(last.x, last.y-20);
	return;
    case 33: // pageup
	zoom_control.value--;
	change_zoom()
	return;
    case 34: // pagedown
	zoom_control.value++;
	change_zoom()
	return;
    default:
//	alert(key.which)
	return;
    }
}

function onImageClick(e) {
    hide_contextmenu();
    shift.x = last.x;
    shift.y = last.y;
    speed.x = 0;
    speed.y = 0;
    mouse.x = e.pageX;
    mouse.y = e.pageY;
    clearTimeout(tmt);  //arrêt de l'éffet eventuel d'amorti en cours.
    canvas.addEventListener('mousemove', stickImage, false);
    canvas.addEventListener('mouseup', launchImage, false);
    //canvas.addEventListener('mouseout', launchImage, false);
    canvas.style.cursor='move';
    //document.onmousemove = stickImage;
    document.onmouseup = launchImage;
    hide_links();
}


function stickImage(e) {
    var xs = mouse.x - e.pageX + shift.x;
    var ys = mouse.y - e.pageY + shift.y;
    speed.x = xs - last.x;  //mémorisation des vitesses horizontales
    speed.y = ys - last.y;  //et verticales lors de ce déplacement
    putImage(xs, ys);
}

function launchImage(e) {
    distort_canvas(0);
    canvas.removeEventListener('mousemove', stickImage, false);
    //document.onmousemove = null;
    shift.x = e.pageX - mouse.x + shift.x;
    shift.y = e.pageY - mouse.y + shift.y;
    tmt = setTimeout(inertialImage, 100);
}

function putImage(x, y) { // est destiné à permettre l'effet d'amortissement par la mémorisation de la position courante.
    if (!zm.is_updated) return; 
    if (x >= zm.im.width) {   // rebouclage horizontal
	shift.x -= zm.im.width;
	x -= zm.im.width;
    } else if (x < 0) {
	shift.x += zm.im.width;
	x += zm.im.width;
    }
    if (y >= zm.im.height) {   // pas de rebouclage vertical mais blocage
	//distort_canvas(1, x, y);
	shift.y = zm.im.height-1;
	y = zm.im.height-1;
    } else if (y < 0) {
	//distort_canvas(-1, x, y);
	shift.y = 0;
	y = 0;
    }

    last.x = x;
    last.y = y;
    draw_image(x, y);
}

function inertialImage() {
    speed.x *= 0.9;
    speed.y *= 0.9;
    if (Math.abs(speed.x) > 2 || Math.abs(speed.y) > 2) {
	putImage(last.x+speed.x, last.y+speed.y);
	tmt = setTimeout(inertialImage, 100);
    } else {
	show_links();
    }
}

function tri_ref_points(v1, v2) {
    return v1['x'] - v2['x'];
}



function tzoom(zv) {
    this.value = zv;
    this.ntiles = {x:0,y:0};
    this.tile = {width:0,height:0};
    this.last_tile = {width:0,height:0};
    this.max_tile = {width:0,height:0};
    this.im = {width:0,height:0};
    this.is_updated = false;

    this.refresh = function() {
	this.im.visible_width = this.tile.width*(this.ntiles.x-1)+this.last_tile.width;
	this.is_updated = true;

	this.im.width = this.im.visible_width;
	this.im.height = this.tile.height*(this.ntiles.y-1)+this.last_tile.height;
	if (this.last_tile.width > this.tile.width) this.max_tile.width = this.im.last_tile.width;
	else this.max_tile.width = this.tile.width;
	if (this.last_tile.height > this.tile.height) this.max_tile.height = this.im.last_tile.height;
	else this.max_tile.height = this.tile.height;

	var ord_pts = new Array();
	i=0;
	for(var label in ref_points) {
	    ord_pts[i++] = ref_points[label]
	}
	ord_pts = ord_pts.sort(tri_ref_points);
	is_located = i > 1 || image_loop && i > 0;

	var alpha_domain = {start:0, end:360}; 
	this.pixel_y_ratio = this.im.width/360;
	if (is_located) {
	    this.ref_pixels = new Array;
	    this.ref_pixels[0] = new Array();    // Attention il faut compter un intervalle de plus !
	    for (var i=0; i < ord_pts.length; i++) { // premier parcours pour les paramètres cap/x
		this.ref_pixels[i+1] = new Array();
		this.ref_pixels[i+1].x = Math.floor(ord_pts[i].x*this.im.width);
		this.ref_pixels[i+1].cap = fmodulo(ord_pts[i].cap, 360);
		if (i != ord_pts.length-1) {
		    this.ref_pixels[i+1].ratio_x = (ord_pts[i+1].x - ord_pts[i].x)/fmodulo(ord_pts[i+1].cap - ord_pts[i].cap, 360)*this.im.width;
		}
	    }
	    if (image_loop == true) {
		var dpix = this.im.width;
		var dangle = 360;
		if (ord_pts.length > 1) {
		    dpix = this.im.width - this.ref_pixels[this.ref_pixels.length-1].x + this.ref_pixels[1].x;
		    dangle = fmodulo(this.ref_pixels[1].cap - this.ref_pixels[this.ref_pixels.length-1].cap, 360);
		}
		this.ref_pixels[0].ratio_x = dpix/dangle;
		this.ref_pixels[ord_pts.length].ratio_x = this.ref_pixels[0].ratio_x;
		dpix = this.im.width - this.ref_pixels[ord_pts.length].x;
		this.ref_pixels[0].cap = fmodulo(this.ref_pixels[ord_pts.length].cap + dpix / this.ref_pixels[0].ratio_x, 360);
	    } else {
		this.ref_pixels[0].ratio_x = this.ref_pixels[1].ratio_x;
		this.ref_pixels[ord_pts.length].ratio_x = this.ref_pixels[ord_pts.length-1].ratio_x;
		this.ref_pixels[0].cap = fmodulo(this.ref_pixels[1].cap - this.ref_pixels[1].x / this.ref_pixels[1].ratio_x, 360);
		alpha_domain.start = this.ref_pixels[0].cap;
		alpha_domain.end = fmodulo(this.ref_pixels[ord_pts.length].cap+(this.im.width-this.ref_pixels[ord_pts.length].x)/this.ref_pixels[ord_pts.length].ratio_x, 360);
		this.pixel_y_ratio = this.im.width/fmodulo(alpha_domain.end-alpha_domain.start, 360);
	    }
	    this.ref_pixels[0].x = 0;

	    for (var i=0; i < ord_pts.length; i++) { // second parcours pour les paramètres elevation/y
		this.ref_pixels[i+1].shift_y = Math.floor(this.pixel_y_ratio*ord_pts[i].ele - ord_pts[i].y*this.im.height);
		if (i != ord_pts.length-1) {
		    var next_shift = Math.floor(this.pixel_y_ratio*ord_pts[i+1].ele - ord_pts[i+1].y*this.im.height);
		    this.ref_pixels[i+1].dshft_y = (next_shift - this.ref_pixels[i+1].shift_y)/(this.ref_pixels[i+2].x - this.ref_pixels[i+1].x);
		}
	    }

	    if (image_loop == true) {
		var dpix  = this.im.width;
		var delt = 0;
		if (ord_pts.length > 1) {
		    dpix  = this.im.width - this.ref_pixels[this.ref_pixels.length-1].x + this.ref_pixels[1].x;
		    delt = this.ref_pixels[this.ref_pixels.length-1].shift_y - this.ref_pixels[1].shift_y;
		}
		this.ref_pixels[0].dshft_y = delt/dpix;
		this.ref_pixels[ord_pts.length].dshft_y = this.ref_pixels[0].dshft_y;
		dpix = this.im.width - this.ref_pixels[ord_pts.length].x;
		this.ref_pixels[0].shift_y = this.ref_pixels[ord_pts.length].shift_y - dpix*this.ref_pixels[0].dshft_y;
	    } else {
		this.ref_pixels[0].shift_y = this.ref_pixels[1].shift_y;
		this.ref_pixels[ord_pts.length].shift_y = this.ref_pixels[ord_pts.length-1].shift_y;
		this.ref_pixels[0].dshft_y = 0;
		this.ref_pixels[ord_pts.length].dshft_y = 0;
	    }

	    if (debug_mode) {
		var res = document.getElementById('res');
		res.innerHTML = 'liste des '+this.ref_pixels.length+' valeurs de correction (image = '+this.im.width+'x'+this.im.height+') zoom = '+this.value+':<br/>';
		for (var i=0; i < this.ref_pixels.length; i++) { // pour le debug
		    res.innerHTML += '<p>point '+i+' :</p><ul>';
		    for (var key in this.ref_pixels[i]) { // pour le debug
			res.innerHTML += '<li>'+key + '['+i+'] = '+this.ref_pixels[i][key]+'</li>';
		    }
		    if (i != this.ref_pixels.length-1) {
			var tx0 = this.ref_pixels[i+1].x-1;
			//var ty0 = this.ref_pixels[i+1].shift_y;
			var ty0 = 0;
		    } else {
			var tx0 = this.im.width-1;
			var ty0 = 0;
		    }
		    res.innerHTML += '</ul><p>test sur : '+tx0+','+ty0+'</p>';
		    var tst = this.get_cap_ele(tx0, ty0);
		    res.innerHTML += '<p>cap:'+tst.cap+', shift:'+tst.ele+'</p>';
		    var tst2 = this.get_pos_xy(tst.cap, tst.ele);
		    res.innerHTML += '</ul><p>x:'+tst2.x+', y:'+tst2.y+'</p>';
		}
	    }
	}

	this.pt_list = new Array();
	for (var i=0; i<point_list.length; i++) {
	    var lbl = point_list[i][0];
	    var dst = point_list[i][1];
	    var cap = point_list[i][2];
	    var ele = point_list[i][3];
	    var lnk = point_list[i][4];
	    var typ = 'unlocated';
	    var rxy = this.get_pos_xy(cap, ele);
	    var is_visible = fmodulo(cap - alpha_domain.start, 360) <= fmodulo(alpha_domain.end - alpha_domain.start -0.0001, 360)+0.0001 && is_located;

	    this.pt_list[i] = new Array();
	    if (ref_points[lbl] != undefined) {
		typ = 'ref_point';
		if (!is_located) rxy = {x:ref_points[lbl].x*this.im.width, y:ref_points[lbl].y*this.im.height}
	    } else if(lnk == '' && is_visible && lbl != 'point temporaire') {
		typ = 'loc_point';
	    }else if(is_visible && lbl =='point temporaire') {
	    typ = 'temporary';
	    
	    } else if(is_visible) {
		typ = 'pano_point';
		lnk += '&to_zoom='+this.value;
	    } 
	    this.pt_list[i]['type'] = typ;
	    this.pt_list[i]['cap'] = cap;
	    this.pt_list[i]['ele'] = ele;
	    this.pt_list[i]['dist'] = dst;
	    this.pt_list[i]['label'] = lbl;
	    this.pt_list[i]['lnk'] = lnk;
	    this.pt_list[i]['xc'] = rxy.x;
	    this.pt_list[i]['yc'] = Math.floor(this.im.height/2 - rxy.y);
	}
    }
    
    this.get_tile_size = function(nx, ny) {
	var res = {width:0, height:0};
	if (nx == this.ntiles.x-1) res.width = this.last_tile.width;
	else res.width = this.tile.width;
	if (ny == this.ntiles.y-1) res.height = this.last_tile.height;
	else res.height = this.tile.height;
	return res;
    }
    
    this.get_cap_ele = function(px, py) {               // recherche d'un cap et d'une élévation à partir d'un pixel X,Y.
	if (is_located) {
	    for (var i=0; i < this.ref_pixels.length; i++) {
		if (i == this.ref_pixels.length - 1 || px < this.ref_pixels[i+1].x) {
		    var dpix = px-this.ref_pixels[i].x;
		    var cp = fmodulo(this.ref_pixels[i].cap + dpix/this.ref_pixels[i].ratio_x, 360);
		    var el = (py+this.ref_pixels[i].shift_y+this.ref_pixels[i].dshft_y*dpix)/this.pixel_y_ratio;
		    return {cap:cp, ele:el};
		}
	    }
	} else {
	    var cp = 360*px/this.im.width;
	    var el = 360*py/this.im.height;
	    return {cap:cp, ele:el};
	}
    }
    
    this.get_pos_xy = function(cap, ele) {                  // recherche des coordonnées pixel à partir d'un cap et d'une élévation.
	if (is_located) {
	    var dcap = fmodulo(cap-this.ref_pixels[0].cap, 360);
	    for (var i=0; i < this.ref_pixels.length; i++) {
		if (i == this.ref_pixels.length - 1 || dcap < fmodulo(this.ref_pixels[i+1].cap-this.ref_pixels[0].cap, 360)) {
		    var px = this.ref_pixels[i].x + this.ref_pixels[i].ratio_x*fmodulo(cap - this.ref_pixels[i].cap, 360);
		    var dpix = px-this.ref_pixels[i].x;
		    var py = this.pixel_y_ratio*ele - this.ref_pixels[i].shift_y - this.ref_pixels[i].dshft_y*dpix;
		    return {x:px, y:py};
		}
	    }
	} else {
	    var px = fmodulo(cap, 360)/360*this.im.width;
	    var py = ele/360*this.im.height;
	    return {x:px, y:py};
	}
    }
}

function reset_zooms () {
    for(i=0; i<zooms.length; i++) zooms[i].is_updated = false;
    zm.refresh();
}

function wheel_zoom (event) {
    var zshift = {x:0, y:0};
    if (event.pageX != undefined && event.pageX != undefined) {
	zshift.x = event.pageX-canvas.width/2-canvas_pos.x;
	zshift.y = event.pageY-canvas.height/2-canvas_pos.y;
    }
    event.preventDefault();
    if (event.wheelDelta < 0 && zoom_control.value < zoom_control.max) {
	zoom_control.value++;
	change_zoom(zshift.x, zshift.y);
    } else if (event.wheelDelta > 0 && zoom_control.value > zoom_control.min) {
	zoom_control.value--; 
	change_zoom(zshift.x, zshift.y);
    }
}

function change_zoom(shx, shy) {
    var zoom_control = document.getElementById("zoom_ctrl");
    var v = zoom_control.value;

    prev_zm = zm;

    if (zooms[v]) {
	if (!zooms[v].is_updated) zooms[v].refresh();
    } else {
	zooms[v] = new tzoom(v);
    }

    if (zooms[v].is_updated) {
	if (shx == undefined || shy == undefined) {
	    shx=0;
	    shy=0;
	}
	zm = zooms[v];
	var px = (last.x+shx)*zm.im.width/prev_zm.im.width - shx;
	var py = (last.y+shy)*zm.im.height/prev_zm.im.height - shy;
	if (py < zm.im.height && py >= 0) {
	    zoom = zm.value;
	    tile = zm.tile;
	    ntiles = zm.ntiles;
	    putImage(px, py);
	} else {
	    zm = prev_zm;
	    zoom_control.value = zm.value;
	}
    }
}

function change_angle() {
    var elvtn_control = document.getElementById('elvtn_ctrl');
    var angle_control = document.getElementById('angle_ctrl');
    var resxy = zm.get_pos_xy(angle_control.value, elvtn_control.value);
    var pos_x = resxy.x;
    var pos_y = Math.floor(zm.im.height/2 - resxy.y);
    putImage(pos_x, pos_y);
}

function check_prox(x, y, r) {   //verification si un point de coordonnées x, y est bien dans un cercle de rayon r centré en X,Y. 
    return Math.sqrt(x*x + y*y) < r;
}

function check_links(e) {
    var mouse_x = e.pageX-canvas_pos.x;
    var mouse_y = e.pageY-canvas_pos.y;
    var pos_x = nmodulo(last.x + mouse_x - canvas.width/2, zm.im.width);
    var pos_y = last.y + mouse_y - canvas.height/2;
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (is_located && zm.pt_list[i]['type'] == 'pano_point') {
	    if (check_prox(zm.pt_list[i]['xc']-pos_x, zm.pt_list[i]['yc']-pos_y, 20)) {
		if (zm.pt_list[i]['lnk'] != '') window.location = zm.pt_list[i]['lnk'];
		break;
	    }
	}
    }
}

function display_links(e) {
    var info = document.getElementById('info');
    var mouse_x = e.pageX-canvas_pos.x;
    var mouse_y = e.pageY-canvas_pos.y;
    var pos_x = nmodulo(last.x + mouse_x - canvas.width/2, zm.im.width);
    var pos_y = last.y + mouse_y - canvas.height/2;
    //var cap = ((pos_x/zm.im.width)*360).toFixed(2);
    var res = zm.get_cap_ele(pos_x, zm.im.height/2 - pos_y);
    //var ele = ((zm.im.height/2 - pos_y)/zm.im.width)*360;
    info.innerHTML = 'élévation : '+res.ele.toFixed(2)+'<br/>cap : '+res.cap.toFixed(2);
    info.style.top = e.pageY+'px';
    info.style.left = e.pageX+'px';
    info.style.backgroundColor = '#FFC';
    info.style.display = 'block';
    canvas.style.cursor='crosshair';
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (is_located || zm.pt_list[i]['type'] == 'ref_point') {
	    if (check_prox(zm.pt_list[i]['xc']-pos_x, zm.pt_list[i]['yc']-pos_y, 20)) {
		info.innerHTML = zm.pt_list[i]['label'];
		if (zm.pt_list[i]['dist'] < 10) var dst = Math.round(zm.pt_list[i]['dist']*1000)+' m';
		else var dst = zm.pt_list[i]['dist'].toFixed(1)+' kms';
		info.innerHTML += '<br/> à ' + dst;
		info.style.backgroundColor = 'rgb('+point_colors[zm.pt_list[i]['type']]+')';
		canvas.style.cursor='auto';
		break;
	    }
	}
    }
}

function hide_links() {
    canvas.removeEventListener( "mousemove", display_links, false);
    var info = document.getElementById('info');
    info.style.display = 'none';
}

function show_links() {
    canvas.addEventListener( "mousemove", display_links, false);
//    var info = document.getElementById('info');
//    info.style.display = 'block';
}

function hide_contextmenu() {
    document.getElementById('insert').style.display = 'none';
}

function manage_ref_points(e) {
    //event.preventDefault();
    //event.stopPropagation();
    var insrt = document.getElementById('insert');
    document.getElementById('do-cancel').onclick = hide_contextmenu;
    insrt.style.left = e.pageX+'px';
    insrt.style.top = e.pageY+'px';
    insrt.style.display = 'block';
    var sel_pt = document.getElementById('sel_point');
    var do_insert = document.getElementById('do-insert');
    var do_delete = document.getElementById('do-delete');
    var pos_x = nmodulo(last.x + e.pageX - canvas_pos.x - canvas.width/2, zm.im.width);
    var pos_y = last.y + e.pageY - canvas_pos.y - canvas.height/2;
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (zm.pt_list[i]['type'] == 'ref_point') {
	    if (check_prox(zm.pt_list[i]['xc']-pos_x, zm.pt_list[i]['yc']-pos_y, 20)) {
		sel_pt.value = zm.pt_list[i]['label'];
	    }
	}
    }
    do_delete.onclick = function() {delete_ref_point(insrt)};
    do_insert.onclick = function() {insert_ref_point(insrt, e.pageX-canvas_pos.x, e.pageY-canvas_pos.y)};
    return false;
}

function insert_ref_point(el, x, y) {
    var label;
    el.style.display = 'none';
    for(var i = 0; i < zm.pt_list.length; i++) {
	label = zm.pt_list[i]['label'];
	if(label == document.getElementById('sel_point').value) {
	    var posx = nmodulo(last.x + x - canvas.width/2, zm.im.width)/zm.im.width;
	    var posy = 0.5 - (last.y + y - canvas.height/2)/zm.im.height;
	    var pval = {x:posx, y:posy, cap:zm.pt_list[i]['cap'], ele:zm.pt_list[i]['ele'], label:label};
	    ref_points[label] = pval;
	    document.getElementById('res').innerHTML = '<h4>Dernier point entré</h4>';
	    document.getElementById('res').innerHTML += '<p>reference["'+label+'"] = '+posx.toFixed(5)+','+posy.toFixed(5)+'</p>';
	    reset_zooms();
 	    putImage(last.x, last.y);
	    break;
	}
    }
    show_result();
}

function show_result(clear_before) {
    var res = document.getElementById('res');
    var strg = '';
    for (var lbl in ref_points) {
	strg += '<li>reference["'+lbl+'"] = '+ref_points[lbl].x.toFixed(5)+','+ref_points[lbl].y.toFixed(5)+'</li>';
    }
    if (strg) strg = '<h3>Liste de tous les points de référence</h3>\n<ul>' + strg + '</ul>';
    if (clear_before) res.innerHTML = strg;
    else res.innerHTML += strg;
}

function delete_ref_point(el) {
    el.style.display = 'none';
    delete ref_points[document.getElementById('sel_point').value];
    reset_zooms();
    putImage(last.x, last.y);
    show_result(true);
}

function clean_canvas_events(e) {
    canvas.removeEventListener('mousemove', stickImage, false);
    document.getElementById('info').style.display = 'none';
    speed.x = 0;
    speed.y = 0;
}



canvas_set_size = function() {
    canvas.style.border = border_width+"px solid red";
    canvas.width = window.innerWidth-2*border_width;
    canvas.height = window.innerHeight-2*border_width;
    canvas_pos.x = canvas.offsetLeft+border_width;
    canvas_pos.y = canvas.offsetTop+border_width;
}

canvas_resize = function() {
    canvas_set_size();
    putImage(last.x, last.y);
}



function paramIn(e) {
	
	 e = e || window.event; 
	 var relatedTarget = e.relatedTarget || e.fromElement; 
	 
	 while (relatedTarget != adding && relatedTarget.nodeName != 'BODY' && relatedTarget != document && relatedTarget != localisation) {
	        relatedTarget = relatedTarget.parentNode;
	 }
	 
	 if (relatedTarget != adding && relatedTarget != localisation) {
		 document.removeEventListener('keydown', keys, false);
	 }
}

function paramOut(e) {
	 
    e = e || window.event; 
    var relatedTarget = e.relatedTarget || e.toElement; 
 
    while (relatedTarget != adding && relatedTarget.nodeName != 'BODY' && relatedTarget != document && relatedTarget != localisation) {
        relatedTarget = relatedTarget.parentNode;
    }
 
    if (relatedTarget != adding && relatedTarget != localisation) {
    	document.addEventListener('keydown', keys, false);
    }
 
}

window.onload = function() {
	
	localisation = document.getElementById("locadraw");
	adding = document.getElementById("adding");
    canvas = document.getElementById("mon-canvas");
    cntext = canvas.getContext("2d");
    canvas_set_size();
    canvas.addEventListener("click", check_links, false);
    //canvas.addEventListener("oncontextmenu", manage_ref_points, false);
    canvas.oncontextmenu = manage_ref_points;
    canvas.addEventListener("mouseout" , clean_canvas_events, false);
    show_links();

    var max_zoom = zooms.length - 1;
    zoom_control = document.getElementById("zoom_ctrl");
    zoom_control.onchange = change_zoom;
    zoom_control.max = max_zoom;
    if (to_zoom > max_zoom) to_zoom = Math.floor(max_zoom/2);
    zm = zooms[to_zoom];
    zoom_control.value = to_zoom;
    zm.refresh();

    zoom = zm.value;
    tile = zm.tile;
    ntiles = zm.ntiles;

    angle_control = document.getElementById("angle_ctrl");
    angle_control.value = to_cap;
    angle_control.onchange = change_angle;
    angle_control.onclick = change_angle;
    elvtn_control = document.getElementById("elvtn_ctrl");
    elvtn_control.value = to_ele;
    elvtn_control.onchange = change_angle;
    elvtn_control.onclick = change_angle;

    change_angle();
    loca_temp = document.getElementById("loca_button");
    loca_temp.onclick = localate_point;
    loca_erase = document.getElementById("loca_erase");
    loca_erase.onclick = erase_point;
    canvas.addEventListener('mousedown', onImageClick, false);
    document.addEventListener('keydown', keys, false);
    canvas.addEventListener('mousewheel', wheel_zoom, false);
    window.onresize = canvas_resize;
    adding.addEventListener('mouseover',paramIn,false);
    adding.addEventListener('mouseout',paramOut,false);
    localisation.addEventListener('mouseover',paramIn,false);
    localisation.addEventListener('mouseout',paramOut,false);
      
};
