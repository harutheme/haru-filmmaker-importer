<?php $youtube_access = Meks_Video_Importer_Youtube::getInstance()->get_access_credentials(); ?>
<tr class="form-field">
    <th class="row">
        <h2><?php echo esc_html__('YouTube', 'meks-video-importer'); ?></h2>
    </th>
</tr>
<tr class="form-field">
    <th class="row">
        <label for="mvi-youtube-api-key"><?php echo esc_html__('API key', 'meks-video-importer'); ?>
            <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span> &nbsp;</label>
    </th>
    <td>
        <input class="smaller" id="mvi-youtube-api-key" name="mvi-youtube-api-key" type="text" value="<?php echo esc_attr($youtube_access['mvi-youtube-api-key']); ?>">
        <div id="mvi-youtube-loader" class="spinner"></div>
        <div id="mvi-youtube-api-verify-message" class="dib">
            <?php if (!empty($youtube_access['mvi-youtube-api-key'])): ?>
                <?php if (!empty($youtube_access['mvi-youtube-api-key-verified']) && $youtube_access['mvi-youtube-api-key-verified']): ?>
                    <p class="mvi-success dib">
                        <span class="dashicons dashicons-yes"></span><?php echo esc_html__('Key successfully verified', 'meks-video-importer'); ?>
                    </p>
                <?php else: ?>
                    <p class="mvi-error dib">
                        <span class="dashicons dashicons-no"></span><?php echo esc_html__('Key not verified. Please try adding it again.', 'meks-video-importer'); ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <p class="description"><?php _e( 'Where can I <a href="https://mekshq.com/faq/youtube-api-key-video-importer-wordpress-plugin/" target="_blank">get YouTube API key</a>?', 'meks-video-importer'); ?></p>
    </td>
</tr>