<nav class="nav-tab-wrapper">
    <a href="<?php echo esc_url(admin_url('tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG)); ?>" class="nav-tab <?php echo !isset($_GET['tab']) || empty($_GET['tab']) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Import', 'meks-video-importer'); ?></a>
    <a href="<?php echo esc_url(admin_url('tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG . '&tab=templates')); ?>" class="nav-tab <?php echo meks_video_importer_selected($_GET['tab'], 'templates', 'nav-tab-active')?>">
<?php echo esc_html__('Templates', 'meks-video-importer'); ?></a>
    <a href="<?php echo esc_url(admin_url('tools.php?page=' . MEKS_VIDEO_IMPORTER_PAGE_SLUG . '&tab=settings')); ?>" class="nav-tab <?php echo meks_video_importer_selected($_GET['tab'], 'settings', 'nav-tab-active')?> "><?php echo esc_html__('Settings', 'meks-video-importer'); ?></a>
</nav>