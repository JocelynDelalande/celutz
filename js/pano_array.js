if (img_prefix == undefined) var img_prefix = './tiles/ttn_mediatheque/mediatheque_70';
if (to_cap == undefined) var to_cap = 0;
if (to_ele == undefined) var to_ele = 0;
if (cap == undefined) var cap = 0;
if (elevation == undefined) var elevation = 0;
if (cap_min == undefined) var cap_min = cap;
if (cap_max == undefined) var cap_max = cap_min+360;
if (ref_points == undefined) var ref_points = new Array();
if (image_loop == undefined) var image_loop = true;

var canvas;
var cntext;
var point_list = new Array();
var zoom;
var to_zoom;
var zooms = new Array();
var prev_zm;
var zm;
var tile = {width:256, height:256};
var ntiles = {x:228, y:9};
var border_width = 5;
var imageObj = new Array();

var last  = {x:0,y:0};
var shift = {x:0,y:0};
var mouse = {x:0,y:0};
var speed = {x:0,y:0};
var canvas_pos = {x:0,y:0};
var tmt;

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
    ox = nmodulo(ox-canvas.width/2, zm.im.width);        // pour placer l'origine au centre du canvas 
    oy = Math.floor(oy-canvas.height/2);                             // pas de rebouclage vertical

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
		load_image(nx, ny, cpx-ox, dy, ox, oy);
	    }
	    cpx += cur_width;
	    i++;
	}
    }
    drawDecorations(ox, oy);
}

function load_image(nx, ny, ox, oy, x, y) {
    var idx = nx+'-'+ny+'-'+zoom;
    if (imageObj[idx] && imageObj[idx].complete) {
	draw_tile(idx, ox, oy); // l'image est déja en mémoire, on force le dessin sans attendre.
    } else {
	var fname = get_file_name(nx, ny, zoom);
	imageObj[idx] = new Image();
	imageObj[idx].src = fname;
	var ts = zm.get_tile_size(nx, ny);
	cntext.fillRect(ox, oy, ts.width, ts.height);
	var dx = shift.x;
	var dy = shift.y;
	imageObj[idx].addEventListener('load', function() {draw_tile_del(zoom, dx, dy, idx, ox, oy, x, y)}, false);
    }
}

function draw_tile_del(ezoom, dx, dy, idx, ox, oy, x, y) {
    if (ezoom == zoom && dx == shift.x && dy == shift.y) {
	draw_tile(idx, ox, oy);
	drawDecorations(x, y);
    }
}

function draw_tile(idx, ox, oy) {
    var img = imageObj[idx];
    cntext.drawImage(img, ox, oy);
}

function drawDecorations(ox, oy) {
    var wgrd = zm.im.width/360;
    var od = ((ox+canvas.width/2)/wgrd)%360;
    var el = (zm.im.height/2 - (oy+canvas.height/2))/wgrd;
//    document.getElementById('angle_ctrl').value = od.toFixed(2);
//    document.getElementById('elvtn_ctrl').value = el.toFixed(2);
    cntext.fillStyle = "rgba(0,128,128,0.9)";
    cntext.strokeStyle = "rgb(255,255,255)";
    cntext.lineWidth = 1;
    cntext.fillRect(canvas.width/2-5, canvas.height/2-5, 10, 10);
    cntext.strokeRect(canvas.width/2-5, canvas.height/2-5, 10, 10);
    document.getElementById('res').innerHTML = '';
    for(var i = 0; i < zm.pt_list.length; i++) {
	var cx = nmodulo(zm.pt_list[i]['xc'] - ox, zm.im.width);
	var cy = zm.pt_list[i]['yc'] - oy;
	if (zm.pt_list[i]['lnk'] != undefined) cntext.fillStyle = "rgba(255,128,128,0.5)";
	else cntext.fillStyle = "rgba(128,255,128,0.5)";
	cntext.beginPath();
	cntext.arc(cx, cy, 20, 0, 2*Math.PI, true);
	cntext.fill();
	document.getElementById('res').innerHTML += 'cx : ' + cx + ' cy ' + cy + ' lnk : ' + zm.pt_list[i]['lnk'] + '<br/>';
    }

    //cntext.font = "20pt Arial";
    //cntext.fillRect(0, 0, 200, 20);
    //cntext.fillStyle = "rgb(255,0,0)";
    //cntext.fillText(od.toFixed(2), 5, 20);
    //for (i=0; i<canvas.width/wgrd; i++) {
	//cntext.strokeRect(i*wgrd, 0, wgrd, 20);
    //}
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
    event.preventDefault();
    event.stopPropagation();
    if (!key) {
	key = event;
	key.which = key.keyCode;
    }
    evt = key || event;
//    alert(key);
//    if (!evt.shiftKey) return;
    switch (key.which) {
    case 36: // home
	test.x=0;
	test.y=0;
	putImage(test.x, test.y);
	return;
    case 66: // b
	alert(key.pageX);
	test.x=tile.width*(ntiles.x-3);
	test.y=0;
	putImage(test.x, test.y);
	return;
    case 67: // c
	test.x=0;
	test.y=tile.height*(ntiles.y-3);
	putImage(test.x, test.y);
	return;
    case 35: // end
	test.x=tile.width*(ntiles.x-3);
	test.y=tile.height*(ntiles.y-3);
	putImage(test.x, test.y);
	return;
    case 39: // left
	test.x+=test.i;
	putImage(test.x, test.y);
	return;
    case 40: // up
	test.y+=test.i;
	putImage(test.x, test.y);
	return;
    case 37: // right
	test.x-=test.i;
	putImage(test.x, test.y);
	return;
    case 38: // down
	test.y-=test.i;
	putImage(test.x, test.y);
	return;
    case 33: // pageup
	test.y=0;
	putImage(test.x, test.y);
	return;
    case 34: // pagedown
	test.y=tile.height*(ntiles.y-3);
	putImage(test.x, test.y);
	return;
    default:
	//alert(key.which)
	return;
    }
}

function onImageClick(e) {
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
    show_links();
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

    draw_image(x, y);
    last.x = x;
    last.y = y;
}

function inertialImage() {
    speed.x *= 0.9;
    speed.y *= 0.9;
    if (Math.abs(speed.x) > 2 || Math.abs(speed.y) > 2) {
	putImage(last.x+speed.x, last.y+speed.y);
	tmt = setTimeout(inertialImage, 100);
    }
}

function tri_ref_points(v1, v2) {
    return v1['x'] - v2['x'];
}



function tzoom(zv) {
    this.ref_pixels = new Array;
    this.value = zv;
    this.ntiles = {x:0,y:0};
    this.tile = {width:0,height:0};
    this.last_tile = {width:0,height:0};
    this.max_tile = {width:0,height:0};
    this.im = {width:0,height:0};
    this.pt_list = new Array();
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

	this.ref_pixels[0] = new Array();    // Attention il faut compter un intervalle de plus !
	ord_pts = new Array();
	i=0;
	for(var label in ref_points) {
	    ord_pts[i] = ref_points[label]
	}
	ord_pts = ord_pts.sort(tri_ref_points);

	for (var i=0; i < ord_pts.length; i++) {
	    this.ref_pixels[i+1] = new Array();
	    if (i != ord_pts.length-1) {
		this.ref_pixels[i+1]['ratio_x'] = (ord_pts[i+1]['x'] - ord_pts[i]['x'])/fmodulo(ord_pts[i+1]['cap'] - ord_pts[i]['cap'], 360)*this.im.width;
		this.ref_pixels[i+1]['ratio_y'] = (ord_pts[i+1]['y'] - ord_pts[i]['y'])/fmodulo(ord_pts[i+1]['ele'] - ord_pts[i]['ele'], 360);
	    }
	    this.ref_pixels[i+1]['x'] = Math.floor(ord_pts[i]['x']*this.im.width);
	    this.ref_pixels[i+1]['cap'] = ord_pts[i]['cap'];
	    this.ref_pixels[i+1]['y'] = Math.floor(ord_pts[i]['y']*this.im.height);
	    this.ref_pixels[i+1]['ele'] = ord_pts[i]['ele'];
	}
	if (image_loop == true) {
	    var dpix = this.im.width;
	    var dangle = 360;
	    if (ord_pts.length > 1) {
		dpix = zm.im.width - this.ref_pixels[this.ref_pixels.length-1]['x'] + this.ref_pixels[1]['x'];
		dangle = fmodulo(this.ref_pixels[1]['cap'] - this.ref_pixels[this.ref_pixels.length-1]['cap'], 360);
	    }
	    this.ref_pixels[0]['ratio_x'] = dpix/dangle;
	    this.ref_pixels[ord_pts.length]['ratio_x'] = this.ref_pixels[0]['ratio_x'];
	    this.ref_pixels[ord_pts.length]['ratio_y'] = this.ref_pixels[0]['ratio_y'];
	    dpix = this.im.width - this.ref_pixels[ord_pts.length]['x'];
	    this.ref_pixels[0]['cap'] = this.ref_pixels[ord_pts.length]['cap'] + dpix / this.ref_pixels[0]['ratio_x'];
	} else {
	    this.ref_pixels[0]['ratio_x'] = this.ref_pixels[1]['ratio_x'];
	    this.ref_pixels[ord_pts.length]['ratio_x'] = this.ref_pixels[ord_pts.length-1]['ratio_x'];
	    this.ref_pixels[0]['ratio_y'] = this.ref_pixels[1]['ratio_y'];
	    this.ref_pixels[ord_pts.length]['ratio_y'] = this.ref_pixels[ord_pts.length-1]['ratio_y'];
	    this.ref_pixels[0]['cap'] = this.ref_pixels[1]['cap'] - this.ref_pixels[1]['x'] / this.ref_pixels[1]['ratio_x'];
	}
	this.ref_pixels[0]['x'] = 0;
	this.ref_pixels[0]['y'] = 0;
	this.ref_pixels[0]['ele'] = 0;


	for (var i=0; i<point_list.length; i++) {
	    this.pt_list[i] = new Array();
	    this.pt_list[i]['angle'] = point_list[i][2];
	    this.pt_list[i]['label'] = point_list[i][0];
	    this.pt_list[i]['xc'] = Math.floor(this.get_pxx(point_list[i][2], 360));
//	    var tmp = fmodulo(point_list[i][2], 360);
//	    this.pt_list[i]['xc'] = Math.floor(tmp * this.im.width/360);
	    this.pt_list[i]['yc'] = Math.floor(this.im.height/2 - (point_list[i][3] + elevation) * this.im.width/360);
	    if (point_list[i][4] != '') this.pt_list[i]['lnk'] = point_list[i][4]+'&to_zoom='+zv;
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
    
    this.get_cap = function(px) {                         // recherche du cap à partir d'un pixel.
	for (var i=0; i < this.ref_pixels.length; i++) {
	    if (i == this.ref_pixels.length - 1 || px < this.ref_pixels[i+1]['x']) {
		return fmodulo(this.ref_pixels[i]['cap']+(px-this.ref_pixels[i]['x'])/this.ref_pixels[i]['ratio_x'], 360);
	    }
	}
    }
    
    this.get_pxx = function(cap) {                        // recherche du pixel à partir d'un cap.
	var dcap = fmodulo(cap-this.ref_pixels[0]['cap'], 360);
	for (var i=0; i < this.ref_pixels.length; i++) {
	    if (i == this.ref_pixels.length - 1 || dcap < fmodulo(this.ref_pixels[i+1]['cap']-this.ref_pixels[0]['cap'], 360)) {
		return this.ref_pixels[i]['x'] + this.ref_pixels[i]['ratio_x']*fmodulo(cap - this.ref_pixels[i]['cap'], 360);
	    }
	}
    }
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
    var pos_x = zm.im.width*angle_control.value/360;
    var pos_y = Math.floor(zm.im.height/2 - zm.im.width*elvtn_control.value/360);
    putImage(pos_x, pos_y);
}

function check_links(e) {
    var mouse_x = e.pageX-canvas_pos.x;
    var mouse_y = e.pageY-canvas_pos.y;
    var pos_x = nmodulo(last.x + mouse_x - canvas.width/2, zm.im.width);
    var pos_y = last.y + mouse_y - canvas.height/2;
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (Math.sqrt((zm.pt_list[i]['xc'] - pos_x) * (zm.pt_list[i]['xc'] - pos_x) + (zm.pt_list[i]['yc'] - pos_y) * (zm.pt_list[i]['yc'] - pos_y)) < 20) {
	    if (zm.pt_list[i]['lnk'] != undefined) window.location = zm.pt_list[i]['lnk'];
	    break;
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
    var cap = zm.get_cap(pos_x).toFixed(2);
    var elev = (((zm.im.height/2 - pos_y)/zm.im.width)*360 - elevation).toFixed(2);
    info.innerHTML = 'élévation :'+elev+'<br/>cap :'+cap;
    info.style.top = e.pageY+'px';
    info.style.left = e.pageX+'px';
    info.style.backgroundColor = '#FFC';
    canvas.style.cursor='crosshair';
    for(var i = 0; i < zm.pt_list.length; i++) {
	if (Math.sqrt((zm.pt_list[i]['xc'] - pos_x) * (zm.pt_list[i]['xc'] - pos_x) + (zm.pt_list[i]['yc'] - pos_y) * (zm.pt_list[i]['yc'] - pos_y)) < 20) {
	    info.innerHTML = zm.pt_list[i]['label'];
	    info.style.backgroundColor = '#FC8';
	    canvas.style.cursor='auto';
	    break;
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
    var info = document.getElementById('info');
    info.style.display = 'block';
}

function show_insert_point(e) {
    event.preventDefault();
    event.stopPropagation();
    var insrt = document.getElementById('insert');
    document.getElementById('do-insert').onclick = function() {insert_point(insert, e.pageX-canvas_pos.x, e.pageY-canvas_pos.y)};
    document.getElementById('do-cancel').onclick = function() {insert.style.display = 'none'};
    insrt.style.left = e.pageX+'px';
    insrt.style.top = e.pageY+'px';
    insrt.style.display = 'block';
}

function insert_point(el, x, y) {
    el.style.display = 'none';
    for(var i = 0; i < zm.pt_list.length; i++) {
	var label = zm.pt_list[i]['label'];
	if(label == document.getElementById('sel_point').value) {
	    var posx = nmodulo(last.x + x - canvas.width/2, zm.im.width)/zm.im.width;
	    var posy = 0;
	    var pval = {x:posx, cap:zm.pt_list[i]['angle'], y:posy, label:label};
	    ref_points[label] = pval;
	    document.getElementById('res').innerHTML += zm.pt_list[i]['label'] + '. ' + posx + '=' + zm.pt_list[i]['angle'] + '<br/>';
 	    putImage(last.x, last.y);

//	    if (ref_points.length > 1) {
//		var pval0 = inserted_points[inserted_points.length-2];
//		var origin = fmodulo(pval0.angle - ((pval.angle - pval0.angle)/(pval.ratio - pval0.ratio))*pval0.ratio, 360);
//		var end = fmodulo(pval.angle + ((pval.angle - pval0.angle)/(pval.ratio - pval0.ratio))*(1-pval.ratio), 360);
//		document.getElementById('res').innerHTML += 'cap_min = ' + origin + '<br/>';
//		document.getElementById('res').innerHTML += 'cap_max = ' + end + '<br/>';
//	    }
	    break;
	}
    }
}

function clean_canvas_events(e) {
    canvas.removeEventListener('mousemove', stickImage, false);
    document.getElementById('info').style.display = 'none';
    speed.x = 0;
    speed.y = 0;
}

window.onload = function() {
    canvas = document.getElementById("mon-canvas");
    canvas.style.border = border_width+"px solid red";
    cntext = canvas.getContext("2d");
    canvas.width = window.innerWidth-200;
    canvas.height = window.innerHeight-20;
    canvas.addEventListener("click", check_links, false);
    canvas_pos.x = canvas.offsetLeft+border_width;
    canvas_pos.y = canvas.offsetTop+border_width;
    canvas.addEventListener("contextmenu", show_insert_point, false);
    canvas.addEventListener("mouseout", clean_canvas_events, false);

    show_links();

    var max_zoom = zooms.length - 1;
    zoom_control = document.getElementById("zoom_ctrl");
    zoom_control.onchange = change_zoom;
    zoom_control.max = max_zoom;
    if (to_zoom == undefined || to_zoom > max_zoom) to_zoom = Math.floor(max_zoom/2);
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
    angle_control.onkeyup = change_angle;
    elvtn_control = document.getElementById("elvtn_ctrl");
    elvtn_control.value = to_ele+elevation;
    elvtn_control.onchange = change_angle;
    elvtn_control.onclick = change_angle;
    elvtn_control.onkeyup = change_angle;

    change_angle();

    canvas.addEventListener('mousedown', onImageClick, false);
    addEventListener('keyup', keys, false);
    canvas.addEventListener('mousewheel', wheel_zoom, false);
};
