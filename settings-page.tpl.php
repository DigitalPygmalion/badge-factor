<div class="wrap">
    <h1><?php _e('Badge Factor Settings', 'badgefactor'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('badgefactor-settings-group'); ?>
        <?php do_settings_sections('badgefactor-settings-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Form Page Text', 'badgefactor'); ?></th>
                <td><input type="text" name="badgefactor_form_page_text"
                           value="<?php echo esc_attr(get_option('badgefactor_form_page_text', '<h3>' . __('In order to get this badge, please submit the following.', 'badgefactor') . '</h3>')); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Default Form Button Text', 'badgefactor'); ?></th>
                <td><input type="text" name="badgefactor_default_form_button_text"
                           value="<?php echo esc_attr(get_option('badgefactor_default_form_button_text', __('Submit', 'badgefactor'))); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Default Certificate Name', 'badgefactor'); ?></th>
                <td><input type="text" name="badgefactor_default_certificate_name"
                           value="<?php echo esc_attr(get_option('badgefactor_default_certificate_name', __('Badge', 'badgefactor'))); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Partage public du badge', 'badgefactor'); ?></th>
                <td><input type="checkbox" name="badge_partage_public" <?php if(get_option('badge_partage_public')) echo "CHECKED" ?> />
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>

    </form>
</div>
