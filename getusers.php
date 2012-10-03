<?php

include_once("../../config.php");
include_once($CFG->dirroot.'/blocks/online_users_map/lib.php');

$callback = optional_param('callback','',PARAM_ALPHA);

$timefrom = 100 * floor((time()-getTimeToShowUsers()) / 100); // Round to nearest 100 seconds for better query cache

// Get context so we can check capabilities.
$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

//Calculate if we are in separate groups
$isseparategroups = ($COURSE->groupmode == SEPARATEGROUPS
                     && $COURSE->groupmodeforce
                     && !has_capability('moodle/site:accessallgroups', $context));

//Get the user current group
$currentgroup = $isseparategroups ? get_and_set_current_group($COURSE, groupmode($COURSE)) : NULL;

$groupmembers = "";
$groupselect = "";
$users = array();

$counter = 0;
//now if the block setting to show offline users to is get then add the offline users to the returned content
if (isset($CFG->block_online_users_map_show_offline) && $CFG->block_online_users_map_show_offline == 1){
    if ($currentgroup !== NULL) {
        $groupmembers = ",  {groups_members} gm ";
        $groupselect = " AND u.id = gm.userid AND gm.groupid = '$currentgroup'";
    }

    if ($COURSE->id == SITEID) {  // Site-level
        $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
        $from = "FROM {user} u,
                      {block_online_users_map} boumc
                      $groupmembers ";
        $where = "WHERE u.lastaccess <= $timefrom
                    AND boumc.userid = u.id
                  $groupselect ";
        $order = "ORDER BY lastaccess DESC ";
        
    } else { // Course-level
        $courseselect = "AND ul.courseid = '".$COURSE->id."'";
        $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
        $from = "FROM {user_lastaccess} ul,
                      {user} u,
                      {block_online_users_map} boumc
                      $groupmembers ";
        $where =  "WHERE ul.timeaccess <= $timefrom
                   AND u.id = ul.userid
                   AND ul.courseid = $COURSE->id
                   AND boumc.userid = u.id
                   $groupselect ";
        $order = "ORDER BY lastaccess DESC ";
    }
    
    $groupby = "GROUP BY u.id, u.username, u.firstname, u.lastname, u.city, u.picture, boumc.lat, boumc.lng ";
    
    $SQLwithLL = $select . $from . $where . $groupby . $order;

    $pcontext = get_related_contexts_string($context);
    
    if ($pusers = $DB->get_records_sql($SQLwithLL, array(), 0, 50)) {   // We'll just take the most recent 50 maximum
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
            $puser->online = "false";
            $users[$counter] = $puser;  
            $counter++;
        }
    }     
}

//Add this to the SQL to show only group users
        if ($currentgroup !== NULL) {
            $groupmembers = ",  {groups_members} gm ";
            $groupselect = " AND u.id = gm.userid AND gm.groupid = '$currentgroup'";
        }

        if ($COURSE->id == SITEID) {  // Site-level
            $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
            $from = "FROM {user} u,
                          {block_online_users_map} boumc
                          $groupmembers ";
            $where = "WHERE u.lastaccess > $timefrom
                        AND boumc.userid = u.id
                      $groupselect ";
            $order = "ORDER BY lastaccess DESC ";
            
        } else { // Course-level
            $courseselect = "AND ul.courseid = '".$COURSE->id."'";
            $select = "SELECT u.id, u.username, u.firstname, u.lastname, u.city, MAX(u.lastaccess) as lastaccess, boumc.lat, boumc.lng ";
            $from = "FROM {user_lastaccess} ul,
                          {user} u,
                          {block_online_users_map} boumc
                          $groupmembers ";
            $where =  "WHERE ul.timeaccess > $timefrom
                       AND u.id = ul.userid
                       AND ul.courseid = $COURSE->id
                       AND boumc.userid = u.id
                       $groupselect ";
            $order = "ORDER BY lastaccess DESC ";
        }
        
        $groupby = "GROUP BY u.id, u.username, u.firstname, u.lastname, u.city, u.picture, boumc.lat, boumc.lng ";
        
        $SQLwithLL = $select . $from . $where . $groupby . $order;

$pcontext = get_related_contexts_string($context);

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

header("Content-type: text/plain");
echo phpToJSON($users,'online',$callback);

?>
