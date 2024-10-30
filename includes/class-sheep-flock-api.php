<?php
/**
* Class for Sheep API integration at the flock level
* Reference: https://gist.github.com/jwebster/948953201e1f8c248c18e44d3a6462c0
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Sheep_Flock_API extends Abstract_Sheep_Flock_API {

  /**
   * Check if we have potential connection details: API key and flock.
   * This only checks to see if they're present, it doesn't test they work.
   *
   * @return bool True if all set; else false.
   */
  public static function has_connection_details() {
    if ( empty( self::get_flock() ) || empty( self::get_api_key() ) ) {
      error_log( __METHOD__ . " Flock and/or API key not set" );
      return false;
    }
    return true;
  }

  /**
   * Query Sheep to see if a person identified by email address has an active membership or not.
   *
   * @param string $email Email address
   *
   * @return boolean|WP_Error
   */
  public static function has_active_membership( $email ) {
    if ( ! self::has_connection_details() ) {
      return new WP_Error();
    }

    $response = self::call_query_by_email( $email );

    if ( is_wp_error( $response ) ) {
      error_log( __METHOD__ . " Sheep call to query by email returned an error\n" . print_r( $response, true ) );
      return $response;
    }

    // Get here, we know we've got a valid response from Sheep
    $active_membership = false;
    foreach ( $response->payload->results as $result ) {
      if ( property_exists( $result->value, 'active_memberships' )
        && is_array( $result->value->active_memberships )
        && count( $result->value->active_memberships ) > 0 ) {
        $active_membership = true;
        break;
      }
    }

    return $active_membership;
  }
}
