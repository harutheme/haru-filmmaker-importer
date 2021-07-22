<?php $vimeo_access = Meks_Video_Importer_Vimeo::getInstance()->get_access_credentials(); ?>
<tr class="form-field">
    <th class="row">
        <h2><?php echo esc_html__('Vimeo', 'meks-video-importer'); ?></h2>
    </th>
</tr>
<tr class="form-field">
    <th class="row">
        <label for="mvi-vimeo-client-id"><?php echo esc_html__('Client ID', 'meks-video-importer'); ?>
            <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span> &nbsp;</label>
    </th>
    <td>
        <input class="smaller" id="mvi-vimeo-client-id" name="mvi-vimeo-client-id" type="text" value="<?php echo esc_attr($vimeo_access['mvi-vimeo-client-id']); ?>">
    </td>
</tr>
<tr class="form-field">
    <th class="row">
        <label for="mvi-vimeo-client-secret"><?php echo esc_html__('Client Secret', 'meks-video-importer'); ?>
            <span class="description">(<?php echo esc_html__("required", 'meks-video-importer'); ?>)</span> &nbsp;</label>
    </th>
    <td>
        <input class="smaller" id="mvi-vimeo-client-secret" name="mvi-vimeo-client-secret" type="text" value="<?php echo esc_attr($vimeo_access['mvi-vimeo-client-secret']); ?>">
        <div id="mvi-vimeo-loader" class="spinner"></div>
        <div id="mvi-vimeo-api-verify-message" class="dib">
            <?php if (!empty($vimeo_access['meks-video-importer-vimeo-access-token']) || !empty($vimeo_access['mvi-vimeo-client-id']) || !empty($vimeo_access['mvi-vimeo-client-secret'])):; ?>
                <?php if (!empty($vimeo_access['meks-video-importer-vimeo-access-token']) && !empty($vimeo_access['mvi-vimeo-client-id']) && !empty($vimeo_access['mvi-vimeo-client-secret'])): ?>
                    <p class="mvi-success dib">
                        <span class="dashicons dashicons-yes"></span><?php echo esc_html__('Credentials successfully verified', 'meks-video-importer'); ?>
                    </p>
                <?php else: ?>
                    <p class="mvi-error dib">
                        <span class="dashicons dashicons-no"></span><?php echo esc_html__('Credentials not verified. Please try adding it again.', 'meks-video-importer'); ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <p class="description"><?php _e( 'Where can I <a href="https://mekshq.com/faq/vimeo-client-id-secret-video-importer-wordpress-plugin/" target="_blank">get Vimeo Client ID & Secret</a>?', 'meks-video-importer'); ?></p>
    </td>
</tr>