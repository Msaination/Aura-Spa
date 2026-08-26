<?php
/**
 * Plugin Name: BookPro
 * Description: BookPro is a beauty scheduling plugin and makes appointments easy to find and book within seconds. Book anytime, from anywhere, 24/7.
 * Plugin URI: https://ovatheme.com
 * Author: ovatheme.com
 * Version: 1.1.1
 * Author URI: https://ovatheme.com
 * Text Domain: ovabookpro
 * Domain Path: /languages/
*/

defined( 'ABSPATH' ) || exit;

// Define
if ( ! defined( 'OBP_PLUGIN_FILE' ) ) define( 'OBP_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'OBP_PLUGIN_PATH' ) ) define( 'OBP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'OBP_PLUGIN_INC' ) ) define( 'OBP_PLUGIN_INC', OBP_PLUGIN_PATH . 'includes/' );
if ( ! defined( 'OBP_PLUGIN_URI' ) ) define( 'OBP_PLUGIN_URI', plugins_url( '/', __FILE__ ) );
if ( ! defined( 'OBP_METABOX' ) ) define( 'OBP_METABOX', 'obp_mb_' );
if ( ! defined( 'OBP_PREFIX' ) ) define( 'OBP_PREFIX', 'obp_' );

// Autoload Composer 
require_once OBP_PLUGIN_PATH . 'vendor/autoload.php';

use BookPro\OVABookPro;

/**
 * Returns the main instance of BookPro.
 *
 * @since  1.0
 * @return BookPro
 */
function OBP() {
	return OVABookPro::instance();
}

// Global for backwards compatibility.
$GLOBALS['OBP'] = OBP();