
<?php
if (!current_user_can('manage_options'))
{
    wp_die( esc_html__('You do not have sufficient permissions to access this page.') );
}
?>
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>