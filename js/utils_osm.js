function draw_map() {

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
        zoom: typeof zoom == 'undefined' ? 10:zoom,
	controls:[zcontrol,
		  new OpenLayers.Control.KeyboardDefaults(),
		  new OpenLayers.Control.Navigation()],
    });

    if (typeof scale_line != 'undefined' && scale_line == true) {
	map.addControl(new OpenLayers.Control.ScaleLine({bottomOutUnits: ''}));
    }

    map.addLayer(new OpenLayers.Layer.OSM());

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
    if (typeof get_lon_lat != 'undefined' && get_lon_lat) {
	map.events.register("click", map, function(e) {
	    var position = map.getLonLatFromViewPortPx(e.xy);
	    position.transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));
	    alert(position.lon.toFixed(3) + ', ' + position.lat.toFixed(3));
	});
    }
}

if (typeof addLoadEvent == 'function') addLoadEvent(draw_map);
else window.onload = draw_map;
