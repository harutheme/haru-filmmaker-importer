<?php
/**
 * Class Meks_Video_Importer_Easy_Transition
 *
 * On activating the plugin this class will check if there are already youtube or vimeo API credentials
 * If they exits it will check for validity and insert them to our plugin settings
 * @since    1.0.0
 */
if (!class_exists('Meks_Video_Importer_Easy_Transition')):
    class Meks_Video_Importer_Easy_Transition {

        /**
         * Call this method to get singleton
         *
         * @return Meks_Video_Importer_Easy_Transition
         * @since    1.0.0
         */
        public static function getInstance() {
            static $instance = null;
            if (null === $instance) {
                $instance = new static;
            }

            return $instance;
        }

        /**
         * Meks_Video_Importer_Easy_Transition constructor.
         *
         * @since    1.0.0
         */
        public function __construct() {
            $this->load_dependecies();
            $this->transition_from_video_importer_plugin_by_refactored_co();
        }

        private function load_dependecies() {
            require_once wp_normalize_path( MEKS_VIDEO_IMPORTER_INCLUDES . 'meks-video-importer-helpers.php' );
        }

        /**
         * This plugin will fetch and check settings from video importer plugin by refactored co
         *
         * @link https://wordpress.org/plugins/video-importer/
         * @since 1.0.0
         */
        public function transition_from_video_importer_plugin_by_refactored_co() {

            $valid_providers = $this->are_there_some_verified_credentials();
            $video_importer_plugin_by_refactored_co_options = get_option('refactored_video_importer');

            if (empty($valid_providers['youtube']) && !empty($video_importer_plugin_by_refactored_co_options['youtube']['api_key'])) {
                $this->verify_youtube($video_importer_plugin_by_refactored_co_options['youtube']['api_key']);
            }

            if (empty($valid_providers['vimeo']) && !empty($video_importer_plugin_by_refactored_co_options['vimeo']['client_id']) && !empty($video_importer_plugin_by_refactored_co_options['vimeo']['client_secret'])) {
                $this->verify_vimeo($video_importer_plugin_by_refactored_co_options['vimeo']['client_id'], $video_importer_plugin_by_refactored_co_options['vimeo']['client_secret']);
            }

        }

        /**
         * Checks if user have already had plugin installed and if api keys and access tokens already exits
         *
         * @return array - providers that doesn't have API credentials added
         */
        private function are_there_some_verified_credentials() {
            $youtube_apy_key_verified = get_option('mvi-youtube-api-key-verified');
            $vimeo_access_token = get_transient('meks-video-importer-vimeo-access-token');
            $valid_providers = array();

            if (!empty($youtube_apy_key_verified)) {
                $valid_providers['youtube'] = true;
            }

            if (!empty($vimeo_access_token)) {
                $valid_providers['vimeo'] = true;
            }

            return $valid_providers;
        }

        /**
         * Checks if youtube credentials are valid and if they are, plugin's youtube part will be ready to use at once
         *
         * @param $api_key
         * @return bool
         */
        private function verify_youtube($api_key) {
            $response = wp_remote_get('https://www.googleapis.com/youtube/v3/search?part=snippet&q=YouTube+Data+API&type=video&key=' . $api_key);
            if(!meks_video_importer_is_valid_response($response)){
                return false;
            }
            update_option('mvi-youtube-api-key', $api_key);
            update_option('mvi-youtube-api-key-verified', 1);

            return true;
        }

        /**
         * Checks if vimeo credentials are valid if they are, plugin's vimeo will be ready to use
         *
         * @param $client_id
         * @param $client_secret
         * @return bool
         */
        private function verify_vimeo($client_id, $client_secret) {
            $response = wp_remote_post('https://api.vimeo.com/oauth/authorize/client',
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
                    ),
                    'body'    => array('grant_type' => 'client_credentials'),
                )
            );

            if (!meks_video_importer_is_valid_response($response)) {
                if (!empty($response['body'])) {
                    $body = json_decode($response['body']);
                    $this->access_error = $body->error;
                }

                return false;
            }

            $body = json_decode($response['body']);

            if (empty($body->access_token)){
                return false;
            }

            update_option('mvi-vimeo-client-id', $client_id);
            update_option('mvi-vimeo-client-secret', $client_secret);
            set_transient('meks-video-importer-vimeo-access-token', $body->access_token, DAY_IN_SECONDS);
            return true;
        }
    }
endif;