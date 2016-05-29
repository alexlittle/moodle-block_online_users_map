<?php
	include_once("../../config.php");
    include_once($CFG->dirroot.'/blocks/online_users_map/lib.php');
    $id = required_param('id', PARAM_INT);
?>

var map = null;


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
 * Load the Google map
 */
function loadMap(){
	if(document.getElementById("block_online_users_googlemap") != null){
			<?php 
				$latlng = $CFG->block_online_users_map_centre_lat . "," . $CFG->block_online_users_map_centre_lng;
                if(isset($CFG->block_online_users_map_centre_user) && $CFG->block_online_users_map_centre_user == 1){ 
                    $coords = getCurrentUserLocation();
                    if ($coords){
                    	$latlng = $coords['lat'] . "," . $coords['lng'];
           			}
                } 
            ?>
			var myOptions = {
	          center: new google.maps.LatLng(<?php p($latlng); ?>),
	          zoom: <?php p($CFG->block_online_users_map_init_zoom); ?>,
	          mapTypeId: google.maps.MapTypeId.ROADMAP,
	          mapTypeControl: false,
	          streetViewControl: false
        	};
        	map = new google.maps.Map(document.getElementById("block_online_users_googlemap"), myOptions);
			//map.addControl(new GSmallZoomControl());

            
			loadUsers();	 
	}   
	
} 


function loadUsers(){
	request = "<?php p($CFG->wwwroot); ?>/blocks/online_users_map/getusers.php?id=<?php p($id); ?>&callback=loadUsersCallback";
	aObj = new JSONscriptRequest(request);
	aObj.buildScriptTag();
	aObj.addScriptTag();
}

function loadUsersCallback(jData){
	if(!jData){
		return;
	}
	var marker = jData;
	for (var i=0; i < marker.length; i++){
		createMarker(marker[i]);
	}
}

function createMarker(marker){
	if (marker.lat != "" && marker.lng != ""){
		var point = new google.maps.LatLng(marker.lat, marker.lng);
        if(marker.usersoffline + marker.usersonline == 1){
	        if(marker.usersonline == 1){
			    createSingleMarker(point,marker,true);
	        } else {
	           createSingleMarker(point,marker,false);
	        }
        } else {
       		if(marker.usersonline == 0){
			    createMultipleMarker(point,marker,false);
	        } else {
	           	createMultipleMarker(point,marker,true);
	        }
        }
	}
}


function createSingleMarker(point,marker,online){

	if(online){
    	m_image = 'online.png';
	} else {
		m_image = 'offline.png';
	}
	
	var image = new google.maps.MarkerImage('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/'+m_image,
		      new google.maps.Size(22, 15),
		      new google.maps.Point(0,0),
		      new google.maps.Point(7, 15));
		      
	var shadow = new google.maps.MarkerImage('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/shadow.png',
		      new google.maps.Size(22, 15),
		      new google.maps.Point(0,0),
		      new google.maps.Point(7, 15));

	if(marker.shownames){
		var title = marker.city + ": " + marker.users[0].fullname;
	} else {
		var title = marker.city + ": 1 user";
	}
	
	if(online){
		title += " online";
	} else {
		title += " offline";
	}
	
	var m = new google.maps.Marker({
							position: point, 
							map: map,
							shadow: shadow,
							icon: image,
							title: title});
}


function createMultipleMarker(point, marker, online){

	if(online){
    	m_image = 'online.png';
	} else {
		m_image = 'offline.png';
	}
	
	var image = new google.maps.MarkerImage('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/'+m_image,
		      new google.maps.Size(22, 15),
		      new google.maps.Point(0,0),
		      new google.maps.Point(7, 15));
	var shadow = new google.maps.MarkerImage('<?php p($CFG->wwwroot);?>/blocks/online_users_map/images/shadow.png',
		      new google.maps.Size(22, 15),
		      new google.maps.Point(0,0),
		      new google.maps.Point(7, 15));

	if(marker.shownames){
		var title = marker.city + ":<br/>";
		users = marker.users;
		for (var i=0; i < users.length; i++){
			title += users[i].fullname + " ";
			if(users[i].online == true){
				title += "(online)<br/>";
			} else {
				title += "(offline)<br/>";
			}
		}
		var infowindow = new google.maps.InfoWindow({
		    content: title,
		    maxWidth:200
		});
		
		var m = new google.maps.Marker({
					    	position: point, 
							map: map,
							shadow: shadow,
							icon: image,
							title: marker.city});

		google.maps.event.addListener(m, 'click', function() {
		  infowindow.open(map,m);
		});

	} else {
		var title = marker.city + ":\n" + marker.usersonline + " users online\n" + marker.usersoffline + " users offline";
		var m = new google.maps.Marker({
							position: point, 
							map: map,
							shadow: shadow,
							icon: image,
							title: title});
	}						
}

loadMap();