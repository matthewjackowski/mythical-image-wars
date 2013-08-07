<?php
/*
 * Mythic Image Wars Settings Page
 * This template allows for config/testing/debug of the plugin
 * It uses the 'Settings API' for plugin configuration
 * In debug mode this page will provide internal info on the MythicImageWars Plugin Class
 * Last it has a 'test' mode where it renders some included test images + meta
 */
?>
<?php
//Media Uploader Scripts for js control
wp_enqueue_script('media_upload');
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');
?>
<div class="wrap">
    <h2>Mythic Image Wars Settings Page</h2>
    <form method="POST" action="options.php">
    <?php settings_fields('main_section'); ?>
    <?php do_settings_sections('main'); ?>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Main Settings'); ?>" />
    </form>
<form method="POST" action="options.php">
    <?php settings_fields('debug_section'); ?>
    <?php do_settings_sections('debug'); ?>
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Debug Settings'); ?>" />
</form>
</form>
</div>
    <script type="text/javascript">
    
    jQuery(document).ready(function() {
        var uploadId = '';
        jQuery('.miw-upload-button').click(function() {
            uploadId = jQuery(this).prev();
  //          formfield = jQuery('.miw-upload-text').attr('name');
            tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
            return false;
        });
        window.send_to_editor = function(html) {
            imgurl = jQuery('img',html).attr('src');
            uploadId.val(imgurl);
            tb_remove();
        }
});

</script>