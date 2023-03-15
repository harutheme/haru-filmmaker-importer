<?php

/**
 * Class Meks_Video_Importer_Youtube
 *
 * Works with Youtube API
 *
 * @since    1.0.0
 */
if (!class_exists('Meks_Video_Importer_Youtube')):
    class Meks_Video_Importer_Youtube {

        /**
         * @var $response_body returned from youtube
         * @since    1.0.0
         */
        private $response_body;
        
        /**
         * @var $second_response_body in order to compare embeddable with not embeddable videos we need to make two requests
         * @since    1.0.3
         */
        private $second_response_body;

        /**
         * @var mixed|void default query arg for getting data from youtube
         * @since    1.0.0
         */
        private $query_defaults;

        /**
         * Call this method to get singleton
         *
         * @return Meks_Video_Importer_Youtube
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
         * Meks_Video_Importer_Youtube constructor.
         *
         * @since    1.0.0
         */
        public function __construct() {

            // Ajax
            add_action('wp_ajax_mvi_fetch_from_youtube', array($this, 'ajax_fetch_from_youtube'));
            add_action('wp_ajax_mvi_save_youtube_settings', array($this, 'ajax_save_settings'));

            // Frontend
            add_action('meks-video-importer-print-providers', array($this, 'print_options'));
            add_action('meks-video-importer-settings', array($this, 'print_settings'));
            add_action('admin_enqueue_scripts', array($this, 'localize_messages'), 99);
            add_filter('meks-video-importer-valid-providers', array($this, 'are_credentials_valid'));

            $this->query_defaults = apply_filters('meks-video-importer-youtube-default-query', array(
	            'part'            => 'snippet',
	            'maxResults'      => 50,
	            'key'             => $this->get_api_key(),
            ));
        }

        /**
         * Localize error messages
         */
        public function localize_messages() {

            wp_localize_script('meks-video-importer-script', 'meks_video_importer_youtube', array(
                    'empty_id_or_type' => __('Please select Type and fill the ID.', 'meks-video-importer'),
                )
            );
        }

        /**
         * Get YouTube API key from options
         *
         * @return mixed|string
         * @since    1.0.0
         */
        private function get_api_key() {
            $youtube_apy_key = get_option('mvi-youtube-api-key');

            return !empty($youtube_apy_key) ? $youtube_apy_key : '';
        }

        /**
         *  Save and verify YouTube settings
         *
         * @since    1.0.0
         */
        public function ajax_save_settings() {

            if (isset($_POST['key'])) {

                // Verify API credentials
                if ($this->verify_credentials($_POST['key'])) {
                    update_option('mvi-youtube-api-key', $_POST['key']);
                    update_option('mvi-youtube-api-key-verified', 1);
                    wp_send_json_success(array('message' => '<span class="dashicons dashicons-yes"></span>' . __('Successfully verified.', 'meks-video-importer')));
                }

            }

            delete_option('mvi-youtube-api-key-verified');
            update_option('mvi-youtube-api-key', $_POST['key']);
            wp_send_json_error(array('message' => '<span class="dashicons dashicons-no"></span>' . __('Credentials not verified. Please try adding it again', 'meks-video-importer')));

        }

        /**
         * Verify Youtube Settings
         */
        public function verify_credentials($api_key) {

            if(empty($api_key)){
               return false;
            }

            $checkKeyQuery = $this->make_query(
                array(
                    'type' => 'search',
                    'id'   => 'YouTube+Data+API',
                    ),
                array(
                    'type' => 'video',
                    'key'  => $api_key,
                )
            );

            return meks_video_importer_is_valid_response($checkKeyQuery);
        }

        /**
         *  Check if credentials are valid and verified. This is "meks-video-importer-redirect" filter callback.
         *
         * @param $redirect_array
         * @return array
         * @since    1.0.0
         */
        public function are_credentials_valid($redirect_array) {
            $youtube_apy_key = get_option('mvi-youtube-api-key');
            $youtube_apy_key_verified = get_option('mvi-youtube-api-key-verified');

            if (!empty($youtube_apy_key) && !empty($youtube_apy_key_verified))
                $redirect_array[] = 'youtube';

            return $redirect_array;
        }

        /**
         * Print options for importing posts
         *
         * @since    1.0.0
         */
        public function print_options() {
            require_once plugin_dir_path(dirname(__FILE__)) . 'partials/youtube.php';
        }

        /**
         * Print options for settings page
         *
         * @since    1.0.0
         */
        public function print_settings() {
            require_once plugin_dir_path(dirname(__FILE__)) . 'partials/youtube-settings.php';
        }

        /**
         * Ajax for fetching posts from youtube
         *
         * @since    1.0.0
         */
        public function ajax_fetch_from_youtube() {
            if (!isset($_POST['type']) || empty($_POST['type']) || !isset($_POST['id']) || empty($_POST['id'])) {
                wp_send_json_error(array('message' => __('Invalid request', 'meks-video-importer')));
            }
            
            $this->make_request();
            
            $table = new Meks_Video_Importer_List_Table();
            $table->set_items($this->format_for_table_display());

            wp_send_json_success(array('res' => $this->response_body, 'table' => $table->display()));
        }
	
	    /**
	     * Main function for making request towards youtube
	     *
	     * @since    1.0.2
	     */
	    private function make_request() {
		    $response = $this->make_query();
		
		    if (!meks_video_importer_is_valid_response($response)) {
		    	$this->respond_with_error_message($response);
		    }
		
		    $this->response_body = json_decode($response['body']);
		    
		    if ($_POST['type'] != 'search') {
			    $this->recursive_fetch_paged_from_youtube($this->response_body);
		    }
		
		    $this->make_only_embeddable_request();

		    $this->intersect_videos();
        }
	
	    /**
	     * Send human readable error message as ajax response
	     *
	     * @param $response
	     * @since 1.0.4
	     */
	    private function respond_with_error_message( $response ) {
		
		    if( empty($response) ){
			    wp_send_json_error(array('message' => 'Cannot retrieve videos from YouTube. Please make sure query parameters are correct.', 'meks-video-importer'));
		    }
		    
		    if( is_wp_error($response) ){
			    wp_send_json_error(array('message' => $response->get_error_message()));
		    }
		
		    if( $response['response']['code'] < 200 || $response['response']['code'] >= 400  ){
			    wp_send_json_error(array('message' => 'Youtube returned ' . $response['response']['code'] . ' status code. Please make sure query parameters are correct.', 'meks-video-importer'));
		    }
	    	
	    	
		    wp_send_json_error(array('message' => __('Cannot retrieve videos from YouTube. Please make sure query parameters are correct.', 'meks-video-importer')));
	    }
	
	    /**
	     * Fetch only embeddable videos
	     * Note: This will not work in case of playlist query
	     *
	     * @return bool
	     * @since 1.0.4
	     */
	    private function make_only_embeddable_request() {
		    if($_POST['type'] == 'playlist'){
		    	return false;
		    }
		
		    $embeddable_args = array(
			    'type'            => 'video',
			    'videoEmbeddable' => 'true',
			    'videoSyndicated' => 'true',
			    'format'=> 5,
		    );
		    
		    $second_response = $this->make_query(array(), $embeddable_args);
		
		    if (!meks_video_importer_is_valid_response($second_response)) {
		    	return false;
		    }
		    
		    $this->second_response_body = json_decode($second_response['body']);
		
		    if($_POST['type'] == 'search'){
			    return false;
		    }
		    
		    $this->recursive_fetch_paged_from_youtube($this->second_response_body, $embeddable_args);
        }
        
	    /**
	     * Intersect between embeddable and not embeddable videos
	     *
	     * @return mixed
	     * @since 1.0.4
	     */
	    private function intersect_videos() {
	
	    	if(empty($this->response_body->items) || empty($this->second_response_body->items)){
	    	    return false;
	    	}
	    	
	        $intersected = array();
		
	        foreach ( $this->response_body->items as $first_request_video ) {
		        $first_request_video->embeddable = false;
		        
		        foreach ( $this->second_response_body->items as $second_request_video ) {
			        if($first_request_video->id == $second_request_video->id ){
				        $first_request_video->embeddable  = true;
			        }
		        }

		        if(!$first_request_video->embeddable){
			        $first_request_video->mvi_message = '<p>' . __('This video is not embeddable', 'meks-video-importer') . '</p>';
		        }

		        $intersected[] = $first_request_video;
	        }
		
	        return $intersected;
        }

        /**
         * Execute query to youtube
         *
         * @param array $args
         * @param array $append_query
         * @return array|WP_Error
         * @since    1.0.0
         */
        private function make_query($args = array(), array $append_query = array()) {
            if (empty($args)) {
                $args = $_POST;
            }

            switch ($args['type']) {
                case "search":
                    $url = $this->build_url(array(
                        'q' => $args['id'],
                    ), $append_query);
                    break;
                case "channelId":
                    $url = $this->build_url(array(
                        'channelId' => $args['id'],
                    ), $append_query);
                    break;
                case "userId":
                    $channelId = $this->get_channel_by_user_id($args['id']);

                    if (!$channelId) {
                        wp_send_json_error(array('message' => __('Channel not found.', 'meks-video-importer')));
                    }

                    $url = $this->build_url(array(
                        'channelId' => $channelId,
                        'order'     => 'date',
                    ), $append_query);
                    break;
                case "playlist":
                default:
                    $url = $this->build_url(array(
                        'playlistId' => $args['id'],
                        'part' => 'snippet,status',
                    ), $append_query, 'playlistItems');
                    break;
            }

            return wp_remote_get($url);
        }

        public function get_single_video( $video_id ){
            // $url = $this->build_url( array('part' => 'snippet', 'id' => $video_id ) ,  array(), 'videos' );
            $url = $this->build_url( array('part' => 'snippet,contentDetails,statistics', 'id' => $video_id ) ,  array(), 'videos' ); // HaruTheme Customize

            $response = wp_remote_get( $url );

            if (!meks_video_importer_is_valid_response($response)) {
                return false;
            }

            $video = json_decode( $response['body'] );

            if(!isset($video->items[0]->snippet)){
                return false;
            }
            
            // return $video->items[0]->snippet;
            // HaruTheme Customize
            $return = array();
            $return['snippet'] = $video->items[0]->snippet;
            $return['contentDetails'] = $video->items[0]->contentDetails;
            $return['statistics'] = $video->items[0]->statistics;

            return $return;
            
        }

        /**
         * Helepr for building URL for fetching query
         *
         * @param $query_args
         * @param array $append_query
         * @param string $endpoint
         * @return string
         * @since    1.0.0
         */
        private function build_url($query_args, array $append_query = array(), $endpoint = 'search') {

            $defaults = $this->query_defaults;
	        $query = meks_video_importer_parse_args($query_args, $defaults);
	        
            if (!empty($append_query)) {
	            $query = meks_video_importer_parse_args($append_query, $query);
            }
	
            return add_query_arg($query, 'https://www.googleapis.com/youtube/v3/' . $endpoint);
        }

        /**
         * Helper for old type of YouTube Channels that have URL like "https://www.youtube.com/user/username"
         * It executes query for getting channel id
         *
         * @param $id - Channel ID
         * @return bool
         * @since    1.0.0
         */
        private function get_channel_by_user_id($id) {

            $userIdQueryArgs = array_merge(array(
                'forUsername' => $id,
            ), $this->query_defaults);

            $userIdQueryArgs['part'] = 'id';

            $userIdQuery = wp_remote_get('https://www.googleapis.com/youtube/v3/channels', array('body' => $userIdQueryArgs));

            if (!meks_video_importer_is_valid_response($userIdQuery))
                return false;

            $userIdQueryJson = json_decode($userIdQuery['body']);

            return !empty($userIdQueryJson->items[0]->id) ? $userIdQueryJson->items[0]->id : false;
        }
	
	    /**
	     * Get all pages of playlist or channel
	     *
	     * @since    1.0.0
	     * @param $response_body
	     * @param array $additional_query_args
	     * @return boolean
	     */
        private function recursive_fetch_paged_from_youtube(&$response_body, array $additional_query_args = array()) {
            if (empty($response_body->nextPageToken)){
	            return false;
            }

            $query_args = array(
	            'pageToken' => $response_body->nextPageToken
            );
            
            if(!empty($additional_query_args)){
	            $query_args = array_merge($query_args, $additional_query_args);
            }
            
            $paged = $this->make_query(false, $query_args);
            $paged_body = json_decode($paged['body']);
            $response_body->items = array_merge($response_body->items, $paged_body->items);

            if (!empty($paged_body->nextPageToken)) {
                $response_body->nextPageToken = $paged_body->nextPageToken;
            } else {
	            unset($response_body->nextPageToken);
	            return false;
            }

            $this->recursive_fetch_paged_from_youtube($response_body, $additional_query_args);
        }

        /**
         * Format response data for displaying Table with videos
         *
         * @return array
         * @since    1.0.0
         */
        private function format_for_table_display() {
            $formatted = array();

            foreach ($this->response_body->items as $video) {

            	if($video->snippet->resourceId->kind != 'youtube#video' && $video->id->kind != 'youtube#video'){
            		continue;
	            }
            	
                if(!empty($video->status->privacyStatus) && $video->status->privacyStatus === 'private'){
                    continue;
                }

                $formatted[] = array(
	                'id'          => $this->get_video_id( $video ),
	                'image'       => $video->snippet->thumbnails->default->url,
	                'image_max'   => $this->get_largest_image( $video->snippet->thumbnails ),
	                'url'         => 'https://www.youtube.com/watch?v=' . $this->get_video_id( $video ),
	                'date'        => $video->snippet->publishedAt,
	                'title'       => $video->snippet->title,
	                'description' => $video->snippet->description,
	                'message'     => $video->mvi_message,
	                'embeddable'  => $video->embeddable,
                );
            }

            return $formatted;
        }

        /**
         * Helper for getting video id
         *
         * @param $video
         * @return null
         * @since    1.0.0
         */
        private function get_video_id($video) {
            if (!empty($video->snippet->resourceId->videoId)) {
                return $video->snippet->resourceId->videoId;
            }

            if (!empty($video->id->videoId)) {
                return $video->id->videoId;
            }

            return null;
        }

        /**
         * Helper for getting biggest image available
         *
         * @param $thumbnails
         * @return null
         * @since    1.0.0
         */
        private function get_largest_image($thumbnails) {
            $youtube_thumbnail_sizes = array(
                'maxres', 'standard', 'high', 'medium', 'default',
            );

            foreach ($youtube_thumbnail_sizes as $youtube_thumbnail_size) {
                if (!empty($thumbnails->{$youtube_thumbnail_size}->url)) {
                    return $thumbnails->{$youtube_thumbnail_size}->url;
                }
            }

            return null;
        }

        /**
         * Youtube options
         *
         * @return array
         * @since    1.0.0
         */
        public function get_select_options() {
            return apply_filters('meks-video-importer-youtube-select-options', array(
                'playlist'  => __("Playlist", 'meks-video-importer'),
                'channelId' => __("Channel", 'meks-video-importer'),
                'search'    => __("Search", 'meks-video-importer'),
                'userId'    => __("User", 'meks-video-importer'),
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
                'mvi-youtube-type' => 'playlist',
                'mvi-youtube-id'   => '',
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
         * Get Youtube's API key with verified confirmation
         *
         * @return array
         * @since    1.0.0
         */
        public function get_access_credentials() {
            $defaults = array(
                'mvi-youtube-api-key-verified' => false,
                'mvi-youtube-api-key'          => '',
            );

            $access_credentials = array(
                'mvi-youtube-api-key-verified' => get_option('mvi-youtube-api-key-verified'),
                'mvi-youtube-api-key'          => get_option('mvi-youtube-api-key'),
            );

            if (!empty($access_credentials['mvi-youtube-api-key-verified'])) {
                return meks_video_importer_parse_args($access_credentials, $defaults);
            }

            return $defaults;
        }
    }
endif;