<?php

/**
 * Parse args ( merge arrays )
 *
 * Similar to wp_parse_args() but extended to also merge multidimensional arrays
 *
 * @param array   $a - set of values to merge
 * @param array   $b - set of default values
 * @return array Merged set of elements
 * @since  1.0
 */

if ( !function_exists( 'meks_video_importer_parse_args' ) ):
    function meks_video_importer_parse_args( &$a, $b ) {
        $a = (array) $a;
        $b = (array) $b;
        $r = $b;
        foreach ( $a as $k => &$v ) {
            if ( is_array( $v ) && isset( $r[ $k ] ) ) {
                $r[ $k ] = meks_video_importer_parse_args( $v, $r[ $k ] );
            } else {
                $r[ $k ] = $v;
            }
        }
        return $r;
    }
endif;

/**
 * Used for getting post types with all taxonomies
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_posts_types_with_taxonomies')):
    function meks_video_importer_get_posts_types_with_taxonomies() {

        $post_types_with_taxonomies = array();

        $post_types = get_post_types(
            array(
                'public'            => true,
                'show_in_nav_menus' => true,
            ),
            'object'
        );

        if (empty($post_types))
            return null;

        $post_type_counter = 0;

        foreach ($post_types as $post_type) {

            $post_taxonomies = array();
            $taxonomies = get_taxonomies(array(
                'object_type' => array($post_type->name),
                'public'      => true,
                'show_ui'     => true,
            ), 'object');

            if (!empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy) {

                    $tax = array();
                    $tax['id'] = $taxonomy->name;
                    $tax['name'] = $taxonomy->label;
                    $tax['hierarchical'] = $taxonomy->hierarchical;
                    if ($tax['hierarchical']) {
                        $tax['terms'] = get_terms($taxonomy->name, array('hide_empty' => false));
                    }

                    $post_taxonomies[] = $tax;
                }
            }

            $post_types_with_taxonomies[$post_type_counter] = $post_type;

            if (!empty($post_taxonomies)) {
                $post_types_with_taxonomies[$post_type_counter]->taxonomies = $post_taxonomies;
            }

            $post_type_counter++;
        }

        return apply_filters('meks-video-importer-get-posts-types-with-taxonomies', $post_types_with_taxonomies);
    }
endif;

/**
 * Get post formats supported by theme
 *
 * @return array
 * @since    1.0.0
 */
if(!function_exists('meks_video_importer_get_posts_formats')):
    function meks_video_importer_get_posts_formats(){
        if(!current_theme_supports('post-formats')){
            return null;
        }

        $post_formats = get_theme_support( 'post-formats' );
        if(!empty($post_formats[0])){
            $post_formats[0]['standard'] = esc_html__('Standard');
            return $post_formats[0];
        }

        return null;
    }
endif;

/**
 * Used for getting providers
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_providers')):
    function meks_video_importer_get_providers() {
        return apply_filters('meks-video-importer-providers', array(
            'youtube' => __('Youtube', 'meks-video-importer'),
            'vimeo'   => __('Vimeo', 'meks-video-importer'),
        ));
    }
endif;


/**
 * Logging helper
 *
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_log')):
    function meks_video_importer_log($mixed) {

        if (is_array($mixed)) {
            $mixed = print_r($mixed, 1);
        } else if (is_object($mixed)) {
            ob_start();
            var_dump($mixed);
            $mixed = ob_get_clean();
        }

        $handle = fopen(MEKS_VIDEO_IMPORTER_DIR . 'log', 'a');
        fwrite($handle, $mixed . PHP_EOL);
        fclose($handle);
    }
endif;


/**
 * Getting hidden field that will be imported
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_hidden_fields')):
    function meks_video_importer_get_hidden_fields() {
        // return apply_filters('meks-video-importer-get-hidden-fields', array('title', 'url', 'image_max', 'date', 'description'));
        // HaruTheme Customize
        return apply_filters('meks-video-importer-get-hidden-fields', array('title', 'url', 'image_max', 'date', 'description', 'duration', 'views'));
    }
endif;


/**
 * This is filter that returns valid providers
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_valid_providers')):
    function meks_video_importer_get_valid_providers() {
        return apply_filters('meks-video-importer-valid-providers', array());
    }
endif;

/**
 * Get current editor 
 *
 * @return string 
 * @since    1.0.0
 */
if (!function_exists('meks_get_editor')):
    function meks_get_editor() {
        
        global $wp_version;

		if ( version_compare( $wp_version, '5', '<' ) ) {
			return 'classic';
        }
        
        return 'editor';
    }
endif;

/**
 * Get all import options and merge with template if template ID is provided in URL
 *
 * @return array
 * @since    1.0.0
 */
if(!function_exists('meks_video_importer_get_import_options')):
    function meks_video_importer_get_import_options(){
	    $valid_providers = meks_video_importer_get_valid_providers();
        $valid_providers = (!empty($valid_providers)) ? $valid_providers[0] : 'youtube';
        $editor = meks_get_editor();
        $defaults = array(
            'provider' => $valid_providers,
            'mvi-editor' => $editor,
            'mvi-post-type' => 'post',
            'mvi-post-status' => 'draft',
            'mvi-description' => 'on',
            'mvi-date' => 'on',
            'mvi-taxonomies' => array(
                'post_tag' => '',
                'series' => '',
                'product_tag' => '',
            ),
            'mvi-author' => '1',
            'name' => ''
        );

        $post_formats = meks_video_importer_get_posts_formats();
        if(!empty($post_formats)){
            if(in_array('video', $post_formats)){
                $defaults['mvi-post-format'] = 'video';
            }
        }

        if (!isset($_GET['template']) || empty($_GET['template'])){
            return $defaults;
        }

        $template_options = Meks_Video_Importer_Saved_Templates::getInstance()->get_template($_GET['template']);

        if(!empty($template_options)){
            return meks_video_importer_parse_args($template_options, $defaults);
        }

        return $defaults;
    }
endif;

/**
 * It returns all options form the saved template, of course if template exits and it's added to url
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_template_options')):
    function meks_video_importer_get_template_options() {
        if (!isset($_GET['template']) || empty($_GET['template'])){
            return null;
        }

        return Meks_Video_Importer_Saved_Templates::getInstance()->get_template($_GET['template']);
    }
endif;


/**
 * It returns option form the saved template, of course if template exits and it's added to url
 *
 * @return array
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_template_option')):
    function meks_video_importer_get_template_option($option) {
        if (!isset($_GET['template']) || empty($_GET['template'])){
            return null;
        }

        return Meks_Video_Importer_Saved_Templates::getInstance()->get_template_option($_GET['template'], $option);
    }
endif;

/**
 * Get taxonomy classes depending on post type
 *
 * @param $post_type
 * @return string
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_taxonomy_classes')):
    function meks_video_importer_taxonomy_classes($post_type) {

        $mvi_post_type = meks_video_importer_get_template_option('mvi-post-type');

        if (!empty($mvi_post_type) && $post_type->name == $mvi_post_type) {
            return 'active';
        }

        if (empty($mvi_post_type) && $post_type->name == 'post') {
            return 'active';
        }

        return '';
    }
endif;


/**
 * Check if provider is enabled and has valid credentials, if true it will be enable for user to click on it
 * if provider doesn't have valid credentials radio for select will be disabled.
 *
 * @param $id
 * @param array $providers
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_get_active_provider')):
    function meks_video_importer_get_provider_status($id, $counter, $providers = array()) {
        if (!empty($providers)){
            $providers = meks_video_importer_get_valid_providers();
        }

        if (isset($_GET['template'])) {
            if (meks_video_importer_get_template_option('provider') == $id) {
                return 'checked';
            }

            return '';
        }

        if (1 < count($providers)) {
            if ($counter == 0) {
                return 'checked';
            }

            return '';
        } else {
            if (in_array($id, $providers)) {
                return 'checked';
            }

            return 'disabled';
        }
    }
endif;

/**
 * Check if two variables have the same value
 * and output the string if true
 *
 * @param mixed $a
 * @param mixed $b
 * @param string $output
 * @param string  output string on true or empty string on false
 * @since    1.0.0
 */
if (!function_exists('meks_video_importer_selected')):
    function meks_video_importer_selected($a, $b, $output = 'selected') {

        return (string)$a === (string)$b ? $output : '';
    }
endif;

/**
 * Check if CURL response is valid
 *
 * @param $response
 * @since    1.0.1
 */
if(!function_exists('meks_video_importer_is_valid_response')):
    function meks_video_importer_is_valid_response($response) {
	    if( empty($response) ){
		    return false;
	    }
	
	    if( is_wp_error($response) ){
		    return false;
	    }
	
	    if( $response['response']['code'] < 200 || $response['response']['code'] >= 400  ){
		    return false;
	    }
	
	    return true;
    }
endif;

/**
 * Checks if this page is plugins settings page
 *
 * @since 1.0.4
 * @return boolean
 */
if(!function_exists('meks_video_importer_is_plugins_page')):
    function meks_video_importer_is_plugins_page(){
		if(!empty($_GET['page']) && $_GET['page'] === MEKS_VIDEO_IMPORTER_PAGE_SLUG){
			return true;
		}
		
		return false;
    }
endif;


/**
 * Upsell Meks themes with notice info
 *
 * @return void
 */
add_action( 'admin_notices', 'meks_admin_notice__info' );

if ( ! function_exists( 'meks_admin_notice__info' ) ) :

	function meks_admin_notice__info() {

		$meks_themes  = array( 'shamrock', 'awsm', 'safarica', 'seashell', 'sidewalk', 'throne', 'voice', 'herald', 'vlog', 'gridlove', 'pinhole', 'typology', 'trawell', 'opinion', 'johannes', 'megaphone', 'toucan', 'roogan' );
		$active_theme = get_option( 'template' );


		if ( ! in_array( $active_theme, $meks_themes ) ) {

			if ( get_option('has_transient') == 0 ) {
				set_transient( 'meks_admin_notice_time_'. get_current_user_id() , true, WEEK_IN_SECONDS );
				update_option('has_transient', 1);
				update_option('track_transient', 1);
			}
			
			if (  !get_option('meks_admin_notice_info') || ( get_option('track_transient') && !get_transient( 'meks_admin_notice_time_'. get_current_user_id() ) ) ) {

				$all_themes = wp_get_themes();

				?>
				<div class="meks-notice notice notice-info is-dismissible">
					<p>
                        <?php
							echo sprintf( __( 'You are currently using %1$s theme. Did you know that Meks plugins give you more features and flexibility with one of our <a href="%2$s">Meks themes</a>?', MEKS_VIDEO_IMPORTER_PAGE_SLUG ), $all_themes[ $active_theme ], 'https://1.envato.market/4DE2o' );
						?>
					</p>
				</div>
				<?php

			}
		} else {
			delete_option('meks_admin_notice_info');
			delete_option('has_transient');
			delete_option('track_transient');
		}
	}

endif;


/**
 * Colose/remove info notice with ajax
 *
 * @return void
 */
add_action( 'wp_ajax_meks_remove_notification', 'meks_remove_notification' );
add_action( 'wp_ajax_nopriv_meks_remove_notification', 'meks_remove_notification' );

if ( !function_exists( 'meks_remove_notification' ) ) :
	function meks_remove_notification() {
		add_option('meks_admin_notice_info', 1);
		if ( !get_transient( 'meks_admin_notice_time_'. get_current_user_id() ) ) {
			update_option('track_transient', 0);
		}
	}
endif;

/**
 * Add admin scripts
 *
 * @return void
 */
add_action( 'admin_enqueue_scripts', 'meks_video_importer_enqueue_admin_scripts' );

if ( !function_exists( 'meks_video_importer_enqueue_admin_scripts' ) ) :
	function meks_video_importer_enqueue_admin_scripts() {
        wp_enqueue_script( 'meks-video-importer-ajax', MEKS_VIDEO_IMPORTER_URL . 'assets/js/admin.js', array('jquery'), MEKS_VIDEO_IMPORTER_VERSION );
	}
endif;
