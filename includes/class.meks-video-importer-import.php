<?php

require_once(ABSPATH . 'wp-admin/includes/image.php');

/**
 * Class Meks_Video_Importer_Import
 *
 * Used for importing posts to WordPress
 * @since    1.0.0
 */
if (!class_exists('Meks_Video_Importer_Import')):
    class Meks_Video_Importer_Import {

        /**
         * @var notices, warnings, success and errors messages
         *
         * @since    1.0.0
         */
        private $notices;

        /**
         * If in flow set to true it will import will have error response
         *
         * @var
         */
        private $has_error = false;

        /**
         * Call this method to get singleton
         *
         * @return Meks_Video_Importer_Import
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
         * Meks_Video_Importer_Import constructor.
         *
         * @since    1.0.0
         */
        public function __construct() {
            add_action('wp_ajax_mvi_import_post', array($this, 'ajax_insert_post'));
        }

        /**
         * Starts the import of the post
         *
         * @since    1.0.0
         */
        public function ajax_insert_post() {
            if ($this->is_valid()){
                $this->import();
            }

            if ($this->has_error){
                wp_send_json_error($this->notices);
            }

            wp_send_json_success($this->notices);
        }

        /**
         * Checks if provided data is valid
         *
         * @return bool
         * @since    1.0.0
         */
        private function is_valid() {
            check_ajax_referer('mvi-import', 'security');

            if (!isset($_POST['mvi-video-id']) || empty($_POST['mvi-video-id'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Id not provided.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-post-type']) || empty($_POST['mvi-post-type'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Post type not selected.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-author']) || empty($_POST['mvi-author'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Post author not selected.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-post-status']) || empty($_POST['mvi-post-status'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Post status not selected.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-video-url']) || empty($_POST['mvi-video-url'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Video mush have url.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-video-title']) || empty($_POST['mvi-video-title'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Video mush have title.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            if (!isset($_POST['mvi-author']) || empty($_POST['mvi-author'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => __('Video mush have author.', 'meks-video-importer'),
                );
                $this->has_error = true;
            }

            return !$this->has_error; // Note the "!"!
        }

        /**
         *  Main function that imports the post
         *
         * @since    1.0.0
         */
        private function import() {
            // Quit if exits
            if ($this->exits($_POST['mvi-video-id'])) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => esc_html__('Video already exists.', 'meks-video-importer'),
                );
                $this->has_error = true;

                return;
            }

            $post_content = $this->get_post_content();

            // HaruTheme Customize
            if ( $_POST['provider'] == 'youtube' ) {
                $video_id = $_POST['mvi-video-id'];
                $post_contentDetails = Meks_Video_Importer_Youtube::getInstance()->get_single_video( $video_id )['contentDetails'];
                $post_statistics = Meks_Video_Importer_Youtube::getInstance()->get_single_video( $video_id )['statistics'];
                $video_duration = $this->ISO8601ToSeconds( $post_contentDetails->duration );
                $video_duration = $this->format_time_from_seconds( $video_duration );
                $video_count = $post_statistics->viewCount;
            }

            if ( $_POST['provider'] == 'vimeo' ) {
                $video_duration = $this->format_time_from_seconds( $_POST['mvi-video-duration'] );
                $video_count = $_POST['mvi-video-views'];
            }

            $post = array(
                'post_title'   => $_POST['mvi-video-title'],
                'post_author'  => $_POST['mvi-author'],
                'post_content' => $post_content,
                'post_status'  => $_POST['mvi-post-status'],
                'post_type'    => $_POST['mvi-post-type'],
                'meta_input'   => array(
                    // 'external_id' => $_POST['mvi-video-id'],
                    'haru_video_id' => $_POST['mvi-video-id'], // HaruTheme Customize
                    'haru_video_server' => $_POST['provider'],
                    'haru_video_duration' => $video_duration, // HaruTheme Customize
                    'haru_fake_view_count' => $video_count, // HaruTheme Customize
                ),
            );

            // Check witch date to use
            if (!empty($_POST['mvi-date']) && $_POST['mvi-date'] == 'on' && !empty($_POST['mvi-video-date'])) {
                $post['post_date'] = $_POST['mvi-video-date'];
            }

            // Format taxonomies
            $tax_input = $this->get_post_taxonomies();
            if (!empty($tax_input)) {
                $post['tax_input'] = $tax_input;
            }

            // Main insert
            $post_id = wp_insert_post($post);

            // Checks if insert post returned error
            if (is_wp_error($post_id)) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => $post_id->get_error_message($post_id->get_error_code()),
                );

                return;
            }

            // Checks if post is inserted
            if (empty($post_id)) {
                $this->notices[] = array(
                    'type' => 'error',
                    'msg'  => '<a href="' . $_POST['mvi-video-url'] . '">' . __('Something went wrong with this video.', 'meks-video-importer') . '</a>',
                );

                return;
            }

            // Set featured image
            if (!empty($_POST['mvi-video-image_max'])){
                // HaruTheme Customize: New API image not have ext
                if ( $_POST['provider'] == 'vimeo' ) {
                    $this->set_featured_image($_POST['mvi-video-image_max'] . '.jpeg', $post_id);
                }

                $this->set_featured_image($_POST['mvi-video-image_max'], $post_id);
            }

            // Set post format
            if (!empty($_POST['mvi-post-format']) && $_POST['mvi-post-format'] != 'standard'){
                set_post_format($post_id, $_POST['mvi-post-format']);
            }

            $this->notices[] = array(
                'type' => 'success',
                'msg'  => __('Post successfully imported.', 'meks-video-importer') . ' <a href="' . get_edit_post_link($post_id) . '" target="_blank">' . __('Edit', 'meks-video-importer') .  ' | <a href="' . get_permalink($post_id) . '" target="_blank"> ' . __('View', 'meks-video-importer') . '</a>',
            );
        }

        /**
         * Checks if posts exits
         *
         * @param $id
         * @return bool
         * @since    1.0.0
         */
        private function exits($id) {
            $p = new WP_Query(array(
                'post_type'   => 'any',
                'post_status' => 'any',
                'meta_query'  => array(
                    array(
                        'key'     => 'external_id',
                        'value'   => $id,
                        'compare' => '==',
                    ),
                ),
            ));

            return $p->have_posts();
        }

        /**
         * Prepare content for import
         *
         * @return string|HTML
         * @since    1.0.0
         */
        private function get_post_content() {
            
            $content = '';
            $description = '';
            $editor = isset( $_POST['mvi-editor'] ) ? $_POST['mvi-editor'] : 'classic';
            $provider = $_POST['provider'];
            $video_url = $_POST['mvi-video-url'];
            $video_id = $_POST['mvi-video-id'];


            if( isset($_POST['mvi-description']) && $_POST['mvi-description'] == "on" && !empty( $_POST['mvi-video-description'] ) ){

                $description = $_POST['mvi-video-description'];

                if( $provider == 'youtube' ){
                    // $video = Meks_Video_Importer_Youtube::getInstance()->get_single_video( $video_id );
                    $video = Meks_Video_Importer_Youtube::getInstance()->get_single_video( $video_id )['snippet']; // HaruTheme Customize

                    if($video && isset($video->description)){
                        $description = $video->description;


                    }
                }
            }

            if ( $editor == 'classic' ) {

                // $content = $video_url; // HaruTheme Customize
                
                if (!empty( $description ) ) {
                    $content = $content . PHP_EOL . PHP_EOL . wpautop($description);
                }

            } else {
               
                $content = '
                <!-- wp:core-embed/'. $provider .' {"url":"'.$video_url.'","type":"video","providerNameSlug":"'. $provider .'","className":""} -->
                <figure class="wp-block-embed-'. $provider .' wp-block-embed is-type-video is-provider-'. $provider .'"><div class="wp-block-embed__wrapper"> 
                '.$video_url.'
                </div></figure>
                <!-- /wp:core-embed/'. $provider .' -->';

                if ( !empty( $description) ) {
                    $content .= '<!-- wp:paragraph -->' . nl2br($description, true) . '<!-- /wp:paragraph -->';
                }

            }

            return $content;
        }


        // HaruTheme Customize
        function ISO8601ToSeconds($ISO8601){
            $interval = new \DateInterval($ISO8601);

            return ($interval->d * 24 * 60 * 60) +
                ($interval->h * 60 * 60) +
                ($interval->i * 60) +
                $interval->s;
        }

        function format_time_from_seconds($time) {
            $hours = floor($time / 3600);
            $mins = floor($time / 60 % 60);
            $secs = floor($time % 60);

            if ( $hours > 0 ) {
                $time = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $time = sprintf('%02d:%02d', $mins, $secs);
            }

            return $time;
        }

        /**
         * Get post taxonomies from provided data
         *
         * @return array
         * @since    1.0.0
         */
        private function get_post_taxonomies() {
            $taxonomies = $_POST['mvi-taxonomies'];
            $tax_input = array();

            if (!empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy => $terms) {
                    if (empty($terms))
                        continue;

                    $tax_input[$taxonomy] = explode(',', $terms);
                }
            }

            return $tax_input;
        }

        /**
         * Set featured image for post
         *
         * @param $image_url
         * @param $post_id
         * @return mixed
         * @since    1.0.0
         */
        private function set_featured_image($image_url, $post_id) {
	
	        require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
	        $temp_file = download_url( $image_url );
	
	        if ( is_wp_error( $temp_file ) ) {
		        return false;
	        }
	        
	        $file = array(
		        'name'     => basename($image_url),
		        'type'     => wp_check_filetype($temp_file),
		        'tmp_name' => $temp_file,
		        'error'    => 0,
		        'size'     => filesize($temp_file),
	        );
	
	        $overrides = array(
		        'test_form' => false,
		        'test_size' => true,
	        );
	
	        $results = wp_handle_sideload( $file, $overrides );
	
	        if ( empty( $results['error'] ) ){
		
		        $filename  = $results['file'];
		        $local_url = $results['url'];
		        $type      = $results['type'];
		
		        $attachment = array(
			        'guid'           => $local_url,
			        'post_mime_type' => $type,
		        );
		        
		        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
		        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
		
		        wp_update_attachment_metadata($attach_id, $attach_data);
		
		        if (!empty($post_id)){
			        set_post_thumbnail($post_id, $attach_id);
		        }
		
		        return array("id" => $attach_id, "data" => $attach_data);
	        }
	        
	        return false;
        }
    }
endif;