<?php 
/**
 * Online Users Map block - reworking of the standard Moodle online users
 * block, but this displays the users on a Google map - using the location
 * given in the Moodle profile.
 * @author Alex Little
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block_online_users_map
 */
 
include_once($CFG->dirroot.'/blocks/online_users_map/lib.php');

class block_online_users_map extends block_base {
	
    function init() {
        $this->title = get_string('pluginname','block_online_users_map');
    }

    function instance_allow_config() {
	    return false;
	}
	
	function has_config() {
    	return true;
	}

    function get_content() {
        global $USER, $CFG, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        
        if (empty($this->instance)) {
            return $this->content;
        }
           
        //Calculate minutes
        $minutes  = floor(getTimeToShowUsers()/60);

        $this->content->text = "<div class=\"info\">(".get_string("periodnminutes","block_online_users_map",$minutes).")</div>";

		if ($CFG->block_online_users_map_type == 'osm'){
			$this->content->text .= get_html_osmmap();
		} else {
        	$this->content->text .= get_html_googlemap();
        }

        return $this->content;
    }
    
    function cron(){ 
		update_users_locations();
    	return true;
    }
    
    function preferred_width(){
        return 210;
    }
}

?>
