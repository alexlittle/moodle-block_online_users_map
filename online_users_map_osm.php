<?php
	include_once("../../config.php");
    include_once($CFG->dirroot.'/blocks/online_users_map/lib.php');
?>

var map = null;
var markers = null;
var popup;

<?php 
    if(isset($CFG->block_online_users_map_centre_user) && $CFG->block_online_users_map_centre_user == 1){ 
        $coords = getCurrentUserLocation();
        if ($coords){
?>
			var lat = <?php p($coords['lat']); ?>;
			var lon = <?php p($coords['lng']); ?>;
			var zoom = <?php p($CFG->block_online_users_map_init_zoom); ?>;
<?php   } else { ?>
			var lat = <?php p($CFG->block_online_users_map_centre_lat); ?>;
			var lon = <?php p($CFG->block_online_users_map_centre_lng); ?>;
			var zoom = <?php p($CFG->block_online_users_map_init_zoom); ?>;
<?php   } ?>
     
<?php
    } else {
?>
        var lat = <?php p($CFG->block_online_users_map_centre_lat); ?>;
		var lon = <?php p($CFG->block_online_users_map_centre_lng); ?>;
		var zoom = <?php p($CFG->block_online_users_map_init_zoom); ?>;
<?php
    } 
?>

function JSONscriptRequest(fullUrl) {
	// REST request path
	this.fullUrl = fullUrl; 
	// Keep IE from caching requests
	this.noCacheIE = '&noCacheIE=' + (new Date()).getTime();
	// Get the DOM location to put the script tag
	this.headLoc = document.getElementsByTagName("head").item(0);
	// Generate a unique script tag id
	this.scriptId = 'YJscriptId' + JSONscriptRequest.scriptCounter++;
}

// Static script ID counter
JSONscriptRequest.scriptCounter = 1;

// buildScriptTag method
//
JSONscriptRequest.prototype.buildScriptTag = function () {

	// Create the script tag
	this.scriptObj = document.createElement("script");
	
	// Add script object attributes
	this.scriptObj.setAttribute("type", "text/javascript");
	this.scriptObj.setAttribute("src", this.fullUrl + this.noCacheIE);
	this.scriptObj.setAttribute("id", this.scriptId);
}
 
// removeScriptTag method
// 
JSONscriptRequest.prototype.removeScriptTag = function () {
	// Destroy the script tag
	this.headLoc.removeChild(this.scriptObj);  
}

// addScriptTag method
//
JSONscriptRequest.prototype.addScriptTag = function () {
	// Create the script tag
	this.headLoc.appendChild(this.scriptObj);
}


/**
 * Load the OSM map
 */
function loadMap(){
	map = new OpenLayers.Map('block_online_users_osmmap',
              { maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
                numZoomLevels: 19,
                maxResolution: 156543.0399,
                units: 'm',
                projection: new OpenLayers.Projection("EPSG:900913"),
                displayProjection: new OpenLayers.Projection("EPSG:4326"),
                controls: [ new OpenLayers.Control.Navigation(), new OpenLayers.Control.ZoomPanel() ]
              });
 
 	map.addControl(new OpenLayers.Control.Attribution());
    var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
 
    map.addLayers([layerMapnik]);
 
    var lonLat = new OpenLayers.LonLat(lon, lat).transform(map.displayProjection,  map.projection);
    if (!map.getCenter()){
    	map.setCenter (lonLat, zoom);
    }
    markers = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markers);
    
	loadUsers();
} 


function loadUsers(){
	request = "<?php p($CFG->wwwroot); ?>/blocks/online_users_map/getusers.php?callback=loadUsersCallback";
	aObj = new JSONscriptRequest(request);
	aObj.buildScriptTag();
	aObj.addScriptTag();
}

function loadUsersCallback(jData){
	if(!jData){
		return;
	}
	var users = jData;
	for (i=0; i < users.length; i++){
		//create marker for each user
		console.log(users[i]);
		createMarker(users[i]);
	}
}

function createMarker(user){
	if (user.lat != "" && user.lng != ""){
		var lonLat = new OpenLayers.LonLat(user.lng,user.lat).transform(map.displayProjection,  map.projection);
		var size = new OpenLayers.Size(13,15);
        var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
        if(user.online == "true"){
        	var icon = new OpenLayers.Icon('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/online_noshadow.png',size,offset);
        } else {
        	var icon = new OpenLayers.Icon('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/offline_noshadow.png',size,offset);
        }
        
        mypopup = new OpenLayers.Popup("user",
                   lonLat,
                   new OpenLayers.Size(80,20),
                   user.fullname,
                   true);

        addMarker(lonLat, mypopup, icon.clone());
	}
}


function addMarker(ll, markerPopup, icon) {

    var feature = new OpenLayers.Feature(markers, ll); 
    feature.data.icon = icon;
            
    var marker = feature.createMarker();

    var markerClick = function (evt) {
        if (this.popup == null) {
            this.popup = markerPopup;
            map.addPopup(this.popup);
            this.popup.show();
        } else {
            this.popup.toggle();
        }
        currentPopup = this.popup;
        OpenLayers.Event.stop(evt);
    };
    marker.events.register("mousedown", feature, markerClick);

    markers.addMarker(marker);
}

loadMap();