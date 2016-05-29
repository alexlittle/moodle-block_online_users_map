<?php

include_once($CFG->dirroot.'/lib/datalib.php');

/**
 * Generate the HTML content for the google map
 *
 * @return string HTML string to display google map
 */
 function get_html_googlemap(){
    global $CFG,$COURSE;
    $retStr = "<script src='https://maps.googleapis.com/maps/api/js?sensor=false' type='text/javascript'></script>";
    $retStr .= "<link rel='stylesheet' type='text/css' href='".$CFG->wwwroot."/blocks/online_users_map/style.css' />";
    $retStr .= "<div id='block_online_users_googlemap'></div>";
    $retStr .= "<script type='text/javascript' src='".$CFG->wwwroot."/blocks/online_users_map/online_users_map.php?id=".$COURSE->id."' defer='defer'></script>";
    return $retStr;
 }

/**
 * Generate the HTML content for the OSM map
 *
 * @return string HTML string to display OSM map
 */
 function get_html_osmmap(){
    global $CFG,$COURSE;
    $retStr = "<script type='text/javascript' src='".$CFG->wwwroot."/blocks/online_users_map/online_users_map_osm.php?id=".$COURSE->id."' defer='defer'></script>";
    $retStr .= "<script src='http://www.openlayers.org/api/OpenLayers.js'></script>";
    $retStr .= "<link rel='stylesheet' type='text/css' href='".$CFG->wwwroot."/blocks/online_users_map/style.css' />";
    $retStr .= "<script src='http://www.openstreetmap.org/openlayers/OpenStreetMap.js'></script>";
    $retStr .= "<div id='block_online_users_osmmap'></div>"; 
    return $retStr;
 }

/**
 * Updates the lat/lng for users
 * @uses $CFG,$DB
 */
function update_users_locations(){
	global $CFG,$DB;
    //get all the users without a lat/lng
    $sql = "SELECT u.id, u.city, u.country, boumc.id AS b_id, u.firstname, u.lastname 
                FROM {user} u
                LEFT OUTER JOIN {block_online_users_map} boumc
                ON  u.id = boumc.userid
                WHERE (boumc.id IS NULL
                OR u.city != boumc.city     
                OR u.country != boumc.country)
                AND u.city != ''";

     if($CFG->block_online_users_map_update_limit == 0){
        $results = $DB->get_records_sql($sql,array());
     } else {
        $results = $DB->get_records_sql($sql,array(),0,$CFG->block_online_users_map_update_limit);
     }
	
     if (!$results){
        if ($CFG->block_online_users_map_debug){
            echo "\nThere are no locations to update." ;  
        }
        return true;
     }
     //loop through results and get location for each user
     foreach ($results as $user){
        if ($CFG->block_online_users_map_debug){
            echo "\nUpdating location for ".$user->firstname." ".$user->lastname." (looking up: ".$user->city.",".$user->country." )...";  
        }
        //get the coordinates:
        $response = getURLContent($CFG->block_online_users_map_geonamesurl,"/search?username=".$CFG->block_online_users_map_geonamesusername."&maxRows=1&q=".urlencode($user->city)."&country=".urlencode($user->country));
        
        if($response != "" && $xml = simplexml_load_string($response)){
            $boumc = new StdClass;
            if (isset($xml->geoname->lat)){
                $boumc->userid = $user->id;
                $boumc->lat = floatval($xml->geoname->lat);
                $boumc->lng = floatval($xml->geoname->lng);
                $boumc->city = $user->city;
                $boumc->country = $user->country;
                
                //if existing record from block_online_users_map then update
                if (isset($user->b_id)){        
                    $boumc->id = $user->b_id;
                    $DB->update_record("block_online_users_map",$boumc);
                } else {            
                    //else create a new record
                    $DB->insert_record("block_online_users_map",$boumc);
                }
                if ($CFG->block_online_users_map_debug){
                    echo "\n\tlocation updated" ;  
                }
            } else {
               if ($CFG->block_online_users_map_debug){
                   echo "\n\tlocation not found in Geonames database" ;  
               } 
            }
        } else {
            if ($CFG->block_online_users_map_debug){
                echo "\n\tlocation not found due to no or invalid response" ;  
            }
        }
     }
}

/**
 * Gets the content of a url request
 * @uses $CFG
 * @return String body of the returned request
 */
function getURLContent($domain,$path){

	global $CFG;

	$message = "GET $domain$path HTTP/1.0\r\n";
	$msgaddress = str_replace("http://","",$domain);
	$message .= "Host: $msgaddress\r\n";
    $message .= "Connection: Close\r\n";
    $message .= "\r\n";
	
	if($CFG->proxyhost != "" && $CFG->proxyport != 0){
    	$address = $CFG->proxyhost;
    	$port = $CFG->proxyport;
	} else {
		$address = str_replace("http://","",$domain);
    	$port = 80;
	}

    /* Attempt to connect to the proxy server to retrieve the remote page */
    if(!$socket = fsockopen($address, $port, $errno, $errstring, 20)){
        echo "Couldn't connect to host $address: $errno: $errstring\n";
        return "";
    }

    fwrite($socket, $message);
    $content = "";
    while (!feof($socket)){
            $content .= fgets($socket, 1024);
    }

    fclose($socket);
    $retStr = extractBody($content);
    return $retStr;
}

/**
 * removes the headers from a url response
 * @return String body of the returned request
 */
function extractBody($response){

	$crlf = "\r\n";
	// split header and body
    $pos = strpos($response, $crlf . $crlf);
    if($pos === false){
   	    return($response);
    }

    $header = substr($response, 0, $pos);
    $body = substr($response, $pos + 2 * strlen($crlf));
    // parse headers
    $headers = array();
    $lines = explode($crlf, $header);

    foreach($lines as $line){
   	    if(($pos = strpos($line, ':')) !== false){
   		    $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
   	    }
    }

   	return $body;

}

/**
 * Gets the timetosee value
 * @uses $CFG
 * @return Integer
 */
function getTimeToShowUsers(){
	global $CFG;
	$timetoshowusers = 300; //Seconds default
    if (isset($CFG->block_online_users_map_timetosee)) {
        $timetoshowusers = $CFG->block_online_users_map_timetosee * 60;
    }
	return $timetoshowusers;
}


/**
 * Gets the lat/lng coords of the current user
 * @uses $CFG,$USER,$DB
 * @return Array of decimal
 */
function getCurrentUserLocation(){
    global $CFG,$USER,$DB;
    $coords = array();
    
    $sql = "SELECT boumc.userid, boumc.lat, boumc.lng 
    		FROM {block_online_users_map} boumc 
    		WHERE userid=?";
    $c = $DB->get_record_sql($sql,array($USER->id));
    if($c){
        $coords['lat'] = $c->lat;
        $coords['lng'] = $c->lng;
    }
    return $coords;
}

?>
