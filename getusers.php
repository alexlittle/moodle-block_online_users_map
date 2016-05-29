<?php

include_once("../../config.php");
include_once($CFG->dirroot.'/blocks/online_users_map/lib.php');

header("Content-type: text/plain");
$callback = optional_param('callback','',PARAM_ALPHA);
$id = required_param('id', PARAM_INT);

$timefrom = 100 * floor((time()-getTimeToShowUsers()) / 100); // Round to nearest 100 seconds for better query cache
$params = array('id' => $id);
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

// Get context so we can check capabilities.
$context = get_context_instance(CONTEXT_COURSE, $course->id);
	
//Calculate if we are in separate groups
$isseparategroups = ($course->groupmode == SEPARATEGROUPS
                     && $course->groupmodeforce
                     && !has_capability('moodle/site:accessallgroups', $context));

//Get the user current group
$currentgroup = $isseparategroups ? get_and_set_current_group($course, groupmode($course)) : NULL;

$groupmembers = "";
$groupselect = "";



if ($currentgroup !== NULL) {
    $groupmembers = " INNER JOIN {groups_members} gm ON gm.userid = u.id ";
    $groupselect = " AND gm.groupid = '$currentgroup'";
}

list($esql, $params) = get_enrolled_sql($context, NULL, $currentgroup, true);
    
if ($course->id == SITEID) {  // Site-level
    $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
    $from = "FROM {user} u,
             {block_online_users_map} boumc
             $groupmembers ";
    $where = "WHERE boumc.userid = u.id
                  $groupselect ";
    $order = "ORDER BY lastaccess DESC ";
        
} else { // Course-level and to include any offline users who have never logged in
    $contextlist = get_related_contexts_string($context);
    $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
    $from = "FROM mdl_user u
					INNER JOIN mdl_block_online_users_map boumc ON u.id = boumc.userid
                      $groupmembers 
        			LEFT JOIN {user_lastaccess} ul ON ul.userid = u.id ";
    $where =  "WHERE u.id IN (SELECT userid FROM {role_assignments} WHERE contextid $contextlist)
                   $groupselect ";
    $order = "ORDER BY lastaccess DESC ";
}

$groupby = "GROUP BY u.id, u.username, u.firstname, u.lastname, u.city, u.picture, boumc.lat, boumc.lng ";

$SQLwithLL = $select . $from . $where . $groupby . $order;

$pcontext = get_related_contexts_string($context);
$markers = array();

if ($pusers = $DB->get_records_sql($SQLwithLL, array(), 0, 100)) {   // We'll just take the most recent 100 maximum
    foreach ($pusers as $puser) {
    	if ($puser->lastaccess <= $timefrom && (!isset($CFG->block_online_users_map_show_offline) || $CFG->block_online_users_map_show_offline == 0)){
    		continue;
    	}
    	
        if (!isset($markers[$puser->lat +"," + $puser->lng])){
        	$marker = new stdClass();
        	$marker->lat = $puser->lat;
        	$marker->lng = $puser->lng;
        	$marker->city = $puser->city;
        	$marker->users = array();
        	$marker->shownames = false;
        	$marker->usersonline = 0;
        	$marker->usersoffline = 0;
        } else {
        	$marker = $markers[$puser->lat +"," + $puser->lng];
        }

        $user = new stdClass();
        
    	if($CFG->block_online_users_map_has_names) {
    		$marker->shownames = true;
    		$user->fullname = fullname($puser);
    	} else {
    		$user->fullname = "";
    	}
            
        if ($puser->lastaccess <= $timefrom){
            $user->online = "false";
            $marker->usersoffline++;
        } else {
          	$user->online = "true";
          	$marker->usersonline++;
        }

        array_push($marker->users, $user);
        $markers[$puser->lat +"," + $puser->lng] = $marker;
           
    }
}     


/*
//Add this to the SQL to show only group users
        if ($currentgroup !== NULL) {
            $groupmembers = ",  {groups_members} gm ";
            $groupselect = " AND u.id = gm.userid AND gm.groupid = '$currentgroup'";
        }

        if ($course->id == SITEID) {  // Site-level
            $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
            $from = "FROM {user} u,
                          {block_online_users_map} boumc
                          $groupmembers ";
            $where = "WHERE u.lastaccess > $timefrom
                        AND boumc.userid = u.id
                      $groupselect ";
            $order = "ORDER BY lastaccess DESC ";
            
        } else { // Course-level
            $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
            $from = "FROM {user_lastaccess} ul,
                          {user} u,
                          {block_online_users_map} boumc
                          $groupmembers ";
            $where =  "WHERE ul.timeaccess > $timefrom
                       AND u.id = ul.userid
                       AND ul.courseid = $course->id
                       AND boumc.userid = u.id
                       $groupselect ";
            $order = "ORDER BY lastaccess DESC ";
        }
        
        $groupby = "GROUP BY u.id, u.username, u.firstname, u.lastname, u.city, u.picture, boumc.lat, boumc.lng ";
        
        $SQLwithLL = $select . $from . $where . $groupby . $order;



if ($pusers = $DB->get_records_sql($SQLwithLL, array(),0, 50)) {   // We'll just take the most recent 50 maximum
    foreach ($pusers as $puser) {

		if($CFG -> block_online_users_map_has_names) {
            $puser->fullname = fullname($puser);
        } else {
            $puser->fullname = $puser->city;
        }
        unset($puser->id);
        unset($puser->username);
        unset($puser->lastname);
        unset($puser->firstname);
        unset($puser->lastaccess);
        $puser->online = "true";
        $users[$counter] = $puser;  
        $counter++;
    }
}  


echo $callback."(".json_encode($users).")";
*/

$display = array();
$counter = 0;
foreach($markers as $m){
	$display[$counter] = $m;
	$counter++;
}
echo $callback."(".json_encode($display).")";

?>
