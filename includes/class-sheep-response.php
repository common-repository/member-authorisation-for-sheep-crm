<?php
/**
* Response data from a Sheep API call
*/
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Sheep_Response {

  public $http_code;
  public $payload;
  public $session_expiry;

  private $debug = false;

  public function __construct( $debug = false ) {
    $this->debug = (bool) $debug;
  }
  
  /**
   * Make an HTTP request to Sheep and populate this Sheep_Response object.
   * Calls are made using WP_Http;
   *
   * @param string $url URL to call
   * @param array $args Arguments to the HTTP request. See WP_Http()
   *
   * @return true|WP_Error 
   */
  public function request( $url, $args ) {
    // Increase the HTTP timeout from the WP default 5 seconds to 15, unless a call explicity sets it
    if ( ! isset( $args['timeout'] ) ) {
      $args['timeout'] = 15;
    }

    $wp_http = new WP_Http();
    $response = $wp_http->request( $url, $args );

    if ( $this->debug ) {
      error_log(__METHOD__ . ": URL:\n" . print_r( $url, true ) );
      error_log(__METHOD__ . ": Args:\n" . print_r( $args, true ) );
      error_log(__METHOD__ . ": Response:\n" . print_r( $response, true ) );
    }

    if ( is_wp_error( $response ) ) {
      return $response;
    }

    $headers = $response['headers']->getAll();
    if ( array_key_exists( 'x-sheepcrm-session-expiry', $headers ) ) {
      $this->session_expiry = $headers['x-sheepcrm-session-expiry'];
    }

    $this->http_code = $response['response']['code'];
    $this->payload = json_decode( $response['body'] );

    if ( is_null( $this->payload ) ) {
      // There was an error parsing the JSON response
      // SBR = Sheep bad response
      return new WP_Error( 'system_error', __( '<strong>ERROR</strong>: There is a temporary problem with our systems (code SBR). Please try again later. Sorry for any inconvenience.' ) );
    }

    return true;
  }

}
