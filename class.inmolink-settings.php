<?php

class InmolinkSettings {
  /**
   * Holds the values to be used in the fields callbacks
   */
  private $options;

  /**
   * Start up
   */
  public function __construct() {
      add_action( 'admin_menu', array( $this, 'inmolink_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  /**
   * Add options page
   */
  public function inmolink_plugin_page() {
      // This page will be under "Settings"
      add_options_page(
          'Inmolink Settings',
          'Inmolink API Settings',
          'manage_options',
          'inmolink',
          array( $this, 'inmolink_admin_page' )
      );
  }

  /**
   * Options page callback
   */
  public function inmolink_admin_page() {
      // Set class property
      $this->options = get_option( 'inmolink_option_name' );
      ?>
      <div class="wrap">
          <h1>Inmolink API Settings</h1>
          <form method="post" action="options.php">
          <?php
              // This prints out all hidden setting fields
              settings_fields( 'inmolink_option_group' );
              do_settings_sections( 'inmolink-setting-admin' );
              submit_button();
          ?>
          </form>
      </div>
      <?php
      /*
      * Import forms
      */
        if(isset($_POST['importlocation'])){
             inmolink_import_location();
        }
        if(isset($_POST['importtype'])){
             inmolink_import_type();
        }
        if(isset($_POST['importfeatures'])){
             inmolink_import_features();
        }
      ?>
      <form method="post">
        <h2>Import Location</h2>
        <input type="submit" name="importlocation" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>

      <form method="post">
        <h2>Import Types</h2>
        <input type="submit" name="importtype" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>

      <form method="post">
        <h2>Import Features</h2>
        <input type="submit" name="importfeatures" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>
      <?php
  }

  /**
   * Register and add settings
   */
  public function page_init() {
      register_setting(
          'inmolink_option_group', // Option group
          'inmolink_option_name', // Option name
          array( $this, 'sanitize' ) // Sanitize
      );

      add_settings_section(
          'inmolink_setting_section', // ID
          'Inmolink API Settings', // Title
          array( $this, 'print_section_info' ), // Callback
          'inmolink-setting-admin' // Page
      );

      add_settings_field(
          'api_access_token',
          'API Access Token',
          array( $this, 'api_access_token_callback' ),
          'inmolink-setting-admin', // Page
          'inmolink_setting_section' // Section
      );

      add_settings_field(
          'api_base_url',
          'API Base URL',
          array( $this, 'api_baseurl_callback' ),
          'inmolink-setting-admin', // Page
          'inmolink_setting_section' // Section
      );

      add_settings_field(
        'google_api_key',
        'Google API Key',
        array( $this, 'google_api_key_callback' ),
        'inmolink-setting-admin', // Page
        'inmolink_setting_section' // Section
      );

      add_settings_field(
        'single_property_slug',
        'Property page slug:',
        array( $this, 'single_property_slug_callback' ),
        'inmolink-setting-admin', // Page
        'inmolink_setting_section' // Section
      );


  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input ) {
      $new_input = array();

      if( isset( $input['api_access_token'] ) )
          $new_input['api_access_token'] = sanitize_text_field( $input['api_access_token'] );

      if( isset( $input['api_base_url'] ) )
          $new_input['api_base_url'] = sanitize_text_field( $input['api_base_url'] );

      if( isset( $input['google_api_key'] ) )
          $new_input['google_api_key'] = sanitize_text_field( $input['google_api_key'] );

      if( isset( $input['single_property_slug'] ) )
          $new_input['single_property_slug'] = $input['single_property_slug'];

      return $new_input;
  }

  /**
   * Print the Section text
   */
  public function print_section_info() {
      print 'Enter API settings below:';
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function api_baseurl_callback() {
      printf(
          '<input type="text" id="api_base_url" name="inmolink_option_name[api_base_url]" value="%s" />',
          isset( $this->options['api_base_url'] ) ? esc_attr( $this->options['api_base_url']) : ''
      );
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function api_access_token_callback() {
      printf(
          '<input type="text" id="api_access_token" name="inmolink_option_name[api_access_token]" value="%s" />',
          isset( $this->options['api_access_token'] ) ? esc_attr( $this->options['api_access_token']) : ''
      );
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function single_property_slug_callback() {
    $languages = inmolink_get_languages();
    foreach($languages as $k => $language){
      printf(
          '<label>/%2$s</label>&nbsp;<input type="text" id="single_property_slug" name="inmolink_option_name[single_property_slug][%1$s]" value="%3$s" /><br>',
          $k,
          $language['dir'] ,
          $language['single_property_slug']
      );
    }
    echo '<span class="setting-description">';
    echo '<b>Note:</b> save permalinks after changing any of the above property page slugs.';
    echo '</span>';

  }

  /**
   * Get the settings option array and print one of its values
   */
  public function google_api_key_callback() {
      printf(
          '<input type="text" id="google_api_key" name="inmolink_option_name[google_api_key]" value="%s" />',
          isset( $this->options['google_api_key'] ) ? esc_attr( $this->options['google_api_key']) : ''
      );
  }
}
