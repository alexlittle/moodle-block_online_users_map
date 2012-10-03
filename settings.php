<?php 

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	$settings->add(new admin_setting_configtext('block_online_users_map_geonamesurl', get_string('geonamesurl', 'block_online_users_map'),
			get_string('configgeonamesurl', 'block_online_users_map'), 'http://api.geonames.org', PARAM_TEXT));
	
	$settings->add(new admin_setting_configtext('block_online_users_map_geonamesusername', get_string('geonamesusername', 'block_online_users_map'),
			get_string('configgeonamesusername', 'block_online_users_map','<a href="http://www.geonames.org/login">http://www.geonames.org/login</a>'), 'demo', PARAM_TEXT));
	
    $settings->add(new admin_setting_configtext('block_online_users_map_timetosee', get_string('timetosee', 'block_online_users_map'),
                   get_string('configtimetosee', 'block_online_users_map'), 5, PARAM_INT));
    
    $settings->add(new admin_setting_configselect('block_online_users_map_type', get_string('type', 'block_online_users_map'),
	                   get_string('configtype', 'block_online_users_map'), 0, array('google' => 'Google Maps', 'osm' => 'OpenStreetMap')));
	                                  
	$settings->add(new admin_setting_configtext('block_online_users_map_centre_lat', get_string('centrelat', 'block_online_users_map'),
	                   get_string('configcentrelat', 'block_online_users_map'), 0, PARAM_NUMBER));
	
	$settings->add(new admin_setting_configtext('block_online_users_map_centre_lng', get_string('centrelng', 'block_online_users_map'),
	                   get_string('configcentrelng', 'block_online_users_map'), 0, PARAM_NUMBER));
	                   
	$settings->add(new admin_setting_configtext('block_online_users_map_init_zoom', get_string('zoomlevel', 'block_online_users_map'),
	                   get_string('configzoomlevel', 'block_online_users_map'), 0, PARAM_INT));
	                   
	$settings->add(new admin_setting_configcheckbox('block_online_users_map_debug', get_string('debug', 'block_online_users_map'),
	                   get_string('configdebug', 'block_online_users_map'), 1));
	                   
	$settings->add(new admin_setting_configcheckbox('block_online_users_map_show_offline', get_string('offline', 'block_online_users_map'),
	                   get_string('configoffline', 'block_online_users_map'), 1));
	                   
	$settings->add(new admin_setting_configcheckbox('block_online_users_map_centre_user', get_string('centreuser', 'block_online_users_map'),
	                   get_string('configcentreuser', 'block_online_users_map'), 1));
	                   
	$settings->add(new admin_setting_configcheckbox('block_online_users_map_has_names', get_string('namesonmap', 'block_online_users_map'),
	                   get_string('confignamesonmap', 'block_online_users_map'), 1));
	                   
	$settings->add(new admin_setting_configtext('block_online_users_map_update_limit', get_string('updatelimit', 'block_online_users_map'),
	                   get_string('configupdatelimit', 'block_online_users_map'), 100, PARAM_INT));
}
