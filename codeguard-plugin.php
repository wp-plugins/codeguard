<?php
/*
   Plugin Name: CodeGuard
   Plugin URI: https://codeguard.com/wordpress
   Author: The CodeGuard Team
   Description: Get a time machine for your website!  CodeGuard will monitor your site for changes.  When a change is detected, we will alert you and take a new backup of your database and site content.
   Version: 0.38
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by    
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of    
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 */

if ( ! defined('ABSPATH') ) { 
  die('Please do not load this file directly.');
}

class CodeGuard_WP_Plugin {
  protected $_plugin_api_key = 'bb459c1b2b348e4a3d82d92270624e14';
  protected $_plugin_oauth_token = 'yVkLFN32tULbyVS0iJu7fKMrSJszhNMEoujj1qPs';
  protected $_plugin_oauth_secret = 'HmaMXTQog5r9xOJAiakIcXaGKXFDjp89SionJrlA';

  private $plugin_name = "codeguard";
  private $cg_client = null;

  const HTTP_REQUEST_TIMEOUT = 45;
  const PLUGIN_VERSION = 0.38;

  function __construct() {
    // Check for PHP > 5.2
    if (version_compare( PHP_VERSION, '5.2.0') >= 0) {
      // Add WP Actions
      add_action('init', array($this, 'init_scripts'), 20);
      add_action('init', array($this, 'init_styles'), 20);
      add_action('admin_menu', array($this,'init_admin_menu'));

      // Setup CodeGuard Client
      if ( !isset( $this->cg_client ) ) {
        require_once( dirname( __FILE__ ) . '/class.codeguard-client.php' );
        require_once( dirname( __FILE__ ) . '/class.codeguard-exporter.php' );
        require_once( dirname( __FILE__ ) . '/class.codeguard-importer.php' );
        require_once( dirname( __FILE__ ) . '/oAuthSimple.php' );

        // Test requirements for client and exporter
        if( !CodeGuard_Exporter::test_requirements() 
          || !CodeGuard_Importer::test_requirements()
          || !CodeGuard_WP_Plugin::test_requirements()) {
            add_action('admin_notices', array($this,'missing_requirements_error_notice'));
            return; 
          }

        $this->cg_client = new CodeGuard_Client($this->_plugin_api_key);
      }

      // Check for multisite installs
      if ($this->check_multi_site()) {
        $this->set_ui_error_message('multisite_not_supported', false);
      } 

      // Route codeguard-action requests
      if (! empty( $_REQUEST['codeguard-action'] ) ) {
        $this->route_codeguard_request( $_REQUEST );
      }

      // Route plugin form submissions
      if (! empty($_POST['codeguard_initiate_new_backup']) ) {
        $this->configure_codeguard_client();
        $this->create_codeguard_website_backup_request();
      } else if (! empty($_POST["codeguard-plugin-reset"])) {
        CodeGuard_WP_Plugin::delete_codeguard_api_tokens();
      } else if ( ! empty( $_POST['codeguard_signup_email'] ) ) { 
        $create_user_result = $this->create_codeguard_user($_POST['codeguard_signup_email']);
        if($create_user_result) {
          $this->configure_codeguard_client();
          $this->has_valid_codeguard_tokens();
          $this->create_codeguard_website_container();
        } else {
          $this->set_ui_error_message('user_duplicate_email');
        }
      } else if ( ! (empty($_POST['codeguard-tokens-key']) && empty($_POST['codeguard-tokens-secret']) ) ) {
        $access_token = $_POST['codeguard-tokens-key'];
        $access_secret = $_POST['codeguard-tokens-secret'];
        $this->set_codeguard_api_credentials( $access_token, $access_secret );
        $this->configure_codeguard_client();
        if ($this->has_valid_codeguard_tokens()) {
          $this->create_codeguard_website_container();
        }
      } else if ( !empty($_POST['codeguard-tokens-combined'] ) ) {
        $combined_token = $_POST['codeguard-tokens-combined'];

        if ( strlen( $combined_token ) == 80 ) {
          $access_token = substr( $combined_token, 0, 40 );
          $access_secret = substr( $combined_token, 40, 40 );
          $this->set_codeguard_api_credentials( $access_token, $access_secret );
          $this->configure_codeguard_client();
          if ($this->has_valid_codeguard_tokens()) {
            $this->create_codeguard_website_container();
          }
        } else {
          $this->set_ui_error_message('invalid_credentials');
        }
      } else if (! (empty($_POST['codeguard-add-website']) ) ) {
        $this->configure_codeguard_client();
        $this->create_codeguard_website_container();
      }

      // Display signup form if not configured
      if( !($this->is_codeguard_configured()) ) {
        // Don't display signup form on the admin-menu page
        if ( !$this->on_cg_page() ) {
          add_action('admin_notices', array($this, 'signup_form_notice'));
        }
      } else {
        // Configure CodeGuard Client
        $this->configure_codeguard_client();

        // Check for valid credentials
        if( ! ($this->has_valid_codeguard_tokens()) ) {
          $this->set_ui_error_message('invalid_credentials');
        }

        // Check for valid website id
        if( !($this->has_website_id() ) ) {
          if ( !$this->on_cg_page() ) {
            $this->set_ui_error_message('missing_website_id', false);
          }
        } else {
          // Only show CodeGuard WP dashboard if there is a valid user and site
          // add_action('wp_dashboard_setup', array( $this, 'codeguard_dashboard_widgets') );
        }
      }
    } else {
      // Display PHP version error notice
      add_action('admin_notices', array($this,'php_version_error_notice'));
    }
  }

  static function test_requirements() {
    return function_exists('openssl_pkey_new') && function_exists('openssl_pkey_export');
  }

  // Install hook
  static function install() {
    CodeGuard_WP_Plugin::generate_plugin_keys();
    return;
  }

  // Uninstall hook
  static function uninstall() {
    return;
  }

  // Javascript init
  function init_scripts() {
    if(file_exists(dirname(__FILE__) . '/scripts/script.js') && is_admin()) {
      if(function_exists('plugins_url')) {
        wp_enqueue_script($this->plugin_name . '-script', plugins_url('/scripts/script.js', __FILE__), array('jquery'), '1.0', true);
      } else {
        wp_enqueue_script($this->plugin_name . '-script', WP_PLUGIN_URL . '/' . $this->plugin_name . '/scripts/script.js', array('jquery'), '1.0', true);
      }
    }
  }

  // CSS init
  function init_styles() {
    if(file_exists(dirname(__FILE__) . '/styles/style.css') && is_admin()) {
      if(function_exists('plugins_url')) {
        wp_enqueue_style($this->plugin_name . '-stylesheet', plugins_url('/styles/style.css', __FILE__), array(), '1.0', 'all');
      } else {
        wp_enqueue_style($this->plugin_name . '-stylesheet', WP_PLUGIN_URL . '/' . $this->plugin_name . '/styles/style.css', array(), '1.0', 'all');
      }
    }
  }

  // Admin menu button init
  function init_admin_menu() {
    if ( !current_user_can( 'manage_options' ) )
      return;
    $admin_menu_image_link = $this->codeguard_plugin_image_url() . 'cgshield.png';
    add_menu_page('CodeGuard Admin Menu', 'CodeGuard', 'publish_posts', $this->plugin_name . '-admin-menu', array($this,'main_menu_page'), $admin_menu_image_link);
    add_submenu_page($this->plugin_name . '-admin-menu', 'CodeGuard Settings', 'Settings', 'manage_options', $this->plugin_name . '-settings-menu', array($this, 'settings_page'));
  }

  private function decrypt_parameter_string($source_text, $cg_exporter) {
    $private_key = $this->get_codeguard_plugin_private_key();
    if ($private_key) {
      return $cg_exporter->get_verified_and_decrypt_chunked_rsa($source_text, $private_key);
    } else {
      throw new Exception('Missing plugin private key.');
    }
  } // end get_verified_string

  private function get_verified_request_value($parameter_name, $cg_exporter) {
    global $_REQUEST;
    return $this->decrypt_parameter_string($_REQUEST[$parameter_name], $cg_exporter);
  } // end get_verified_request_value

  private function get_parameters_hash($cg_exporter) {
    $parameters_json = $this->get_verified_request_value('what', $cg_exporter);
    unset($to_return);
    if (false != $parameters_json) {

      $parameters_json = trim($parameters_json);

      if("" != $parameters_json 
        && ("[" == $parameters_json[0] || "{" == $parameters_json[0]) 
      ) 
      {
        $to_return = json_decode($parameters_json);
      } // end if
    } // end there is anything at all

    //echo "JSON Returning - " . print_r($to_return, true);
    return $to_return;
  } // end get_parameters_hash

  // Route incoming API requests
  public function route_codeguard_request( $request )
  {
    $this->cg_client->setup_client_response($request);
    $cg_exporter = new CodeGuard_Exporter();
    $cg_importer = new CodeGuard_Importer();

    // Retrieve the action request and VERIFY that it was signed by CodeGuard's Secret RSA Key
    $unverified_action = base64_decode($_REQUEST['action']);

    try {
      $verified_request = $cg_exporter->get_verified_string($unverified_action);
    } catch (Exception $e) {
      $verified_request = false;
      $verification_error = $e->getMessage();
    }

    $response["action"] = $unverified_action;

    $standard_response = true;
    $data = false;

    if(false == $verified_request) {
      if(isset($verification_error)) {	
        $this->send_plugin_error($verification_error);
      } else if ( !$this->has_valid_codeguard_tokens() ) {
        $this->send_plugin_error("User credentials not stored.");
      } else {
        $this->send_plugin_error("Unable to verify request.");
      }
      return;
    } else {

      $params = Array();
      try {
        if(isset($_REQUEST['what'])) {
          $params = $this->get_parameters_hash($cg_exporter);
        }
      } catch (Exception $e) {
        $this->send_plugin_error("Unable to decrypt the parameter string.");
        return;
      }

      // Process the requested web service call
      switch ($verified_request) {
      case "get_tables":
        $data = $cg_exporter->get_tables_list();
        break;
      case "phpinfo":
        $data = $cg_exporter->phpinfo_data();
        break;
      case "get_wp_statistics":
        $data = $cg_exporter->get_wp_statistics();
        break;
      case "get_table_record_count":
        $data = Array("count" => $cg_exporter->get_table_record_count($params->table_name));
        break;
      case "echotest":
        $data = Array("echo" => $params[0]);
        break;
      case "execute_sql_file":
        $data = $cg_importer->execute_sql_file($params->file_path, $params->expected_sha1_hash);
        break;
      case "change_file_permissions":
        $data = $cg_importer->change_file_permissions($params->file_path, $params->permissions);
        break;
      case "get_temp_file_name":
        $data = $cg_importer->get_temp_file_name($params->prefix);
        break;
      case "get_file_attributes":
        if (file_exists($params->file_path)) {
          $data = $cg_exporter->get_file_attributes_with_sha1($params->file_path);
        }
        break;
      case "sha1_file":
        if (file_exists($params->file_path)) {
          $data = sha1_file($params->file_path);
        }
        break;
      case "write_file_data":
        $data = "Hi";
        $data = $cg_importer->write_file_data($params->file_path, $params->file_data, $params->append);
        break;
      case "delete_file":
        $data = $cg_importer->delete_file($params->file_path);
        break;
      case "copy_file":
        $data = $cg_importer->copy_file($params->old_name, $params->new_name);
        break;
      case "create_directory":
        $data = $cg_importer->create_directory($params->path, $params->permissions);
        break;
      case "delete_directory":
        $data = $cg_importer->delete_directory($params->path);
        break;
      case "change_directory_permissions":
        $data = $cg_importer->change_directory_permissions($params->path, $params->permissions);
        break;
      case "get_table_data":
        // The data request is a little different, we will assign the success and data items here
        $standard_response = false;
        $tbl_stats = $cg_exporter->get_table_data($params->table_name, $params->limit_start, $params->limit_end);
        $response["success"] = (false != $tbl_stats);
        $response["data"] = $tbl_stats;
        break;
      case "get_file_list":
        // The data request is a little different, we will assign the success and data items here
        $standard_response = false;
        $verified_bundle_size = $params[0];

        if( isset($verified_bundle_size) && intval($verified_bundle_size) > 0 ) {
          $file_list = $cg_exporter->get_file_list_stream(intval($verified_bundle_size));
        } else {
          $file_list = $cg_exporter->get_file_list_stream();
        }

        $response["success"] = (is_array($file_list) && $file_list['error'] == false);
        $response["data"] = $file_list;
        break;
      case "get_file_data":
        // The data request is a little different, we will assign the success and data items here
        $standard_response = false;
        $verified_file_name = $params[0];
        if (false == $verified_file_name) {
          header('HTTP/1.1 403 Forbidden');
          return;
        } else {
          $file_stats = $cg_exporter->get_file_data_in_chunks($verified_file_name);
          $response["success"] = (is_array($file_stats) && $file_stats['error'] == false);
          $response["data"] = $file_stats;
        } // end if verified
        break;
      default:
        $data = Array("public_key" => $this->get_codeguard_plugin_public_key_or_make_it());
      } // end switch / possible routes

    } // end route_codeguard_request

    if($standard_response && isset($data)) {
      $response["version"] = CodeGuard_WP_Plugin::PLUGIN_VERSION;
      $response["success"] = (false != $data);
      $response["data"] = $data;
    } // end if standard response

    echo "\n" . $cg_exporter->respond_with($response) . "\n";
    return;
  } // end route_codeguard_request

  /*
   * Begin CodeGuard API methods
   */

  function configure_codeguard_client() {
    list( $user_token, $user_secret ) = $this->get_codeguard_api_tokens();
    $this->cg_client->set_oauth_tokens($this->_plugin_oauth_token, $this->_plugin_oauth_secret, $user_token, $user_secret);
  }

  function has_website_id() {
    $id = $this->get_codeguard_website_id();
    return (!empty($id));
  }

  function has_valid_codeguard_tokens($revalidate = false) {
    if( $this->is_codeguard_configured() ) {
      if( $this->is_codeguard_token_valid() == true && !($revalidate)) {
        return true;
      }

      try {
        $url = $this->cg_client->list_user_websites_url();
        $resp = wp_remote_get( 
          $url,
          array(
            'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
            'headers' => array(
              'Accept' => 'application/json',
              'Content-Type' => 'application/json'
            ),
          )
        );

        if ( is_wp_error( $resp ) ) {
          return false;
        }

        if ( ! empty( $resp['response'] ) && 
          isset( $resp['response']['code'] ) && 
          '200' == $resp['response']['code'] ) {
            $this->set_codeguard_api_token_validity(true);
            return true;
          } else {
            return false;
          }
      } catch( Exception $e) {
        return false;
      }
    } else {
      return false;
    }
  }

  function create_codeguard_website_backup_request() {
    $id = $this->get_codeguard_website_id();
    $signed_website_backup_request_url = $this->cg_client->create_website_backup_request_url($id);

    $resp = wp_remote_post( 
      $signed_website_backup_request_url,
      array(
        'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
        'headers' => array(
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        )
      )
    );

    if( is_wp_error( $resp ) ) {
      $this->set_ui_error_message('failed_to_request_backup');
      return false;
    } else {
      if ( ! empty( $resp['response'] ) &&
        isset( $resp['response']['code'] ) &&
        '200' == $resp['response']['code'] ) {
          $this->set_ui_error_message('backup_request_success');
          return true;
        } else {
          $this->set_ui_error_message('failed_to_request_backup');
          return false;
        }
    }
  }

  function create_codeguard_website_container() {
    $signed_website_request_url = $this->cg_client->create_website_container_url();

    $site_url = site_url();
    $home_url = home_url();

    $args = array(
      'url' => $site_url,
      'hostname' => $home_url,
      'provider' => 'WordPress',
    );

    $resp = wp_remote_post( 
      $signed_website_request_url,
      array(
        'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
        'headers' => array(
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ),
        'body' => json_encode( $args ),
      )
    );

    if( is_wp_error( $resp ) ) {
      $this->set_ui_error_message('connection_error');
      return null;
    } else {
      $values = $this->_get_json_resp_values( $resp['body'] );
      if( isset($values->word_press_database_backup->id) && 
        $this->set_codeguard_website_id($values->word_press_database_backup->id)) {
          return true;
        } else {
          if( isset($values->error->message ) ) {
            $this->set_error_message_from_api($values->error->message);
          } else {
            $this->set_ui_error_message('connection_error');
          }
          return false;
        }
    }
  }

  function create_codeguard_user($email = null, $name = null) {
    if ( ! empty( $email ) ) {
      $url = $this->cg_client->create_user_url();

      if ( empty( $name ) ) {
        $arr = explode( '@', $email );
        $name = $arr[0];
      }

      $args = array(
        'user' => array(
          'name' => $name,
          'email' => $email,
        ),
      );

      $resp = wp_remote_post( 
        $url,
        array(
          'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
          'headers' => array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
          ),
          'body' => json_encode( $args ),
        )
      );

      if ( is_wp_error( $resp ) ) {
        return false;
      }

      if ( ! empty( $resp['response'] ) ) {
        if ( isset( $resp['response']['code'] ) ) {
          // 201: created
          if ( '201' == $resp['response']['code'] ) {
            $values = $this->_get_json_resp_values( $resp['body'] );
            if ( ! empty( $values->user ) ) {
              $access_token = empty( $values->user->access_token ) ? '' : $values->user->access_token;
              $access_secret = empty( $values->user->access_secret ) ? '' : $values->user->access_secret;
              $this->set_codeguard_api_credentials( $access_token, $access_secret );
            }
            return true;
          }
        }
        return false;
      }
    }
  }
  /*
   * End CodeGuard API methods
   */


  /**
   * JSON Helpers
   */
  public function _get_json_resp_values( $text = '' )
  {
    $text = trim( $text );
    try {
      $args = json_decode( $text );
    } catch( Exception $e ) {
      $args = array();
    }

    return $args;
  }

  /**
   * Getters and Setters for CodeGuard OAuth credentials.
   *          
   */
  public function is_codeguard_configured() {
    $token = $this->get_codeguard_api_access_token();
    $secret = $this->get_codeguard_api_access_secret();
    $result = ! ( empty($token) && empty($secret) );
    return $result;
  }

  public function is_codeguard_token_valid() {
    return get_option( 'codeguard_api_token_valid' );
  }

  public function set_codeguard_api_token_validity($value) {
    return update_option( 'codeguard_api_token_valid', $value);
  }

  public function set_codeguard_website_id($value) {
    return update_option( 'codeguard_website_id', $value);
  }

  public function get_codeguard_website_id() {
    return get_option( 'codeguard_website_id' );
  }

  public function set_codeguard_api_credentials( $token = '', $secret = '' )
  {
    return update_option( 'codeguard_api_access_token', $token ) && 
      update_option( 'codeguard_api_access_secret', $secret );
  }

  public function get_codeguard_api_access_token()
  {
    return get_option( 'codeguard_api_access_token' );
  }

  public function get_codeguard_api_access_secret()
  {
    return get_option( 'codeguard_api_access_secret' );
  }

  public function get_codeguard_api_tokens()
  {
    return array( $this->get_codeguard_api_access_token(),
      $this->get_codeguard_api_access_secret()
    );
  }

  static function set_codeguard_plugin_keys($public_key, $private_key) {
    return update_option( 'codeguard_plugin_public_key', $public_key) && 
      update_option( 'codeguard_plugin_private_key', $private_key);

  }

  public function get_codeguard_plugin_private_key() {
    return get_option( 'codeguard_plugin_private_key' );
  }

  public function get_codeguard_plugin_public_key() {
    return get_option( 'codeguard_plugin_public_key' );
  }

  static function generate_plugin_keys() {
    // Generate a RSA public and private keypair
    $key_pairs = openssl_pkey_new(array('private_key_bits' => 2048));

    if ($key_pairs) {
      // Retrieve the private key into 
      openssl_pkey_export($key_pairs, $private_key_contents);

      // Get public key
      $key_details = openssl_pkey_get_details($key_pairs);
      $public_key_contents = $key_details["key"];

      CodeGuard_WP_Plugin::set_codeguard_plugin_keys($public_key_contents, $private_key_contents);
    } else {
      throw new Exception('Plugin was unable to generate an RSA security key that is needed for secure communication with the CodeGuard.com backup service.');
    }
    return;
  } // end generate_plugin_keys

  public function get_codeguard_plugin_public_key_or_make_it() {
    $key_content = $this->get_codeguard_plugin_public_key();
    if(false == $key_content) {
      $this->generate_plugin_keys();
      $key_content = $this->get_codeguard_plugin_public_key();
    }
    return $key_content;
  } // end get_codeguard_plugin_public_key_or_make_it

  static function delete_codeguard_api_tokens() {
    return delete_option( 'codeguard_api_access_token' ) &&
      delete_option( 'codeguard_api_access_secret' ) &&
      delete_option( 'codeguard_api_token_valid' ) &&
      delete_option( 'codeguard_website_id' ) &&
      delete_option( 'codeguard_plugin_private_key' ) &&
      delete_option( 'codeguard_plugin_public_key' );
  }


  /**
   * This function will be executed when the admin sub page is to be loaded
   * @return void
   */
  function sub_menu_page() {
    // Include the HTML from a separate file to keep the plugin class clean
    require "pages/admin_options_page.php";
  }

  /**
   * This function will be executed when the admin sub page is to be loaded
   * @return void
   */
  function signup_form_notice() {
    require "pages/inline_signup_form.php";
  }

  function get_suggested_signup_email()
  {
    $email = '';
    $current_user = wp_get_current_user();
    if ( ! empty( $current_user->user_email ) ) {
      $email = $current_user->user_email; 
    }

    if ( empty( $email ) ) {
      $email = get_option( 'admin_email' );
    }

    return $email;
  }

  function settings_page() {
    if ( !current_user_can( 'manage_options' ) )
      return;
    require "pages/settings.php";
  }

  function main_menu_page() {
    if ( !current_user_can( 'manage_options' ) )
      return;
    require "pages/admin_main.php";
  }

  function php_version_error_notice()
  {
    require "pages/php_version_error.php";
  }

  function missing_requirements_error_notice()
  {
    require "pages/missing_requirements_error.php";
  }

  function codeguard_dashboard_widgets() {
    if ( current_user_can( 'manage_options' ) ) {
      global $wp_meta_boxes;
      wp_add_dashboard_widget('codeguard_dashboard_widget', 'CodeGuard Status', array( $this, 'codeguard_dashboard_widget') );
    }
  }

  function get_codeguard_wordpress_stats() {
    $id = $this->get_codeguard_website_id();
    $request_url = $this->cg_client->get_wordpress_stats_url($id);

    $response = wp_remote_get( 
      $request_url, 
      array(
        'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
        'headers' => array(
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ),
      )
    );
    if( is_wp_error( $response ) ) {
      return null;
    } else {
      $values = $this->_get_json_resp_values( $response['body'] );
    }

    return $values;
  }

  function get_codeguard_login_url() {
    $request_url = $this->cg_client->get_login_url();

    $response = wp_remote_get( 
      $request_url,
      array(
        'timeout' => CodeGuard_WP_Plugin::HTTP_REQUEST_TIMEOUT,
        'headers' => array(
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ),
      )
    );

    $login_url = "";
    if( is_wp_error( $response ) ) {
      return null;
    } else {

      $values = $this->_get_json_resp_values( $response['body'] );
      $login_url = $values->perishable_login_url;
    }

    return $login_url;
  }

  // Convenience method for detecting if the user is viewing the codeguard-settings-menu
  function on_settings_page() {
    return !( empty($_REQUEST["page"]) || $_REQUEST["page"] !== "codeguard-settings-menu" ); 
  }

  // Convenience method for detecting if the user is viewing the codeguard-admin-menu
  function on_admin_page() {
    return !( empty($_REQUEST["page"]) || $_REQUEST["page"] !== "codeguard-admin-menu" ); 
  }

  // Convenience method for detecting if on any CodeGuard pages
  function on_cg_page() {
    return ($this->on_settings_page() || $this->on_admin_page());
  }

  // Convenience method for checking if this is a multisite install
  function check_multi_site() {
    try {
      return is_multisite();
    } catch(Exception $e) {
      return false;
    }
  }

  public function codeguard_plugin_image_url() {
    $plugin_url = plugin_dir_url( __FILE__ );
    $image_url = $plugin_url . 'images/';
    return $image_url;
  }

  function codeguard_dashboard_widget() { 
    $login_url = $this->get_codeguard_login_url();
    $site_stats = $this->get_codeguard_wordpress_stats();

    if(!isset($login_url) || !isset($site_stats) ) {
      if(!isset($login_url)) {
        $error_message = "We were unable to locate your CodeGuard user information. If you need help, check out the <a href='http://support.codeguard.com/'>CodeGuard Support Center</a>.<br /><br />If you don't find any answers there, you could try the <a id='codeguard-plugin-reset' href='javascript:;'>CodeGuard Plugin Reset</a> as a last resort.";
      } else if(!isset($site_status)) {
        $error_message = "We were unable to locate your backup. Did you remove your website from CodeGuard?  If you need help, check out <a href='http://support.codeguard.com/'>CodeGuard Support</a>.<br /><br />If you don't find any answers there, you could try the <a id='codeguard-plugin-reset' href='javascript:;'>CodeGuard Plugin Reset</a> as a last resort.";
      } else {
        $error_message = "Unable to reach the CodeGuard service, please try again later. If this problem persists, please contact support@codeguard.com. <br /><br />If you don't find any answers there, you could try the <a id='codeguard-plugin-reset' href='javascript:;'>CodeGuard Plugin Reset</a> as a last resort.";
      }
    }
    require "pages/dashboard_widget.php";
  } 

  function codeguard_error_message($message_key) {
    switch($message_key) {
    case 'user_duplicate_email': 
      $m = 'Sorry, we could not create an account with that email address. <a href="plugins.php?page=codeguard-admin-menu">Click here</a> if you already have a CodeGuard account.';
      break;
    case 'invalid_credentials':
      $m = 'CodeGuard is unable to verify your access tokens. <a href="plugins.php?page=codeguard-admin-menu">Update your information.</a>';
      break;
    case 'connection_error':
      $m = 'Unable to reach the CodeGuard service. Please try again later.';
      break;
    case 'missing_website_id':
      $m = 'Your website has not yet been added to CodeGuard! <a href="plugins.php?page=codeguard-admin-menu">See more details.</a>';
      break;
    case 'failed_to_request_backup':
      $m = 'Sorry, we were unable to schedule a backup of your site at this time.  Please try again later.';
      break;
    case 'backup_request_success':
      $m = 'Your backup has been requested. If anything has changed, you will receive an email with additional information.';
      break;
    case 'multisite_not_supported':
      $m = 'Sorry, the CodeGuard plugin does not support multisite installations at this time. Please let us know if this is something you\'re interested in!';
      break;
    default:
      $m = 'Oops! Something went wrong, please try again.';
      break;
    }
    return $m;
  }

  function set_error_message_from_api($message_text, $hidden = true) {
    global $codeguard_ui_messages, $hidden_ui_messages;

    $hidden_ui_messages = $hidden;
    $codeguard_ui_messages = $message_text;
    add_action('admin_notices', array($this, 'ui_message_transport'));
  }

  function set_ui_error_message($message_key, $hidden = true) {
    global $codeguard_ui_messages, $hidden_ui_messages;

    $hidden_ui_messages = $hidden;
    $codeguard_ui_messages = $this->codeguard_error_message($message_key);
    add_action('admin_notices', array($this, 'ui_message_transport'));
  }

  function ui_message_transport() {
    require "pages/ui_message_transport.php";
  }

  function send_plugin_error($msg) {
    header('HTTP/1.1 403 Forbidden');
    $error_array = Array("Error" => $msg);

    /* try {
      $error_array["phpinfo"] = $this->phpinfo_array();
    } catch (Exception $e) {
      //Do nothing
    }
     */
    echo "\n" . json_encode($error_array) . "\n";
  }

  function phpinfo_array()
  {
    ob_start(); 
    phpinfo(-1);

    $pi = preg_replace(
      array(
        '#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
        '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
        "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
    '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
      '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
      '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
      "# +#", '#<tr>#', '#</tr>#'),
      array(
        '$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
        '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
        "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
        '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
        '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
        '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
      ),
      ob_get_clean()
    );

    $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
    unset($sections[0]);

    $pi = array();
    foreach ($sections as $section)
    {
      $n = substr($section, 0, strpos($section, '</h2>'));
      preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);
      foreach($askapache as $m)
      {
        $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
      }
    }

    return $pi;

  }
}

// Startup actions and registration 
add_action("init", create_function('', 'new CodeGuard_WP_Plugin();'));
register_activation_hook(__FILE__, array('CodeGuard_WP_Plugin', 'install'));
register_deactivation_hook(__FILE__, array('CodeGuard_WP_Plugin', 'uninstall'));
