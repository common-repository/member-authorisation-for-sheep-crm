<?php
/**
* Abstract base class for Sheep API integration
*
* This contains core Sheep methods that wouldn't be called directly.
* Consumers of Sheep should integrate with the Sheep_User_API or Sheep_Flock_API classes.
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

abstract class Abstract_Sheep_API {

  const API_URL = 'https://api.sheepcrm.com/api/v1/';

  /**
   * Settings.  These are managend as a WP option
   * @see admin/class-sheep-authorisation-admin.php
   */
  private static $options = array();

  protected static function get_options() {
    if ( ! self::$options ) {
      self::$options = get_option( Sheep_Member_Authorisation_Admin::OPTION, array() );
    }
    return self::$options;    
  }

  /**
   * Get the flock string
   *
   * @return string|false
   */
  protected static function get_flock() {
    return isset( self::get_options()['flock'] ) ? self::get_options()['flock'] : false;
  }

/*** SUPPORTING HELPER METHODS ***/

  /**
   * Build the Sheep HTTP authorisation request header for use in subsequent HTTP calls
   *
   * @param string $token
   * @return string
   */
  protected static function sheep_request_header( $token ) {
    return "Authorization: Bearer " . (string) $token;
  }

  /**
   * Build the Sheep access URL for the current flock
   *
   * @return string
   */
  protected static function sheep_url() {
    return self::API_URL . self::get_flock();
  }

  /**
   * Sheep provides unique resource identifiers in the format
   * /{flock}/{resource_type}/{unique ID} - but we sometimes only need a specific part
   * of this URI. (e.g. when writing back a value, Sheep only wants the unique ID)
   *
   * @param string $uri Sheep resource URI in /{flock}/{resource_type}/{unique ID} format
   * @param string $part Required part of the URI. Valid values are 'flock', 'resource_type', 'uid' (default)
   *
   * @return false|string UID component or false if not found.
   */
  public static function get_from_uri( $uri, $part = 'uid' ) {
    $part_values = array(
      'flock' => 1,
      'resource_type' => 2,
      'uid' => 3,
      );

    if ( ! array_key_exists( $part, $part_values ) ) {
      error_log( __METHOD__.': Invalid part key provided: '.print_r( $part, true ) );
      return false;
    }

    $part_key = $part_values[ $part ];
    $uri_parts = explode( '/', $uri );
    if ( ! array_key_exists( $part_key, $uri_parts ) || empty( $uri_parts[ $part_key ] ) ) {
      return false;
    }

    return $uri_parts[ $part_key ];
  }

  /**
   * Change a PHP array of key => value pairs to a list of Sheep tuples
   * e.g. ['home'] => "123" to [0] => "123;home"
   *
   * Note that numeric (integer) keys in the input array are not carried over
   *
   * @param array $input PHP array of key => value pairs
   *
   * @return array 
   */
  public static function tuple_array_contract($input) {
    $new_array = array();

    if (!is_array($input))
      return $new_array;

    foreach ($input as $key => $value) {
      if (is_integer($key))
        $new_array[] = $value;
      else
        $new_array[] = $value.';'.$key;
      }

    return $new_array;
  }

  /**
   * Change an array of Sheep tuple values into a PHP key => value array
   * e.g. [0] => "123;home" to ['home'] => "123"
   *
   * @param array $input array of Sheep data tuples
   *
   * @return array where the second component of the tuple is the key
   */
  public static function tuple_array_expand($input) {
    $new_array = array();

    if (!is_array($input))
      return $new_array;

    foreach ($input as $value) {
      $tuple = explode(';', $value, 2);
      if (array_key_exists(1, $tuple)) // There is a name
        $new_array[$tuple[1]] = $tuple[0];
      else
        $new_array[] = $tuple[0];
      }

    return $new_array;
  }

  /**
   * Helper method to write error messages to the error log
   *
   * @param string $heading Heading printed before messages in error log for identification
   * @param array $messages Array of message strings
   * @param Sheep_Response $response Full response object to print_r in the logs
   */
  protected static function write_messages_to_error_log( $heading = '', $messages = array(), $response ) {
    if ( ! is_array( $messages ) ) {
      error_log( __METHOD__ . ': messages passed is not an array.' );
      return;
    }

    array_unshift( $messages, '=== Sheep_Response_Validator: ' . $heading . ' ===' );
    $messages[] = print_r( $response, true ); // Output the response object too.
    $message = implode( "\n", $messages );
    error_log( $message );
  }
}