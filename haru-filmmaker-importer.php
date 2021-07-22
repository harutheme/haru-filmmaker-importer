<?php
/*
Plugin Name: Haru Filmmaker Importer
Description: Customized from Meks Video Importer to help HaruTheme customer easily import YouTube and Vimeo videos in bulk to your posts, pages or any custom post type.
Version: 1.0.0
Author: HaruTheme
Author URI: http://harutheme.com
Plugin URI: http://harutheme.com
Text Domain: meks-video-importer
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define useful constants here
 */
define( 'MEKS_VIDEO_IMPORTER_VERSION', '1.0.9' );
define( 'MEKS_VIDEO_IMPORTER_BASENAME', plugin_basename(__FILE__) );
define( 'MEKS_VIDEO_IMPORTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'MEKS_VIDEO_IMPORTER_INCLUDES', trailingslashit( MEKS_VIDEO_IMPORTER_DIR . 'includes' ) );
define( 'MEKS_VIDEO_IMPORTER_PARTIALS', trailingslashit( MEKS_VIDEO_IMPORTER_DIR . 'partials' ) );
define( 'MEKS_VIDEO_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'MEKS_VIDEO_IMPORTER_ASSETS_URL', trailingslashit( MEKS_VIDEO_IMPORTER_URL . 'assets' ) );

define( 'MEKS_VIDEO_IMPORTER_PAGE_SLUG', 'meks_video_importer' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-meks-video-importer-activator.php
 */
register_activation_hook( __FILE__, 'activate_meks_video_importer' );
function activate_meks_video_importer() {
    include_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-easy-transition.php' );
    Meks_Video_Importer_Easy_Transition::getInstance();
}

/**
 * On activating the plugin this class will check if there are already youtube or vimeo API credentials
 * If they exits it will check for validity and insert them to our plugin settings
 *
 * @since    1.0.0
 */

/* Initialize plugin */
function meks_video_importer_start() {

    if ( !is_admin() ){
        return;
    }

    /* Load translation */
    load_plugin_textdomain(
        'meks-video-importer',
        false,
        dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
    );

    /**
     * Begins execution of the plugin.
     *
     * @since    1.0.0
     */
    require_once MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer.php';
    Meks_Video_Importer::getInstance();
}

add_action( 'plugins_loaded', 'meks_video_importer_start' );