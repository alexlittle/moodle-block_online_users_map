Online Users Map Block
======================

This block is an alternative version of the standard online_users block, but 
with users plotted on an OpenStreetMap or Google map - with locations taken 
from users Moodle profile.

How to install:
1. Extract the contents of the downloaded zip into the 'blocks' directory
2. Log into your Moodle as an admin
3. Visit the Moodle->Site Administration->Notifications page - you should 
   see messages about the setting up of the block database tables
4. Go to Moodle->Site Administration->Modules->Blocks->Manage Blocks (this may 
   vary slightly depending on which version of Moodle you're using) and then 
   select the block settings for the online_users_map
5. Complete the block settings as necessary. More details about the 
   block settings below
6. Finally you need to add the block to some courses/homepage - best bet is 
   probably add it as a sticky block.

This block can either use OpenStreetMap or Google Maps for the map display - 
this can be changed in the block admin settings. 

The geocoding of users locations is performed as part of the Moodle cron, using 
http://www.geonames.org free geocoding service. If your server requires a proxy 
to access the interent (and so the Geonames service) please check that the proxy 
host/port is entered on the Moodle admin pages 
(see: http://your-moodle/admin/settings.php?section=http).

The Geonames service should work with the default demo user, but if you are 
geocoding a lot of users (so accessing their API often) then you should register 
your own account to prevent your Moodle server getting blocked from using the 
service. You can sign up for a username (for free) at: 
http://www.geonames.org/login.

NOTE: the block (with default settings) will only geocode 100 users in any run 
of Moodle cron. So if you have a lot of users, cron may need to run multiple 
times before all your users are geocoded. Once all users have been geocoded, 
subsequent crons should be quicker since only new users and those who have 
changed their locations will be geodcoded. Use the 'max locations to update' 
block admin setting to configure how many users can be geocoded in one run of 
cron. 

All feedback on this block welcome :-) 
Alex Little 
http://alexlittle.net/ 
alex@alexlittle.net