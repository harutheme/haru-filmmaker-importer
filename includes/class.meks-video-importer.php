<?php

final class Meks_Video_Importer {

    /**
     * Call this method to get singleton
     *
     * @return Meks_Video_Importer
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
        $this->load_dependencies();
    }


    /**
     * Load plugin files
     *
     * @since    1.0.0
     */
    private function load_dependencies() {

        /**
         * Helpers, for all globally needed functions
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'meks-video-importer-helpers.php' );

        /**
         * This class is used for importing posts, it expects array of posts with certain structure, everything else you can leave to this class.
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-import.php' );
        Meks_Video_Importer_Import::getInstance();

        /**
         * Used for formatting YouTube posts
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-youtube.php' );
        Meks_Video_Importer_Youtube::getInstance();

        /**
         * Used for formatting Vimeo posts
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-vimeo.php' );
        Meks_Video_Importer_Vimeo::getInstance();

        /**
         * Works with options page, it renders html
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-options-page.php' );
        Meks_Video_Importer_Options_Page::getInstance();

        /**
         * Works with saved templates
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-saved-templates.php' );
        Meks_Video_Importer_Saved_Templates::getInstance();

        /**
         * It lists table with videos
         *
         * @since    1.0.0
         */
        require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'class.meks-video-importer-list-table.php' );
    }
}
