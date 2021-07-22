<?php
    $import_options = meks_video_importer_get_import_options();
    $vimeo_options = Meks_Video_Importer_Vimeo::getInstance()->get_options_from_template();
    $fetch_from_options = Meks_Video_Importer_Vimeo::getInstance()->get_select_options();
?>
<tr class="form-field type-change provider vimeo <?php echo meks_video_importer_selected($import_options['provider'], 'vimeo', 'active'); ?>">
    <th class="row">
        <label for="mvi-vimeo-type"><?php echo esc_html__("Type", 'meks-video-importer'); ?> <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span></label>
    </th>
    <td>
        <select name="mvi-vimeo-type" id="mvi-vimeo-type" data-type="vimeo-type">
            <?php foreach ($fetch_from_options as $key => $option) :?>
                <option value="<?php echo esc_attr($key); ?>" <?php echo meks_video_importer_selected($vimeo_options['mvi-vimeo-type'], $key); ?>><?php echo  $option; ?></option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>

<tr class="form-field type-change provider vimeo <?php echo meks_video_importer_selected($import_options['provider'], 'vimeo', 'active'); ?>">
    <th class="row">
        <label for="mvi-vimeo-id"><?php echo esc_html__("ID", 'meks-video-importer'); ?> <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span> &nbsp;</label>
    </th>
    <td>
        <input class="smaller" id="mvi-vimeo-id" name="mvi-vimeo-id" type="text" value="<?php echo esc_attr($vimeo_options['mvi-vimeo-id']); ?>">
        <br>
        <p class="description type-change vimeo-type user <?php echo meks_video_importer_selected($vimeo_options['mvi-vimeo-type'], 'user', 'active'); ?>">
            <small><?php _e('Example: https://vimeo.com/<strong>SomeUser</strong>', 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('User ID: <strong>SomeUser</strong>' , 'meks-video-importer'); ?></small>
        </p>

        <p class="description type-change vimeo-type group <?php echo meks_video_importer_selected($vimeo_options['mvi-vimeo-type'], 'group', 'active'); ?>">
            <small><?php _e('Example: https://vimeo.com/groups/<strong>SomeGroup</strong>', 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('Group ID: <strong>SomeGroup</strong>' , 'meks-video-importer'); ?></small>
        </p>
        <p class="description type-change vimeo-type channel <?php echo meks_video_importer_selected($vimeo_options['mvi-vimeo-type'], 'channel', 'active'); ?>">
            <small><?php _e('Example: https://vimeo.com/channels/<strong>SomeChannel</strong>', 'meks-video-importer'); ?></small>
            <br>
            <small><?php _e('Channel ID: <strong>SomeChannel</strong>' , 'meks-video-importer'); ?></small>
        </p>
    </td>
</tr>

<tr class="form-field type-change provider vimeo <?php echo meks_video_importer_selected($import_options['provider'], 'vimeo', 'active'); ?>">
    <th class="row">
        <label for="mvi-vimeo-from-to-page"><?php echo esc_html__("Pages", 'meks-video-importer'); ?></label>
    </th>
    <td>
        <input class="smaller" id="mvi-vimeo-from-page" name="mvi-vimeo-from-page" type="number" value="<?php echo $vimeo_options['mvi-vimeo-from-page']; ?>"> -
        <input class="smaller" id="mvi-vimeo-to-page" name="mvi-vimeo-to-page" type="number" value="<?php echo $vimeo_options['mvi-vimeo-to-page']; ?>">
        <button class="button button-default" id="mvi-fetch-vimeo"><?php echo esc_html__("Fetch videos", 'meks-video-importer'); ?></button>
        <div id="mvi-vimeo-loader" class="spinner"></div>
    </td>
</tr>

<tr id="vimeo-messages" class="form-field type-change provider vimeo active">
    <td class="status">

    </td>
</tr>