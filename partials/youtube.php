<?php
    $options = Meks_Video_Importer_Youtube::getInstance()->get_select_options();
    $youtube_options = Meks_Video_Importer_Youtube::getInstance()->get_options_from_template();
    $import_options = meks_video_importer_get_import_options();
?>
<tr class="form-field type-change provider youtube <?php echo meks_video_importer_selected($import_options['provider'], 'youtube', 'active'); ?>">
    <th class="row">
        <label for="mvi-youtube-type"><?php echo esc_html__("Type", 'meks-video-importer'); ?> <span class="description">(<?php echo esc_html__('required', 'meks-video-importer'); ?>)</span></label>
    </th>
    <td>
        <select name="mvi-youtube-type" id="mvi-youtube-type" data-type="youtube-type">
            <?php foreach ($options as $key => $option) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php echo meks_video_importer_selected($youtube_options['mvi-youtube-type'], $key); ?>><?php echo  $option; ?></option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>

<tr class="form-field type-change provider youtube <?php echo meks_video_importer_selected($import_options['provider'], 'youtube', 'active'); ?>">
    <th class="row">
        <label for="mvi-youtube-id"><?php esc_html_e("ID", 'meks-video-importer'); ?> <span class="description">(<?php esc_html_e('required', 'meks-video-importer'); ?>)</span> &nbsp;</label>
    </th>
    <td>
        <input class="smaller" id="mvi-youtube-id" name="mvi-youtube-id" type="text" value="<?php echo esc_attr($youtube_options['mvi-youtube-id']); ?>">
        <button class="button button-default" id="mvi-fetch-youtube"><?php esc_html_e('Fetch videos', 'meks-video-importer'); ?></button>
        <div id="mvi-youtube-loader" class="spinner"></div>
        <br>
        <p class="description type-change youtube-type playlist <?php echo meks_video_importer_selected($youtube_options['mvi-youtube-type'], 'playlist', 'active'); ?>" >
            <small><?php _e('Example: https://www.youtube.com/watch?v=g72rFb6P13w&list=<strong>PL2acA3M-2dSTuk6tSUblX_P7A0Yxj_Xca</strong>' , 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('Playlist ID: <strong>PL2acA3M-2dSTuk6tSUblX_P7A0Yxj_Xca</strong>' , 'meks-video-importer'); ?></small>
        </p>

        <p class="description type-change youtube-type channelId <?php echo meks_video_importer_selected($youtube_options['mvi-youtube-type'], 'channelId', 'active'); ?>">
            <small class=""><?php _e('Example: https://www.youtube.com/channel/<strong>UCzs2Skg6P_9gdQGZo65YQTg</strong>', 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('Channel ID: <strong>UCzs2Skg6P_9gdQGZo65YQTg</strong>' , 'meks-video-importer'); ?></small>
        </p>

        <p class="description type-change youtube-type search <?php echo meks_video_importer_selected($youtube_options['mvi-youtube-type'], 'search', 'active'); ?>">
            <small><?php _e('Example: <strong>Meks WordPress Themes</strong>' , 'meks-video-importer'); ?></small>
        </p>
        <p class="description type-change youtube-type userId <?php echo meks_video_importer_selected($youtube_options['mvi-youtube-type'], 'userId', 'active'); ?>">
            <small><?php _e('Example: https://www.youtube.com/user/<strong>SomeUser</strong>', 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('User ID: <strong>SomeUser</strong>' , 'meks-video-importer'); ?></small>
        </p>

    </td>
</tr>

<tr id="youtube-messages" class="form-field type-change provider youtube <?php echo meks_video_importer_selected($import_options['provider'], 'youtube', 'active'); ?>">
    <td class="status">

    </td>
</tr>