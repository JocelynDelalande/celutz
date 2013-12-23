
/** Draws the map for the "view this cap" feature
 */
function draw_cap_map(zoom) {

    var zcontrol;
    if (typeof zoom_control != 'undefined') {
	switch (zoom_control) {
	case 'full':
	    zcontrol = new OpenLayers.Control.PanZoomBar();
	    break;
	case 'light':
	default:
	    zcontrol = new OpenLayers.Control.Zoom();
	}
    } else zcontrol = new OpenLayers.Control.Zoom();

    var map = new OpenLayers.Map({
	    div: "map",
	    theme: null,
	    zoom: typeof zoom == 'undefined' ? 10:zoom,
	controls:[zcontrol,
		  new OpenLayers.Control.KeyboardDefaults(),
		  new OpenLayers.Control.Navigation()],
    });
	
	
    if (typeof scale_line != 'undefined' && scale_line == true) {
	map.addControl(new OpenLayers.Control.ScaleLine({bottomOutUnits: ''}));
    }
	map.celutz_addnew = false;

    if (typeof base_layers != 'undefined') {
	var layers = new OpenLayers.Control.LayerSwitcher();
	map.addControl(layers);
	for (var i = 0; i < base_layers.length; i++) {
	    map.addLayer(base_layers[i]);
	}

	// gestion du 45° google //
	function update_tilt() {
	    for (var i = 0; i < base_layers.length; i++) {
		if (base_layers[i].type == google.maps.MapTypeId.SATELLITE) {
		    base_layers[i].mapObject.setTilt(this.checked?1:0);
		    //alert((chkbx.checked?1:0)+'//'+i);
		    base_layers[i].removeMap;
		    base_layers[i].redraw;
		}
	    }
	}
	document.getElementById("tilt").onchange = update_tilt;
	// fin de gestion du 45° google //

	// autres tests
	function show_pos(e) {
	    alert(formatLonlats(map.getLonLatFromViewPortPx(e.xy)));
	}
	function set_pos(e) {
	    if(this.checked) {
		document.getElementById("position").style.display = 'none';
		map.events.register("click", map, show_pos);
	    } else {
		document.getElementById("position").style.display = 'block';
		map.events.unregister("click", map, show_pos);
	    }
	}
	    
	    
    var bt_pano = mk_new_pano_button(map);
	var panel = new OpenLayers.Control.Panel({
		div: document.getElementById("panel"),
    	default: bt_pano,
        createControlMarkup: function(control) {
            var button = document.createElement('button'),
                iconSpan = document.createElement('span'),
                textSpan = document.createElement('span');
            iconSpan.innerHTML = '&nbsp;';
            button.appendChild(iconSpan);
            if (control.text) {
                textSpan.innerHTML = control.text;
            }
            button.appendChild(textSpan);
            return button;
        }
    });

	function formatLonlats(lonLat) {
	    lonLat.transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));
            var lat = lonLat.lat;
            var lon = lonLat.lon;
	    var dist = OpenLayers.Util.distVincenty(lonLat, new OpenLayers.LonLat(ln.lon1, ln.lat1))*1000;
            return lat.toFixed(5) + ', ' + lon.toFixed(5) + ' à ' + parseInt(dist) + ' mètres';
	}

	map.addControl (new OpenLayers.Control.MousePosition({
	    div: document.getElementById("position"),
	    formatOutput: formatLonlats
	}));

    var history = new OpenLayers.Control.NavigationHistory({
	    previousOptions: {
		    title: "Go to previous map position",
		    text: "Préc."
	    },
	    nextOptions: {
		    title: "Go to next map position",
		    text: "Suiv."
	    },
    });
	    
	map.addControl(history);
	    panel.addControls([bt_pano, history.next, history.previous]);
	map.addControl(panel);

	document.getElementById("clic_pos").onchange = set_pos;
	layers.layersDiv.appendChild(document.getElementById("extra"));
	// fin des autres tests
    } else {
	map.addLayer(new OpenLayers.Layer.OSM());
    }

    if (typeof contour != 'undefined') contours = [contour];
    if (typeof contours == 'undefined') contours = new Array;
    for (var i = 0; i < contours.length; i++) {
	var ct = contours[i];
	var cntr = new OpenLayers.Layer.Vector("contour_"+i, {
	    strategies: [new OpenLayers.Strategy.Fixed()],
	    projection: new OpenLayers.Projection("EPSG:4326"),
	    styleMap: new OpenLayers.StyleMap({
		strokeWidth:     ct.strokeWidth,
		strokeColor:     ct.strokeColor,
		strokeOpacity:   ct.strokeOpacity,
		fillOpacity:     ct.fillOpacity,
		fillColor:       ct.fillColor
	    }),
	    protocol: new OpenLayers.Protocol.HTTP({
		url: ct.url,
		format: new OpenLayers.Format.OSM(),
	    }),
	    eventListeners: {
		"featuresadded": function () {
		    if (typeof fit_contours == 'undefined' || fit_contours) this.map.zoomToExtent(this.getDataExtent());
		}
	    }
	});
	map.addLayer(cntr);
    }

    if (typeof ref_line != 'undefined') ref_lines = [ref_line];
    if (typeof ref_lines != 'undefined') {
	if (typeof def_line_style == 'undefined') def_line_style = {};
	var def_ln = {
	    width:       def_line_style.width? def_line_style.width:2,
	    color:       def_line_style.color? def_line_style.color:'#00F',
	    length:      def_line_style.length? def_line_style.length:20000,
	    opacity:     def_line_style.opacity? def_line_style.opacity:1}

	var lineLayer = new OpenLayers.Layer.Vector("ref_lines");
	map.addControl(new OpenLayers.Control.DrawFeature(lineLayer, OpenLayers.Handler.Path));
	for (var i = 0; i < ref_lines.length; i++) {
	    var ln = ref_lines[i];
	    if(isNaN(ln.cap)) {
		var pt = {lon: ln.lon2, lat: ln.lat2};
	    } else {
		var LonLat = new OpenLayers.LonLat(ln.lon1, ln.lat1);
		var dist = ln.length? ln.length:def_ln.length;
		var pt = OpenLayers.Util.destinationVincenty(LonLat, ln.cap, dist);
	    }
	    var points = new Array(
		new OpenLayers.Geometry.Point(ln.lon1, ln.lat1),
		new OpenLayers.Geometry.Point(pt.lon, pt.lat)
	    );
	    points[0].transform("EPSG:4326", map.getProjectionObject());
	    points[1].transform("EPSG:4326", map.getProjectionObject());
	    var line = new OpenLayers.Geometry.LineString(points);

	    var style = {
		strokeColor:   ln.color? ln.color:def_ln.color,
		strokeWidth:   ln.width? ln.width:def_ln.width,
		strokeOpacity: ln.width? ln.opacity:def_ln.opacity
	    };

	    var lineFeature = new OpenLayers.Feature.Vector(line, null, style);
	    lineLayer.addFeatures([lineFeature]);
	}
	map.addLayer(lineLayer);
    }

    if (typeof ref_point != 'undefined') ref_points = [ref_point];
    if (typeof ref_points != 'undefined') {
	refpts_layer = new OpenLayers.Layer.Vector("ref_points", {projection: "EPSG:4326"});
	var selectMarkerControl = new OpenLayers.Control.SelectFeature(refpts_layer, {
	    onSelect: function(feature) {
		var le_popup = new OpenLayers.Popup.FramedCloud("Popup",
								feature.attributes.lonlat,
								null,
								feature.attributes.description,
								null,
								true);
		                                                //function() {selectMarkerControl.unselect(feature)});
		feature.popup = le_popup;
		map.addPopup(le_popup);
	    },
	    onUnselect: function(feature) {
		//alert(feature.id);
		map.removePopup(feature.popup);
		feature.popup.destroy();
		feature.popup = null;
	    },
	    multiple: true,
	    toggle: true,
	});
	map.addControl(selectMarkerControl);

	selectMarkerControl.activate();
	map.addLayer(refpts_layer);


	if (typeof def_points_style == 'undefined') def_points_style = {};
	var def_pt = {
	    icon_url:    def_points_style.icon_url,
	    icon_width:  def_points_style.icon_width,
	    icon_height: def_points_style.icon_height,
	    showPopup:   def_points_style.showPopup   ? def_points_style.showPopup:false,
	    icon_shiftX: def_points_style.icon_shiftX ? def_points_style.icon_shiftX:0,
	    icon_shiftY: def_points_style.icon_shiftY ? def_points_style.icon_shiftY:0,
	    opacity:     def_points_style.opacity ? def_points_style.opacity:1}

	for (var i = 0; i < ref_points.length; i++) {
	    var pt = ref_points[i];
            var ptGeo = new OpenLayers.Geometry.Point(pt.lon, pt.lat);
            ptGeo.transform("EPSG:4326", map.getProjectionObject());
	    var LonLat = new OpenLayers.LonLat(pt.lon, pt.lat).transform("EPSG:4326", map.getProjectionObject());
	    map.setCenter(LonLat);
	    var laFeature = new OpenLayers.Feature.Vector(ptGeo,
							  {description:pt.descr, lonlat: LonLat},
							  {externalGraphic: pt.icon_url?    pt.icon_url:def_pt.icon_url,
							   graphicWidth:    pt.icon_width?  pt.icon_width:def_pt.icon_width,
							   graphicHeight:   pt.icon_height? pt.icon_height:def_pt.icon_height,
							   graphicXOffset:  pt.icon_shiftX? pt.icon_shiftX:def_pt.icon_shiftX,
							   graphicYOffset:  pt.icon_shiftY? pt.icon_shiftY:def_pt.icon_shiftY,
							   graphicOpacity:  pt.opacity?     pt.opacity:def_pt.opacity,
							   title:           pt.title?       pt.title :''});
	    if (i == 0) elFeature = laFeature;
            refpts_layer.addFeatures(laFeature);
	    if (pt.showPopup) selectMarkerControl.select(laFeature);
	}
	if (typeof zoom == 'undefined') map.zoomToExtent(refpts_layer.getDataExtent());
    }
    if (typeof get_lon_lat != 'undefined' && get_lon_lat) {
	map.events.register("click", map, function(e) {
	    var position = map.getLonLatFromViewPortPx(e.xy);
	    position.transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));
	    alert(position.lat.toFixed(5) + ', ' + position.lon.toFixed(5));
	});
    }

	return map;
}

function list2css_color(vals) {
	return "rgb("+vals+")";
}

function mk_all_refpoints_layer() {
	// Put the same style as panorama view for points
	var points_style = new OpenLayers.StyleMap({
		pointRadius: 10,
		fillOpacity: 0.5,
	});
	
	var lookup = {};
	
	for (var k in  point_colors ) {
		var css_color = list2css_color(point_colors[k]);
		lookup[k] = {
			fillColor: css_color,
			strokeColor: css_color
		};
	}
	console.log(lookup);
	points_style.addUniqueValueRules("default", "type", lookup);	
	
	var layer = new OpenLayers.Layer.Vector(
		"Reference points",{
			projection: new OpenLayers.Projection("EPSG:4326"),
			strategies: [new OpenLayers.Strategy.Fixed()],
			protocol: new OpenLayers.Protocol.HTTP({
				url: 'ajax/all_points.php',
				format: new OpenLayers.Format.GeoJSON(),
			}),
			styleMap: points_style
		});
	return layer;
}


function mk_point_cloud(feature) {
	/* Takes a ref_point or loc_point and makes a dom element to be used as map
	 * popup
	 */
	var div = document.createElement('div');
	var ul = document.createElement('ul');
	var title = feature.attributes.name;
	var desc, link;
	
	if (feature.attributes.type == 'pano_point') {
		desc = 'photo panoramique';
		link = feature.attributes.view_url;
	} else {
		desc = 'point de référence';
	}
	div.innerHTML = '<p><strong>'+title+'</strong></p>';
	ul.innerHTML = '<li>'+desc+'</li>';
	
	if (link) {
		ul.innerHTML += '<li><a href="'+
			link+'" >Visualiser';
	}
	
	div.appendChild(ul);
	return div.innerHTML;
}

function add_refpoint_control(layer, map) {
	var selectControl ;
	selectControl = new OpenLayers.Control.SelectFeature(
		layer,{
			onSelect:function(feature) {
				var popup = new OpenLayers.Popup.FramedCloud(
					feature.attributes.name,
					feature.geometry.getBounds().getCenterLonLat(),
					null,
					mk_point_cloud(feature),
					null, true, function() {selectControl.unselect(feature);});
				feature.popup = popup;
				map.addPopup(popup);},

			onUnselect:function(feature) {
				map.removePopup(feature.popup);
				feature.popup.destroy();
				feature.popup = null;
			}});
	
	map.addControl(selectControl);
	selectControl.activate();
}


function mk_new_pano_button(map) {
	var btn = new OpenLayers.Control.Button({
		displayClass: 'olControlBtnNewPano',
		title: "Ajouter un nouveau panorama",
		text: "Photo",
		id: 'btn-new-pano',
		autoActivate: true,
		trigger: function() {start_add_new_pano(map)},
	});	
	return btn;
}


function add_pano_click_handler(callback) {
	var ctrlClass = OpenLayers.Class(OpenLayers.Control, {                
		defaultHandlerOptions: {
		    'single': false,
		    'double': true,
		    'pixelTolerance': 0,
		    'stopSingle': false,
		    'stopDouble': false
		},

		initialize: function(options)
		{
		    this.handlerOptions = OpenLayers.Util.extend(
		        {}, this.defaultHandlerOptions
		    );
		    
		    OpenLayers.Control.prototype.initialize.apply(this, arguments);
		    
		    this.handler = new OpenLayers.Handler.Click(
		        this, {
		            'click': callback
		        }, this.handlerOptions
		    );
		},
	});
	
	var control = new ctrlClass({
		        handlerOptions: {
		            "single": true,
		            "double": false
		        },
		autoActivate: true});
	
	return control;
}

function start_add_new_pano(map) {
	map.celutz_addnew = true;
	alert("cliquer sur un point de la carte pour choisir l'emplacement");
	document.body.style.cursor = 'crosshair';
	
	var control = add_pano_click_handler(function(evt) {
		var coord = map.getLonLatFromPixel(evt.xy);
		coord.transform(new OpenLayers.Projection("EPSG:900913"), 
		                new OpenLayers.Projection("EPSG:4326"));
		window.location = 'envoyer.php?lat='+coord.lat+'&lon='+coord.lon;
	});
	map.addControl(control);
}

