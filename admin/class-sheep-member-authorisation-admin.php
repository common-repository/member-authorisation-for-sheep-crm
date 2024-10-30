<?php
/**
* Sheep member authorisation admin page for options
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class Sheep_Member_Authorisation_Admin {

  const OPTION    = 'sheep_member_authorisation_options'; // Name of options array stored in DB
  const PAGE_SLUG = 'sheep-member-authorisation'; // Options admin page slug

  private static $options; // Values for this option

  private static $fields = array(
    'flock'         => array(
      'default'     => '',
      'label'       => 'Flock',
      'field'       => 'settings_field_flock',
      'description' => 'Name of the Sheep flock (client instance) to connect to.',
    ),
    'api_key'       => array(
      'default'     => '',
      'label'       => 'Sheep admin user’s API key',
      'field'       => 'settings_field_api_key',
      'description' => 'A personal API key associated with a Sheep admin user with access to the flock. Needed for flock-level transactions such as queries by email address.',
    ),
    'grant_role'          => array(
      'default'     => '',
      'label'       => 'Role for active members',
      'field'       => 'settings_field_grant_role',
      'description' => 'Role granted to active members (and revoked from non-members) on login.',
    ),
    'revoke_role'          => array(
      'default'     => '',
      'label'       => 'Role for non-members',
      'field'       => 'settings_field_revoke_role',
      'description' => 'Role revoked from active members (and granted to non-members) on login. Useful if a "No access" role is used to display specific content to non-members.',
    ),
    'timeout'         => array(
      'default'     => 5,
      'label'       => 'Timeout (in seconds)',
      'field'       => 'settings_field_timeout',
      'description' => 'Maximum length of time to wait for a response from Sheep before returning a timeout error. Must be greater than or equal to 5 seconds.',
    ),
    'debug'         => array(
      'default'     => false,
      'label'       => 'Log API calls',
      'field'       => 'settings_field_debug',
      'description' => 'Debug setting. Tick to log all Sheep API calls and responses to the PHP error log.',
    ),
  );

  public static function init() {
    add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
    add_action( 'admin_init', array( __CLASS__, 'init_settings' ) );
  }

  /**
   * Populate the option with defaults
   */
  private static function set_option_defaults() {
    if ( ! empty( self::$options ) ) {
      return;
    }

    // Set default options
    self::$options = (array) get_option( self::OPTION );
    foreach( self::$fields as $field_name => $field_details ) {
      if ( ! isset( self::$options[ $field_name ] ) ) {
        self::$options[ $field_name ] = $field_details[ 'default' ];
      }
    }
  }

  /**
   * Initialize the WP admin settings page
   */
  public static function add_settings_page() {

    add_options_page(
      'Sheep member authorisation settings',      // HTML title
      'Sheep member authorisation',               // Menu entry label
      'manage_options',                           // Permission required
      self::PAGE_SLUG,                            // Menu entry slug
      array( __CLASS__, 'display_settings_page' ) // Callback for HTML output
    );
  }

  /**
   * Callback function to output outer HTML of settings page
   */
  public static function display_settings_page() {
    include Sheep_Member_Authorisation::plugin_path() . '/admin/templates/admin-settings.php';
  }

  /**
   * Initialise the option group and fields
   */
  public static function init_settings() {
    // Register the option (setting) with WordPress
    register_setting( 'sheep_member_authorisation_option_group', self::OPTION, array( __CLASS__, 'sanitize_options' ) );

    // Add the options page section. We only have one section, but it still needs adding
    add_settings_section( 'sheep_member_authorisation_section', '', '__return_empty_string', self::PAGE_SLUG );

    // Add the fields to the section
    foreach ( self::$fields as $field_name => $field_data ) {
      add_settings_field( $field_name, __( $field_data['label'], 'sheep_member_authorsation' ), array( __CLASS__, $field_data['field'] ), self::PAGE_SLUG, 'sheep_member_authorisation_section' );
    }

    // Set the field values
    self::set_option_defaults();
  }


  /**
   * Callback function to display flock field HTML
   */
  public static function settings_field_flock() {
    $html = '<input type="text" name="' . self::OPTION . '[flock]" id="flock" value="' . esc_attr( self::$options['flock'] ) . '" />';

    if ( ! empty( self::$fields['flock']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['flock']['description'] );
      $html .= ' Find this in your Sheep CRM URL:</p> <pre>https://sheepcrm.com/[flock]/</pre>';

    echo $html;
  }

  /**
   * Callback function to display API key field HTML
   */
  public static function settings_field_api_key() {
    $html = '<input type="text" name="' . self::OPTION . '[api_key]" id="api_key" value="' . esc_attr( self::$options['api_key'] ) . '" class="regular-text" />';

    if ( ! empty( self::$fields['api_key']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['api_key']['description'] );
      $html .= ' <a href="https://intercom.help/sheepcrm/automation/creating-a-sheep-api-key" target="_blank">How to create a Sheep API key</a>.</p>';

    echo $html;
  }

  /**
   * Callback function to display grant role field HTML
   */
  public static function settings_field_grant_role() {
    $roles = get_editable_roles();

    $html = '<select name="' . self::OPTION . '[grant_role]" id="grant_role" class="">';
    foreach ( $roles as $slug => $r ) {
      $html .= '<option value="' . esc_attr( $slug ) . '"' . selected( self::$options['grant_role'], $slug, false ) . '>';
      $html .= esc_html( $r['name'] ) . '</option>';
    }
    $html .= '<option value=""' . selected( self::$options['grant_role'], '', false ) . '>';
    $html .= '(None. No action is taken)</option>';
    $html .= '</select>';

    if ( ! empty( self::$fields['grant_role']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['grant_role']['description'] ) . '</p>';

    echo $html;
  }

  /**
   * Callback function to display revoke role field HTML
   */
  public static function settings_field_revoke_role() {
    $roles = get_editable_roles();

    $html = '<select name="' . self::OPTION . '[revoke_role]" id="revoke_role" class="">';
    foreach ( $roles as $slug => $r ) {
      $html .= '<option value="' . esc_attr( $slug ) . '"' . selected( self::$options['revoke_role'], $slug, false ) . '>';
      $html .= esc_html( $r['name'] ) . '</option>';
    }
    $html .= '<option value=""' . selected( self::$options['revoke_role'], '', false ) . '>';
    $html .= '(None. No action is taken)</option>';
    $html .= '</select>';

    if ( ! empty( self::$fields['revoke_role']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['revoke_role']['description'] ) . '</p>';

    echo $html;
  }

  /**
   * Callback function to display timeout field HTML
   */
  public static function settings_field_timeout() {
    $html = '<input type="number" name="' . self::OPTION . '[timeout]" id="timeout" value="' . esc_attr( self::$options['timeout'] ) . '" step="1" min="' . (integer) self::$fields['timeout']['default'] . '" class="small-text" />';

    if ( ! empty( self::$fields['timeout']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['timeout']['description'] );

    echo $html;
  }

  /**
   * Callback function to display debug field HTML
   */
  public static function settings_field_debug() {
    $html = '<input type="checkbox" name="' . self::OPTION . '[debug]" id="debug" value="yes"' . checked( self::$options['debug'], true, false ) . '" />';
    $html .= '<label for="debug">Enabled</label>';

    if ( ! empty( self::$fields['debug']['description'] ) )
      $html .= '<p class="description">' . esc_html( self::$fields['debug']['description'] ) . '</p>';

    echo $html;
  }

  /**
   * Additional input sanitisation
   *
   * @param array $input
   *
   * @return array Sanitized input
   */
  public static function sanitize_options( $input ) {

    $input['flock'] = strtolower( trim( (string) $input['flock'] ) );
    $input['api_key'] = trim( (string) $input['api_key'] );
    $input['timeout'] = max ( (integer) $input['timeout'], (integer) self::$fields['timeout']['default'] ); // Ensure it's an int and no lower than the default.
    $input['debug'] = 'yes' === (string) $input['debug'] ? true : false ;

    return $input;
  }
}

Sheep_Member_Authorisation_Admin::init();