<?php

/*
 * Plugin Name: Mythical Image Wars
 * Plugin URI: TBD
 * Description: A plugin that helps build image contests in a post.
 * Author: Matthew Jackowski
 * Version: 0.4.0
 * Author URI: http://www.linkedin.com/pub/matthew-jackowski/6/6b2/242
 * Text Domain: mythicalimagewars
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 */

// Load main plugin class and call a static method
require_once( WP_PLUGIN_DIR . '/mythical-image-wars/classes/mythicalImageWars.php');
add_action( 'init', array( 'mythicalImageWars', 'init' ) );

require_once( WP_PLUGIN_DIR . '/mythical-image-wars/classes/mythicalImageWarsDebug.php');
add_action( 'init', array ('mythicalImageWarsDebug','init') );



register_activation_hook( __FILE__, array( 'mythicalImageWars', 'activatePlugin' ) );
register_deactivation_hook( __FILE__, array( 'mythicalImageWars', 'deactivatePlugin' ) );

//restore_error_handler();

 ?>
