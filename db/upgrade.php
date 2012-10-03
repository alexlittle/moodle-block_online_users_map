<?php 

// This file keeps track of upgrades to 
// the online_users_map block

function xmldb_block_online_users_map_upgrade($oldversion) {

    global $CFG, $DB, $OUTPUT;

	$dbman = $DB->get_manager();
	
    if ($oldversion < 2007110101) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_centre_lat";
        $setting->value = 17.383;
        $DB->insert_record("config",$setting);
        
        $setting = new object();
        $setting->name = "block_online_users_map_centre_lng";
        $setting->value = 11.183;
        $DB->insert_record("config",$setting);
        
        $setting = new object();
        $setting->name = "block_online_users_map_init_zoom";
        $setting->value = 0;
        $DB->insert_record("config",$setting);
    }
    
    if ($oldversion < 2008011400) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_debug";
        $setting->value = 0;
        $DB->insert_record("config",$setting);
        
    }
    
    if ($oldversion < 2008030600) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_show_offline";
        $setting->value = 0;
        $DB->insert_record("config",$setting);
        
        $setting = new object();
        $setting->name = "block_online_users_map_show_offline_role";
        $setting->value = 0;
        $DB->insert_record("config",$setting);
    }
    
    if ($oldversion < 2008052700) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_centre_user";
        $setting->value = 0;
        $DB->insert_record("config",$setting);
    }
    
    if ($oldversion < 2008080700) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_update_limit";
        $setting->value = 100;
        $DB->insert_record("config",$setting);
    }
    
	if ($oldversion < 2010051900) { 
        // add new config entries
        $setting = new object();
        $setting->name = "block_online_users_map_has_names";
        $setting->value = 1;
        $DB->insert_record("config",$setting);
    }
    
    if ($oldversion < 2010122700) {
        $setting = new object();
        $setting->name = "block_online_users_map_type";
        $setting->value = 'osm';
        $DB->insert_record("config",$setting);
        
        // block savepoint reached
        upgrade_block_savepoint(true, 2010122700, 'online_users_map');
    }
     
    return true;
}

?>
