<?php
/*
   Plugin Name: CodeGuard Website Backups
   Plugin URI: https://codeguard.com/wordpress
   Author: The CodeGuard Team
   Description: Get a time machine for your website!  CodeGuard will monitor your site for changes.  When a change is detected, we will alert you and take a new backup of your database and site content.
   Version: 0.50
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by    
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of    
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . "main.php";

main::setPluginDir(dirname(__FILE__));  
main::setPluginName('codeguard');
main::init();
add_action('init', array('main', 'run') );

add_action('admin_print_scripts', array('main', 'include_admins_script' ));

// Hooks to set up the crons
register_activation_hook( __FILE__, array( 'main', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'main', 'deactivate' ) );
