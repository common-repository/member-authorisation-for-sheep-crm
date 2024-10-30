<?php
/**
 * @package sheep-member-authorisation
 * @version 1.1
 */

/**
 * Plugin Name: Member authorisation for Sheep CRM
 * Description: Grants/revokes a specified WordPress role for users at login based upon their membership status in Sheep CRM. Matching against Sheep CRM records is done by email address.
 * Version: 1.1
 * Author: Tall Projects
 * Author URI: https://www.tallprojects.co.uk/
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}


class Sheep_Member_Authorisation {

  public static function init() {
    // Admin
    require_once( 'admin/class-sheep-member-authorisation-admin.php' ); // Sheep options page in WP admin

    // Sheep API
    require_once( 'includes/abstracts/abstract-sheep-api.php' ); // Base Sheep API implementation
    require_once( 'includes/abstracts/abstract-sheep-flock-api.php' ); // Base Sheep flock API implementation
    require_once( 'includes/class-sheep-flock-api.php' ); // Sheep flock API functions
    require_once( 'includes/class-sheep-response.php' ); // Sheep API call response container
    
    // Primary plugin hooks
    add_action( 'wp_login', array( __CLASS__, 'login' ), 30, 2 );
  }

  /**
   * Log in action; fires after successful authentication
   *
   * @param string $user_login Username
   * @param WP_User $user
   */
  public static function login( $user_login, $user ) {

    // Don't change anything for admin users.
    if ( in_array( 'administrator', $user->roles ) )
      return;

    $grant_role = Sheep_Flock_API::get_grant_role();
    $revoke_role = Sheep_Flock_API::get_revoke_role();

    $is_member = Sheep_Flock_API::has_active_membership( $user->user_email );

    // Something went wrong with the Sheep call so don't change anything - including leaving the user meta value intact
    if ( $is_member instanceof WP_Error ) {
      return;
    }

    if ( $is_member ) {
      if ( ! empty( $grant_role ) )
        $user->add_role( $grant_role );
      if ( ! empty( $revoke_role ) )
        $user->remove_role( $revoke_role );
    }
    else {
      if ( ! empty( $grant_role ) )
        $user->remove_role( $grant_role );
      if ( ! empty( $revoke_role ) )
        $user->add_role( $revoke_role );
    }
  }

  /**
   * Gets the absolute path to this plugin directory.
   * Important that this function is in the root directory of this plugin.
   * @return string
   */
  public static function plugin_path() {
    return untrailingslashit( plugin_dir_path( __FILE__ ) );
  }

  /**
   * Get the plugin url.
   * Important that this function is in the root directory of this plugin.
   * @return string
   */
  public static function plugin_url() {
    return untrailingslashit( plugins_url( '/', __FILE__ ) );
  }
}

Sheep_Member_Authorisation::init();