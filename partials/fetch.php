<br><h2><?php echo esc_html__('Provider settings', 'meks-video-importer'); ?></h2>
<form method="post" id="mvi-video-fetch">
    <table class="form-table">
        <tbody>
        <tr class="form-field">
            <th class="row">
                <label for="mvi-provider"><?php echo esc_html__("Provider", 'meks-video-importer'); ?>: &nbsp;</label>
            </th>
            <td>
                <?php
                $i = 0;
                $valid_providers = meks_video_importer_get_valid_providers();

                if(!empty($valid_providers)):
                    foreach (meks_video_importer_get_providers() as $id => $provider) : ?>
                        <label><input type="radio" class="mvi-provider" name="provider" data-type="provider" value="<?php echo esc_attr($id); ?>" <?php echo esc_attr(meks_video_importer_get_provider_status($id, $i, $valid_providers)); ?>>&nbsp;
                            <?php echo $provider; ?>
                        </label><br>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>

        <?php do_action('meks-video-importer-print-providers'); ?>
        </tbody>
    </table>
</form>