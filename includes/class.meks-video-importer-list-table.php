<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class Meks_Video_Importer_List_Table
 *
 * @since    1.0.0
 */
if (!class_exists('Meks_Video_Importer_List_Table')):
    class Meks_Video_Importer_List_Table extends WP_List_Table {

        /**
         * Set items that will be displayed in the Table
         *
         * @param $items
         * @since    1.0.0
         */
        public function set_items($items) {
            $this->items = $items;
        }

        /**
         * Prepare items for display
         *
         * @since    1.0.0
         */
        public function prepare_items() {
            $columns = $this->get_columns();
            $this->_column_headers = array($columns);
        }

        /**
         * Get column ids and title
         *
         * @return array
         * @since    1.0.0
         */
        public function get_columns() {
            return array(
                'cb'     => 'cb',
                'image'  => 'Image',
                'title'  => 'Title',
                'status' => 'Status',
            );
        }

        /**
         * Get column's checkbox
         *
         * @param object $item
         * @return string
         * @since    1.0.0
         */
        protected function column_cb($item) {
	        $importable = 'checked';
	
        	if(isset($item['embeddable']) && !$item['embeddable']){
		        $importable = 'disabled';
        	}

            return '<input id="cb-select-' . esc_attr($item['id']) . '" type="checkbox" name="mvi-video-id" value="' . esc_attr($item['id']) . '" ' . $importable . '>';
        }

        /**
         * This method prints the html of row
         *
         * @param object $item
         * @param string $column_name
         * @since    1.0.0
         */
        protected function column_default($item, $column_name) {
            switch ($column_name) {
                case 'image':
                    echo '<a href="' . esc_attr($item['url']) . '" target="_blank"><img src="' . esc_attr($item['image']) . '"></a>';
                    break;
                case 'title':
                    echo '<a href="' . esc_attr($item['url']) . '" target="_blank">' . esc_attr($item['title']) . '</a>';
                    break;
                case 'status':
                	$message = !(empty($item['message'])) ? $item['message'] : '';
                    $class = !(empty($message)) ? 'mvi-error' : '';
                	echo '<span class="mvi-status-messages ' . $class . '">' . $message . '</span>';
                    $hidden_fields = meks_video_importer_get_hidden_fields();
                    foreach ($hidden_fields as $hidden_field) {
                        echo '<input class="mvi-video-' . esc_attr($hidden_field) . '" type="hidden" value="' . esc_attr($item[$hidden_field]) . '">';
                    }
                    break;
            }
        }

        /**
         * Main function that displays the table
         *
         * @return string
         * @since    1.0.0
         */
        public function display() {
            ob_start();

            $singular = $this->_args['singular'];

            $this->display_tablenav('top');

            $this->screen->render_screen_reader_content('heading_list');

            require_once MEKS_VIDEO_IMPORTER_PARTIALS . 'list-table.php';

            $this->display_tablenav('bottom');

            return ob_get_clean();
        }

        public function print_column_headers( $with_id = true ) {
            list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

            $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $current_url = remove_query_arg( 'paged', $current_url );

            if ( isset( $_GET['orderby'] ) ) {
                $current_orderby = $_GET['orderby'];
            } else {
                $current_orderby = '';
            }

            if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
                $current_order = 'desc';
            } else {
                $current_order = 'asc';
            }

            if ( ! empty( $columns['cb'] ) ) {
                static $cb_counter = 1;
                $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
                    . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" checked/>';
                $cb_counter++;
            }

            foreach ( $columns as $column_key => $column_display_name ) {
                $class = array( 'manage-column', "column-$column_key" );

                if ( in_array( $column_key, $hidden ) ) {
                    $class[] = 'hidden';
                }

                if ( 'cb' === $column_key )
                    $class[] = 'check-column';
                elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                    $class[] = 'num';

                if ( $column_key === $primary ) {
                    $class[] = 'column-primary';
                }

                if ( isset( $sortable[$column_key] ) ) {
                    list( $orderby, $desc_first ) = $sortable[$column_key];

                    if ( $current_orderby === $orderby ) {
                        $order = 'asc' === $current_order ? 'desc' : 'asc';
                        $class[] = 'sorted';
                        $class[] = $current_order;
                    } else {
                        $order = $desc_first ? 'desc' : 'asc';
                        $class[] = 'sortable';
                        $class[] = $desc_first ? 'asc' : 'desc';
                    }

                    $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
                }

                $tag = ( 'cb' === $column_key ) ? 'td' : 'th';
                $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
                $id = $with_id ? "id='$column_key'" : '';

                if ( !empty( $class ) )
                    $class = "class='" . join( ' ', $class ) . "'";

                echo "<$tag $scope $id $class>$column_display_name</$tag>";
            }
        }
    }
endif;