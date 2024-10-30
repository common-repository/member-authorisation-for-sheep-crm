<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://tallprojects.co.uk
 * @since      1.0.0
 *
 * @package    Sheep_Member_Authorisation
 * @subpackage Sheep_Member_Authorisation/admin/templates
 */
?>

<div class="wrap">
  <h2><?php _e( 'Sheep member authorisation', 'sheep-authorisation' ); ?></h2>

  <form method="post" action="options.php">
    <?php settings_fields( 'sheep_member_authorisation_option_group' ); ?>
    <?php do_settings_sections( Sheep_Member_Authorisation_Admin::PAGE_SLUG ); ?>
    <?php submit_button( 'Save changes' ); ?>
  </form>

</div>