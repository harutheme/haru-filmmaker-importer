<?php

/**
 * Class Meks_Video_Importer_Options_Page
 *
 * @since    1.0.0
 */
if ( !class_exists( 'Meks_Video_Importer_Options_Page' ) ):
    final class Meks_Video_Importer_Options_Page {

    /**
     * Call this method to get singleton
     *
     * @return Meks_Video_Importer_Options_Page
     * @since    1.0.0
     */
    public static function getInstance() {
        static $instance = null;
        if ( null === $instance ) {
            $instance = new static;
        }

        return $instance;
    }

    /**
     * Private construct so nobody else can instantiate it
     *
     * @since    1.0.0
     */
    private function __construct() {
        // Filters
        add_filter( 'plugin_action_links_' . MEKS_VIDEO_IMPORTER_BASENAME, array($this, 'add_settings_page') );

        // Actions
        add_action( 'admin_menu', array( $this, 'add_video_importer_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'admin_init', array( $this, 'maybe_redirect_to_settings' ) );
    }

    /**
     * Add Settings link to plugin
     *
     * @param $links
     * @return array
     * @since    1.0.0
     */
    function add_settings_page ( $links ) {
        $plugin_links = array(
            '<a href="' . esc_url(admin_url('tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG . '&tab=settings')) . '">' . esc_html__('Settings', 'meks-video-importer') . '</a>',
        );
        return array_merge( $links, $plugin_links );
    }

    /**
     * Adding tools page
     *
     * @since    1.0.0
     */
    public function add_video_importer_page() {
        add_management_page( esc_html__( 'Haru Filmmaker Importer', 'meks-video-importer' ), esc_html__( 'Haru Filmmaker Importer', 'meks-video-importer' ), 'manage_options', MEKS_VIDEO_IMPORTER_PAGE_SLUG, array( $this, 'print_page' ) ); // HaruTheme Customize
    }

    /**
     * Callback for printing the Tools page content
     *
     * @since    1.0.0
     */
    public function print_page() {
?>
            <div class="wrap">
                <?php $this->render_content(); ?>
            </div>
            <?php
    }

    /**
     * Prints and renders content of the page
     *
     * @since    1.0.0
     */
    private function render_content() {

        $this->get_partial( 'title' );
        $this->get_partial( 'tabs' );

        if ( !isset( $_GET['tab'] ) || empty( $_GET['tab'] ) ) {
            $this->get_partial( 'fetch' );
            $this->get_partial( 'import' );
        } else {
            switch ( $_GET['tab'] ){
                case 'settings':
                    $this->get_partial( 'settings' );;
                    break;
                case 'templates':
                    $this->get_partial( 'templates' );;
                    break;
            }
        }
    }

    /**
     * Helper for including partials
     *
     * @param    $name  partial name
     * @since    1.0.0
     */
    public function get_partial( $name ) {
        require_once MEKS_VIDEO_IMPORTER_PARTIALS . $name . '.php';
    }

    /**
     * This function decided if importing can be done
     *
     * @since    1.0.0
     */
    public function maybe_redirect_to_settings() {
        $valid_providers = meks_video_importer_get_valid_providers();

        // Quit if is video importer page
        if ( !isset( $_GET['page'] ) || empty( $_GET['page'] ) || $_GET['page'] != MEKS_VIDEO_IMPORTER_PAGE_SLUG ){
            return;
        }

        // Quit if is settings page
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'settings' ){
            return;
        }

        if ( empty( $valid_providers ) ) {
            wp_redirect( admin_url( 'tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG . '&tab=settings&ref=import' ), 301 );
            exit;
        }

    }

    /**
     * Enqueue styles and scripts
     *
     * @since    1.0.0
     */
    public function enqueue() {
	
	    global $pagenow;
	    
	    if($pagenow != 'tools.php' || empty($_GET['page']) || $_GET['page'] != MEKS_VIDEO_IMPORTER_PAGE_SLUG){
	       return;
        }
        
        wp_register_style( 'meks-video-importer-style', MEKS_VIDEO_IMPORTER_ASSETS_URL . 'css/meks-video-importer.css', false, MEKS_VIDEO_IMPORTER_VERSION );
        wp_enqueue_style( 'meks-video-importer-style' );

        wp_register_script( 'meks-video-importer-script', MEKS_VIDEO_IMPORTER_ASSETS_URL . 'js/meks-video-importer.js', array( 'jquery', 'suggest' ), MEKS_VIDEO_IMPORTER_VERSION, true );
        wp_enqueue_script( 'meks-video-importer-script' );

        $taxonomies = array();

        $post_types = meks_video_importer_get_posts_types_with_taxonomies();
        foreach ( $post_types as $post_type ) {
            foreach ( $post_type->taxonomies as $taxonomy ) {
                if ( !empty($taxonomy['hierarchical']) && !$taxonomy['hierarchical'] ) {
                    $taxonomies[] = $taxonomy['id'];
                }
            }
        }

        wp_localize_script( 'meks-video-importer-script', 'meks_video_importer_script', array(
                'ajax_url'                 => admin_url( 'admin-ajax.php' ),
                'youtube_empty_id_or_type' => esc_html__( 'Please select Type and fill the ID.', 'meks-video-importer' ),
                'vimeo_empty_id_or_type'   => esc_html__( 'Please select Type and fill the ID.', 'meks-video-importer' ),
                'importing'                => esc_html__( 'Importing...', 'meks-video-importer' ),
                'hidden_fields'            => meks_video_importer_get_hidden_fields(),
                'taxonomies'               => $taxonomies,
                'are_you_sure'             => esc_html__( 'Are you sure?', 'meks-video-importer' ),
                'total'                    => esc_html__( 'Total', 'meks-video-importer' ),
            )
        );
    }

}
endif;
