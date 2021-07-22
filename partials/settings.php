<div id="mvi-settings-notices">
    <?php if(isset($_GET['ref']) && !empty($_GET['ref']) && $_GET['ref'] == 'import'): ?>
        <p class="mvi-warning"><?php echo esc_html__("For importing videos, you will need to add at least one verified provider's API credentials.", 'meks-video-importer'); ?></p>
    <?php endif; ?>
</div>
<form method="post" id="mvi-settings">
    <table class="form-table">
        <tbody>
            <?php do_action('meks-video-importer-settings'); ?>
        </tbody>
    </table>
</form>