<br><h2><?php echo esc_html__('Templates', 'meks-video-importer'); ?></h2>
<?php $templates = Meks_Video_Importer_Saved_Templates::getInstance()->get_templates(); ?>
<?php if (!empty($templates)): ?>
    <ul>
        <?php foreach ($templates as $key => $template) : ?>
            <li>
                <a href="<?php echo esc_url(admin_url('tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG . '&template=') . $key ); ?>"><?php echo $template['name']; ?> (<?php echo $template['provider']; ?>) </a>
                <a class="mvi-delete-template" href="javascript:void(0)" data-id="<?php echo esc_attr($key); ?>">
                    <span class="mvi-error"><span class="dashicons dashicons-no"></span></span><span class="meks-video-importer-delete-template-message"></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
   <?php echo esc_html__('You don\'t have any saved templates yet. To add a template, go to the import tab, fill the data and click Save Template & Import.', 'meks-video-importer'); ?>
<?php endif; ?>