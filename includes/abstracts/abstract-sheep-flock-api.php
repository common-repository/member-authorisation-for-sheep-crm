<?php
/**
* Abstract base class for Sheep API integration at the flock / system level.
*
* This contains core Sheep methods that wouldn't be called directly.
* Consumers of Sheep should integrate with the Sheep_Flock_API class.
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

abstract class Abstract_Sheep_Flock_API extends Abstract_Sheep_API {

  /**
   * Get the system-level API key
   *
   * @return string|false
   */
  protected static function get_api_key() {
    return isset( self::get_options()['api_key'] ) ? self::get_options()['api_key'] : false;
  }

  /**
   * Get the role to grant
   *
   * @return string|false
   */
  public static function get_grant_role() {
    return isset( self::get_options()['grant_role'] ) ? self::get_options()['grant_role'] : false;
  }

  /**
   * Get the role to revoke
   *
   * @return string|false
   */
  public static function get_revoke_role() {
    return isset( self::get_options()['revoke_role'] ) ? self::get_options()['revoke_role'] : false;
  }

  /**
   * Get the cURL timeout
   *
   * @return integer|false
   */
  public static function get_timeout() {
    return isset( self::get_options()['timeout'] ) ? self::get_options()['timeout'] : false;
  }

  /**
   * Debug setting
   *
   * @return boolean
   */
  protected static function is_debug() {
    return isset( self::get_options()['debug'] ) ? self::get_options()['debug'] : false;
  }

/*** PRIMARY CALLS TO SHEEP ***/

  /**
   * Query Sheep records by email address
   * 
   * @param string $email Email address to query by
   *
   * @return Sheep_Response|WP_Error
   */
  protected static function call_query_by_email( $email ) {
    $url = self::sheep_url() . '/contact/mapreduce/?email__startswithi=' . $email;
    $args = array (
      'headers' => self::sheep_request_header( self::get_api_key() ),
      'timeout' => self::get_timeout(),
    );
    $sheep_response = new Sheep_Response( self::is_debug() );
    $response = $sheep_response->request( $url, $args );

    if ( is_wp_error( $response ) )
      // Request failed so return the WP_Error object.
      return $response;
    
    $sheep_response = self::validate_query_by_email_response( $sheep_response );
    return $sheep_response;
  }


/*** VALIDATORS ***/

  /**
   * Validate the response from Sheep query by email call
   *
   * We only check the values we're concerned about.
   * Anomalies are sent to the error log. If valid or non-critical errors are found
   * the Sheep_Response is returned.
   *
   * If errors are found that we can't recover from, a WP_Error is returned.
   *
   * @param Sheep_Response $response The response to validate
   *
   * @return Sheep_Response|WP_Error 
   */
  private static function validate_query_by_email_response( $response ) {
    $messages = array();
    $fatal = false;

    if ( 200 == $response->http_code ) {
      $payload = $response->payload;

      // Validate required data fields
      if ( property_exists( $payload, 'results' ) && is_array( $payload->results ) ) {
        foreach ( $payload->results as $result ) {
          if ( property_exists( $result, 'value' ) ) {
          }
          else {
            $messages[] = 'Value array not present in result data';
            $fatal = true;
          }
        }
      }
      else {
        $messages[] = 'Results array not present';
        $fatal = true;
      }
    }

    if ( $messages ) {
      self::write_messages_to_error_log( 'Login response', $messages, $response );
    }

    if ( $fatal ) {
      // VQE = validating query by email
      return new WP_Error('system_error', __( '<strong>ERROR</strong>: There is a temporary problem with our systems (code VQE). Please try again later. Sorry for any inconvenience.' ));
    }

    return $response;
  }
}