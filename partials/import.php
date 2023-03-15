<?php
    $post_types = meks_video_importer_get_posts_types_with_taxonomies();
    $post_formats = meks_video_importer_get_posts_formats();
    $post_statuses = get_post_statuses();
    $users = get_users();
    $import_options = meks_video_importer_get_import_options();
?>
<form method="post" id="mvi-video-import">
    <?php wp_nonce_field('mvi-import'); ?>
    <div id="mvi-fetched" class="wrap"></div>
    <h2><?php echo esc_html__('Import settings', 'meks-video-importer'); ?></h2>
    <table class="form-table">
        <tbody>

        <!-- Classic or WP 5.0 editor -->
        <tr class="form-field">
            <th scope="row">
                <label for="mvi-editor"><?php echo esc_html__("Import videos as", 'meks-video-importer'); ?></label>
            </th>
            <td>
                <label for="mvi-editor">
                    <input type="radio" id="mvi-editor" name="mvi-editor" value="editor"<?php echo meks_video_importer_selected( $import_options['mvi-editor'], 'editor', 'checked'); ?>/>
                    <?php echo esc_html__("Blocks (WP 5.0+)", 'meks-video-importer'); ?>
                </label>
                <br>
                <label for="mvi-editor-classic"><input type="radio" id="mvi-editor-classic" name="mvi-editor" value="classic"  <?php echo meks_video_importer_selected( $import_options['mvi-editor'], 'classic', 'checked'); ?>/> <?php echo esc_html__("Classic editor embed (up to WP 4.9)", 'meks-video-importer'); ?></label>
            </td>
        </tr> <!-- WP 5.0 or Classic -->

        <!-- Post type -->
        <tr class="form-field">
            <th scope="row">
                <label for="mvi-post-type"> <?php echo esc_html__("Post type", 'meks-video-importer'); ?>
                    <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span></label>
            </th>
            <td>
                <select name="mvi-post-type" id="mvi-post-type" data-type="post-type">
                    <?php foreach ($post_types as $post_type) : ?>
                        <option value="<?php echo esc_attr($post_type->name); ?>" <?php echo meks_video_importer_selected( $import_options['mvi-post-type'], $post_type->name); ?>>
                            <?php echo $post_type->label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr> <!-- Post type End -->

        <!-- Post format -->
        <?php if(!empty($post_formats)): ?>
            <tr class="form-field type-change post-type post <?php echo meks_video_importer_selected($import_options['mvi-post-type'], 'post', 'active'); ?>">
                <th scope="row">
                    <label for="mvi-post-format"> <?php echo esc_html__("Post format", 'meks-video-importer'); ?></label>
                </th>
                <td>
                    <select name="mvi-post-format" id="mvi-post-format" data-type="post-format">
                        <?php foreach ($post_formats as $post_format) : ?>
                            <option value="<?php echo esc_attr($post_format); ?>" <?php echo meks_video_importer_selected( $import_options['mvi-post-format'], $post_format); ?>>
                                <?php echo ucfirst($post_format); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr> <!-- Post format End -->
        <?php endif; ?>

        <!-- Post Status -->
        <tr class="form-field">
            <th scope="row">
                <label for="mvi-post-status"> <?php echo esc_html__("Post status", 'meks-video-importer'); ?>
                    <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span></label>
            </th>
            <td>
                <select name="mvi-post-status" id="mvi-post-status">
                    <?php foreach ($post_statuses as $post_status_key => $post_status) : ?>
                        <option value="<?php echo $post_status_key; ?>" <?php echo meks_video_importer_selected( $import_options['mvi-post-status'], $post_status_key); ?>>
                            <?php echo $post_status; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr><!-- Post Status End -->

        <!-- Description -->
        <tr class="form-field">
            <th scope="row">
                <label for="mvi-description"> <?php echo esc_html__("Insert video description in content", 'meks-video-importer'); ?>
            </th>
            <td>
                <input type="hidden" name="mvi-description" value="off">
                <input type="checkbox" name="mvi-description" id="mvi-description" value="on" <?php echo meks_video_importer_selected( $import_options['mvi-description'], 'on', 'checked'); ?>>
            </td>
        </tr> <!-- Description End -->

        <!-- Date -->
        <tr class="form-field">
            <th scope="row">
                <label for="mvi-date"> <?php echo esc_html__("Set date to original video date", 'meks-video-importer'); ?>
            </th>
            <td>
                <input type="hidden" name="mvi-date" value="off">
                <input type="checkbox" name="mvi-date" id="mvi-date" value="on" <?php echo meks_video_importer_selected( $import_options['mvi-date'], 'on', 'checked'); ?>>
            </td>
        </tr> <!-- Description End -->
        <!-- Post Taxonomies -->
        <?php
        foreach ($post_types as $post_type) :
            if ($post_type->taxonomies):
                //print_r( $post_type->taxonomies );
                $saved_taxonomies = $import_options['mvi-taxonomies'];
                foreach ($post_type->taxonomies as $taxonomy) :

                    if( !is_array($taxonomy) ){
                        continue;
                    }

                    ?>
                    <tr class="form-field mvi-<?php echo esc_attr($taxonomy['id']); ?> <?php echo esc_attr($post_type->name)?> type-change post-type <?php echo esc_attr(meks_video_importer_taxonomy_classes($post_type)); ?>">
                        <th class="row"><?php echo $taxonomy['name']; ?></th>
                        <td>
                            <?php
                            if ($taxonomy['hierarchical']):
                                if (empty($taxonomy['terms'])) continue;

                                foreach ($taxonomy['terms'] as $term) :

                                    $is_checked = !empty($saved_taxonomies[$taxonomy['id']]) && in_array($term->term_id, explode(',', $saved_taxonomies[$taxonomy['id']])) ? 'checked' : '';
                                    $taxonomy_id = $taxonomy['id'] . '-' . $term->term_id; ?>

                                    <label for="<?php echo esc_attr($taxonomy_id); ?>">
                                        <input id="<?php echo esc_attr($taxonomy_id); ?>" type="checkbox" name="mvi-taxonomies[<?php echo esc_attr($taxonomy['id']); ?>]" value="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_attr($is_checked); ?>>
                                        <?php echo $term->name; ?>
                                    </label> <br>

                                <?php endforeach;
                            else: ?>
                                <?php $saved_taxonomy = !(empty($saved_taxonomies[$taxonomy['id']])) ? $saved_taxonomies[$taxonomy['id']] : ''; ?>
                                <label for="mvi-<?php echo esc_attr($taxonomy['id']); ?>" class="mvi-not-hierarchical">
                                    <input type="text" class="smaller" id="mvi-<?php echo esc_attr($taxonomy['id']); ?>" name="mvi-taxonomies[<?php echo esc_attr($taxonomy['id']); ?>]" value="<?php echo esc_attr($saved_taxonomy); ?>" data-name="<?php echo esc_attr($taxonomy['id']); ?>">
                                </label>

                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                endforeach;
            endif;

            // HaruTheme Customize
            if ( $post_type->name == 'haru_video' ) : ?>
                <?php
                    $metaboxes = array( 'person' ); // person,...
                    foreach ( $metaboxes as $meta ) :
                    // Hidden now
                    if ( $meta != 'person' ) :    
                ?>
                <tr class="form-field mvi-video_<?php echo esc_attr( $meta ); ?> <?php echo esc_attr($post_type->name)?> type-change post-type <?php echo esc_attr(meks_video_importer_taxonomy_classes($post_type)); ?>" style="display: none">
                    <th class="row" style="text-transform: capitalize;"><?php echo esc_html__( 'Video ', 'meks-video-importer' ) . esc_html( $meta ); ?></th>
                    <td>
                        <?php

                        $query_args  = array(
                            'post_type'         => 'haru_' . $meta,
                            'post_status'       => 'publish',
                            'posts_per_page'    => -1,
                        );

                        $page_query = new WP_Query( $query_args );

                        $page_list = array();

                        if ( $page_query->have_posts() ) :
                            while($page_query->have_posts()) : $page_query->the_post();
                        ?>
                            <label for="<?php echo esc_attr( get_the_ID() ); ?>">
                                <input id="<?php echo esc_attr( get_the_ID() ); ?>" type="checkbox" name="mvi-haru_<?php echo esc_attr( $meta ); ?>[<?php echo esc_attr( get_the_ID() ); ?>]" value="<?php echo esc_attr( get_the_ID() ); ?>">
                                <?php echo get_the_title(); ?>
                            </label> <br>
                        <?php
                                $key = get_the_ID();
                                $page_list[$key] = get_the_title();
                            endwhile;
                        else :
                            // Do something
                        endif;
                        wp_reset_query();
                        ?>
                    </td>
                </tr>

                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif;

        endforeach; ?>
        <!-- Post Taxonomies End -->

        <!-- User -->
        <tr class="form-field">
            <th class="row">
                <label for="mvi-author" id="mvi-author"><?php echo esc_html__("User", 'meks-video-importer'); ?></label>
            </th>
            <td>
                <select name="mvi-author" id="mvi-author">
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_attr($user->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr><!-- User End -->
        </tbody>
    </table>
    <div id="import-buttons">

        <?php submit_button(esc_html__("Import", 'meks-video-importer'), 'primary', 'mvi-import-posts'); ?>
        <label>
            <?php echo esc_html__('Template name', 'meks-video-importer'); ?>:
            <?php if(isset($_GET['template']) && !empty($_GET['template'])): ?>
                <input type="text" id="template_name" value="<?php echo esc_attr($import_options['name']); ?>" disabled>
                <input type="hidden" id="template_id" value="<?php echo esc_attr($_GET['template']); ?>">
            <?php else: ?>
                <input type="text" id="template_name" value="<?php echo esc_attr($import_options['name']); ?>">
            <?php endif; ?>
        </label>
        <?php submit_button(esc_html__("Save Template & Import", 'meks-video-importer'), 'default', 'mvi-save-and-import-posts', true, array('data-save' => 'true')); ?><span id="save-and-import-messages"></span>
    </div>
</form>