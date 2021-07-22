<?php

if (!class_exists('Meks_Video_Importer_Saved_Templates')):
    class Meks_Video_Importer_Saved_Templates {


        private $saved_templates = array();

        /**
         * Call this method to get singleton
         *
         * @return Meks_Video_Importer_Saved_Templates
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
            add_action('wp_ajax_mvi_save_template', array($this, 'save_template'));
            add_action('wp_ajax_mvi_delete_template', array($this, 'delete_template'));

            $this->saved_templates = get_option('mvi-templates');
        }

        /**
         * Ajax call back for saving templates
         */
        public function save_template() {
            $data = $_POST['data'];

            if (empty($data)){
                wp_send_json_error(array('type' => 'error', 'msg' => __('Not valid template.', 'meks-video-importer')));
            }

            $id = $data['id'];

            // Update template
            if (array_key_exists($id, $this->saved_templates)) {
                $this->saved_templates[$id] = $data;
            }else{
                if(!empty($this->saved_templates)){
                    $saved = $this->saved_templates;
                    end($saved);
                    $template_new_id = absint(key($saved)) + 1;
                }else{
                    $template_new_id = 1;
                }
                $this->saved_templates[$template_new_id] = $data;
            }

            update_option('mvi-templates', $this->saved_templates);
            wp_send_json_success(array('type' => 'success', 'msg' => __('Template saved.', 'meks-video-importer')));
        }

        /**
         * Ajax call back for deleting templates
         */
        public function delete_template(){
            $id = $_POST['id'];
            if(empty($id)){
                wp_send_json_error(array('type' => 'error', 'msg' => __("Id can't be empty.", 'meks-video-importer')));
            }


            if (empty($this->saved_templates[$id])){
                wp_send_json_error(array('type' => 'error', 'msg' => __("Template with that name doesn't exits.", 'meks-video-importer')));
            }

            unset($this->saved_templates[$id]);
            update_option('mvi-templates', $this->saved_templates);
            wp_send_json_success(array('type' => 'success', 'msg' => __('Template deleted.', 'meks-video-importer')));
        }

        /**
         * Get all templates
         *
         * @return array|null
         */
        public function get_templates(){
            return $this->saved_templates;
        }

        /**
         * Get single template by mane
         *
         * @param $id
         * @return mixed
         */
        public function get_template($id){
            return $this->saved_templates[$id];
        }


        /**
         * Get single template option
         *
         * @param $id
         * @param $option
         * @return mixed
         */
        public function get_template_option($id, $option){
            return !empty($this->saved_templates[$id][$option]) ? $this->saved_templates[$id][$option] : null;
        }
    }
endif;