<?php
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

require_once 'oAuthSimple.php';

class CodeGuard_Client {
  /* 
   * Client Version
   */
  const CG_CLIENT_VERSION = 0.34;
  /*
   * URL Endpoints
   */

  private $_endpoint = 'https://api.codeguard.com';
  private $_user_friendly_endpoint = 'https://www.codeguard.com';

  const WEBSITES_URI = '/websites';
  const WEBSITES_CONTAINER_URI = '/wordpress/container';
  const USERS_URI = '/users';
  const USERS_OWNED_WEBSITES_URI = '/users/owned_websites';
  const WORDPRESS_STATS_URI = '/wordpress/%%/stats';
  const WORDPRESS_URI = '/wordpress/%%';
  const WORDPRESS_REQUEST_BACKUP_URI = '/wordpress/%%/request_backup';

  /*
   * Keys and Tokens
   */ 
  private $partner_api_key = null;
  private $shared_oauth_token = null;
  private $shared_oauth_secret = null;
  private $user_oauth_token = null;
  private $user_oauth_secret = null;

  function __construct($api_key = null) {
    $this->partner_api_key = $api_key;
  }

  public function setup_client_response($response) {
    $response["service"] = "CodeGuard.com Wordpress Backup Service";
    $response["version"] = CodeGuard_Client::CG_CLIENT_VERSION;
    $response["timestamp"] = date('r'); // RFC 2822 formatted date (Thu, 21 Dec 2000 16:01:07 +0200)
    $response["random"] = mt_rand(); 
    return;
  }

  function set_oauth_tokens($shared_token, $shared_secret, $user_token = null, $user_secret = null) {
    $this->shared_oauth_token = $shared_token;
    $this->shared_oauth_secret = $shared_secret;
    $this->user_oauth_token = $user_token;
    $this->user_oauth_secret = $user_secret;
  }

  protected function oauth_signed_url($url) {
    $signed_url = null;
    try {
      $oauthObject = new OAuthSimple($this->shared_oauth_token, $this->shared_oauth_secret);

      $res = $oauthObject->sign( array(
        'path' =>  $url,
        'signatures' => array( 
          'access_token' => $this->user_oauth_token,
          'access_secret' => $this->user_oauth_secret,
        ) ) );                                   

      $signed_url = $res['signed_url'];                               

    } catch( Exception $e ) {
      $signed_url = null;
    }                                                            
    return $signed_url;
  }

  /* 
   * Start URL Generation
   */

  function create_user_url() {
    return $this->_endpoint . CodeGuard_Client::USERS_URI . '?api_key=' . $this->partner_api_key;
  }

  function create_website_url() {
    $url = $this->_endpoint . CodeGuard_Client::WEBSITES_URI;
    return $this->oauth_signed_url($url);
  }

  function create_website_container_url() {
    $url = $this->_endpoint . CodeGuard_Client::WEBSITES_CONTAINER_URI;
    return $this->oauth_signed_url($url);
  }

  function list_user_websites_url() {
    $url = $this->_endpoint . CodeGuard_Client::USERS_OWNED_WEBSITES_URI;
    return $this->oauth_signed_url($url);
  }

  function get_login_url() {
    $url = $this->_user_friendly_endpoint . CodeGuard_Client::USERS_OWNED_WEBSITES_URI;
    return $this->oauth_signed_url($url);
  }

  function get_wordpress_stats_url($id) {
    $url = $this->_endpoint . str_replace('%%', $id, CodeGuard_Client::WORDPRESS_STATS_URI);
    return $this->oauth_signed_url($url);
  }

  function create_website_backup_request_url($id) {
    $url = $this->_endpoint . str_replace('%%', $id, CodeGuard_Client::WORDPRESS_REQUEST_BACKUP_URI);
    return $this->oauth_signed_url($url);
  }

  /* 
   * End URL Generation
   */
}
