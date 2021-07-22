<?php


if (!class_exists('Meks_Video_Importer_Vimeo')):
    class Meks_Video_Importer_Vimeo {

        /**
         * Access token for communication with Vimeo API
         *
         * @var bool|mixed
         * @since    1.0.0
         */
        private $access_token;

        /**
         * If there is an error in getting Vimeo access token this will contain that error
         *
         * @var
         * @since    1.0.0
         */
        private $access_error;

        /**
         * Call this method to get singleton
         *
         * @return Meks_Video_Importer_Vimeo
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
         * Meks_Video_Importer_Vimeo constructor.
         *
         * @since    1.0.0
         */
        public function __construct() {

            // Ajax
            add_action('wp_ajax_mvi_fetch_from_vimeo', array($this, 'ajax_fetch_from_vimeo'));
            add_action('wp_ajax_mvi_save_vimeo_settings', array($this, 'ajax_save_settings'));

            // Frontend
            add_action('meks-video-importer-print-providers', array($this, 'print_options'));
            add_action('meks-video-importer-settings', array($this, 'print_settings'));
            add_action('admin_enqueue_scripts', array($this, 'localize_messages'), 99);
            add_filter('meks-video-importer-valid-providers', array($this, 'are_credentials_valid'));
            add_action('admin_init', array($this, 'update_access_token'));
        }


        /**
         * Localize error messages
         */
        public function localize_messages() {

            wp_localize_script('meks-video-importer-script', 'meks_video_importer_vimeo', array(
                    'empty_id_or_type' => __('Please select Type and fill the ID.', 'meks-video-importer'),
                )
            );
        }

        /**
         *  Save and verify Vimeo settings
         *
         * @since    1.0.0
         */
        public function ajax_save_settings() {
            if (!isset($_POST['id']) || !isset($_POST['secret']))
                wp_send_json_error(array('message' => '<span class="dashicons dashicons-no"></span>' . __('Credentials not verified. Please try adding it again', 'meks-video-importer')));

            $access_token = $this->set_access_token($_POST['id'], $_POST['secret']);

            if (!empty($access_token)) {
                update_option('mvi-vimeo-client-id', $_POST['id']);
                update_option('mvi-vimeo-client-secret', $_POST['secret']);
                set_transient('meks-video-importer-vimeo-access-token', $access_token, DAY_IN_SECONDS);
                wp_send_json_success(array('message' => '<span class="dashicons dashicons-yes"></span>' . __('Successfully verified', 'meks-video-importer')));
            }

            update_option('mvi-vimeo-client-id', $_POST['id']);
            update_option('mvi-vimeo-client-secret', $_POST['secret']);
            delete_transient('meks-video-importer-vimeo-access-token');
            wp_send_json_error(array('message' => '<span class="dashicons dashicons-no"></span>' . __('Credentials not verified. Please try adding it again', 'meks-video-importer')));
        }
	
	    public function update_access_token() {
		    if( (defined('DOING_AJAX') && DOING_AJAX) || meks_video_importer_is_plugins_page() ){
			    $this->access_token = $this->get_access_token();
	        }
        }
        /**
         * Get access token and set it to transient
         *
         * @return bool|mixed
         * @since    1.0.0
         */
        private function get_access_token() {

            if (false === ($access_token = get_transient('meks-video-importer-vimeo-access-token'))) {

                $id = get_option('mvi-vimeo-api-id');
                $secret = get_option('mvi-vimeo-api-secret');

                $new_access_token = $this->set_access_token($id, $secret);

                if (empty($new_access_token)){
                    return false;
                }

                set_transient('meks-video-importer-vimeo-access-token', $new_access_token, DAY_IN_SECONDS);

                return $new_access_token;
            }

            return $access_token;
        }

        /**
         * Get access token from Vimeo
         *
         * @param $id
         * @param $secret
         * @return bool
         */
        private function set_access_token($id, $secret) {

            $response = wp_remote_post('https://api.vimeo.com/oauth/authorize/client',
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($id . ':' . $secret),
                    ),
                    'body'    => array('grant_type' => 'client_credentials'),
                )
            );

            if (!meks_video_importer_is_valid_response($response)) {
            	if(is_wp_error($response)){
            		$this->access_error = $response->get_error_message();
            		return false;
	            }
	            
                if (!empty($response['body'])) {
                    $body = json_decode($response['body']);
                    $this->access_error = $body->error;
	                return false;
                }

                return false;
            }

            $body = json_decode($response['body']);

            if (empty($body->access_token)){
                return false;
            }

            return $body->access_token;
        }

        /**
         * Print options for importing posts
         *
         * @since    1.0.0
         */
        public function print_options() {
            require_once plugin_dir_path(dirname(__FILE__)) . 'partials/vimeo.php';
        }

        /**
         * Print options for settings page
         *
         * @since    1.0.0
         */
        public function print_settings() {
            require_once plugin_dir_path(dirname(__FILE__)) . 'partials/vimeo-settings.php';
        }

        /**
         * Ajax for fetching posts from vimeo
         *
         * @since    1.0.0
         */
        public function ajax_fetch_from_vimeo() {

            if (!isset($_POST['type']) || empty($_POST['type']) || !isset($_POST['id']) || empty($_POST['id'])){
                wp_send_json_error(array('message' => __('You must provide type and id.', 'meks-video-importer')));
            }

            if (!empty($this->access_error)){
                wp_send_json_error(array('message' => $this->access_error));
            }

            $response_body = $this->make_query();

            $table = new Meks_Video_Importer_List_Table();
            $table->set_items($this->format_for_table_display($response_body));

            wp_send_json_success(array('res' => $response_body, 'table' => $table->display()));
        }

        /**
         * Format response data for displaying Table with videos
         *
         * @param $response_body
         * @return array
         * @since    1.0.0
         */
        private function format_for_table_display($response_body) {
            $formatted = array();

            foreach ($response_body as $video) {
                $formatted[] = array(
                    // 'id'          => $video->uri,
                    'id'          => $this->getVimeoVideoIdFromUrl($video->link), // HaruTheme Customize
                    'image'       => $video->pictures->sizes[0]->link,
                    'image_max'   => $this->get_largest_image($video),
                    'url'         => $video->link,
                    'date'        => $video->release_time,
                    'title'       => $video->name,
                    'description' => $video->description,
                    'duration'    => $video->duration, // HaruTheme Customize
                    'views'       => $video->stats->plays, // HaruTheme Customize
                );
            }

            return $formatted;
        }

        // HaruTheme Customize
        function getVimeoVideoIdFromUrl($url = '') {
            $regs = array();
            $id = '';
        
            if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $regs)) {
                $id = $regs[3];
            }
        
            return $id;
        }

        /**
         * Helper for getting biggest image available
         *
         * @param $video
         * @return null
         * @since    1.0.0
         */
        private function get_largest_image($video) {
            $last = end($video->pictures->sizes);

            return strtok($last->link, '?');
        }

        /**
         * Execute query to vimeo
         *
         * @param array $args
         * @return array|WP_Error
         * @since    1.0.0
         */
        private function make_query($args = array()) {
            if (empty($args)) {
                $args = $_POST;
            }

            switch ($args['type']) {
                case "user":
                    $url = 'https://api.vimeo.com/users/' . $args['id'] . '/videos';
                    break;
                case "groups":
                    $url = 'https://api.vimeo.com/groups/' . $args['id'] . '/videos';
                    break;
                case "channels":
                default:
                    $url = 'https://api.vimeo.com/channels/' . $args['id'] . '/videos';
                    break;
            }

            if (!empty($args['from']) && !empty($args['to'])) {
                $requests = array();
                for ($i = $args['from']; $i <= $args['to']; $i++) {
                    $response = wp_remote_get($url,
                        array(
                            'headers' => array(
                                'authorization' => 'bearer ' . $this->access_token,
                                'accept' => 'application/vnd.vimeo.*+json; version=3.2'
                            ),
                            'body' => array(
                                'full_response' => true,
                                'per_page'      => 50,
                                'access_token'  => $this->access_token,
                                'page'          => $i,
                            ))
                    );
                    
                    if (meks_video_importer_is_valid_response($response)) {
                        $json = json_decode($response['body']);
                        $requests = array_merge($requests, $json->data);
                    }
                }
                
                if (empty($requests)){
                    wp_send_json_error(array('message' => __('Invalid response', 'meks-video-importer')));
                }
                
                return $requests;
            }

            $response = wp_remote_get($url,
                array(
                    'headers' => array(
                        'authorization' => 'bearer ' . $this->access_token,
                        'accept' => 'application/vnd.vimeo.*+json; version=3.2'
                    ),
                    'body' => array(
                        'full_response' => true,
                        'per_page'      => 12,
                        'access_token'  => $this->access_token,
                    ))
            );

            if (!meks_video_importer_is_valid_response($response)) {
                if (!empty($response['body'])) {
                    $body = json_decode($response['body']);
                    wp_send_json_error(array('message' => $body->error));
                }

                wp_send_json_error(array('message' => __('Invalid request', 'meks-video-importer')));
            }

            $json = json_decode($response['body']);

            if (empty($json)){
                wp_send_json_error(array('message' => __('Invalid response', 'meks-video-importer')));
            }

            return $json->data;
        }

        /**
         *  Check if credentials are valid and verified. This is "meks-video-importer-redirect" filter callback.
         *
         * @param $redirect_array
         * @return array
         * @since    1.0.0
         */
        public function are_credentials_valid($redirect_array) {
            $access_token = get_transient('meks-video-importer-vimeo-access-token');

            if (!empty($access_token)){
                $redirect_array[] = 'vimeo';
            }

            return $redirect_array;
        }

        /**
         * Vimeo options
         *
         * @return array
         * @since    1.0.0
         */
        public function get_select_options() {
            return apply_filters('meks-video-importer-vimeo-select-options', array(
                'user'    => __("User", 'meks-video-importer'),
                'group'   => __("Group", 'meks-video-importer'),
                'channel' => __("Channel", 'meks-video-importer'),
            ));
        }

        /**
         * Get youtube options from saved template
         *
         * @return array
         * @since    1.0.0
         */
        public function get_options_from_template() {
            $defaults = array(
                'mvi-vimeo-type'      => 'user',
                'mvi-vimeo-id'        => '',
                'mvi-vimeo-from-page' => '1',
                'mvi-vimeo-to-page'  => '3',
            );

            if (!isset($_GET['template']) || empty($_GET['template'])) {
                return $defaults;
            }

            $template_options = Meks_Video_Importer_Saved_Templates::getInstance()->get_template($_GET['template']);

            if (!empty($template_options)) {
                return meks_video_importer_parse_args($template_options, $defaults);
            }

            return $defaults;
        }

        /**
         * Get Vimeo's access credentials including, access token generated in settings page, client ID and client Secret
         *
         * @return array
         * @since    1.0.0
         */
        public function get_access_credentials() {

            $defaults = array(
                'meks-video-importer-vimeo-access-token' => false,
                'mvi-vimeo-client-id'                    => '',
                'mvi-vimeo-client-secret'                => '',
            );

            $access_credentials = array(
                'meks-video-importer-vimeo-access-token' => get_transient('meks-video-importer-vimeo-access-token'),
                'mvi-vimeo-client-id'                    => get_option('mvi-vimeo-client-id'),
                'mvi-vimeo-client-secret'                => get_option('mvi-vimeo-client-secret'),
            );

            if (!empty($access_credentials['meks-video-importer-vimeo-access-token'])) {
                return meks_video_importer_parse_args($access_credentials, $defaults);
            }

            return $defaults;
        }
    }
endif;