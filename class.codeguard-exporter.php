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

class CodeGuard_Exporter {
  const DEFAULT_FILE_LIST_BUNDLE_SIZE = 500;
  const TABLE_DATA_BUNDLE_SIZE = 1000;

  function __construct() {
  }

  // Check for openssl requirements
  static function test_requirements() {
    return function_exists('openssl_pkey_get_public') && function_exists('openssl_seal')
      && function_exists('base64_encode') && function_exists('openssl_public_decrypt')
        && function_exists('openssl_free_key') && function_exists('openssl_get_privatekey')
          && function_exists('openssl_get_publickey') && function_exists('sha1')
            && function_exists('gzcompress') && function_exists('base64_decode');
  }

  //
  // The 2048-bit RSA public key for CodeGuard's Backup Server.
  // All data returned by this plugin is strongly encrypted using the OpenSSL library
  // this key.
  //
  public function codeguard_public_key_contents() {
    // This is the test key, not the production one.
    //return "-----BEGIN PUBLIC KEY-----\nMFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAKpfrreqZb9B5pLYU02qFpXeMB2XUI70\nPhg7Dsp6lGgw43Dv8CbK/JNvn6PuCRYtHOzDpuLeG+1wjKfXgkzB2P8CAwEAAQ==\n-----END PUBLIC KEY-----\n";

    // CodeGuard public key
    return "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3k75f7nJSDj324f7k9pz\n8uwI6EDD9R+vMIlUOLt+oeeBw2mR2N9z7OMLXmHE3418RfDrZBEtBIymlh4WnBMd\nY37iWJD9FuxJbrzObWDrxGUCsE5bELKEFWcMO9OBSn+xXmVdiDn+pfmewpnQm5Sn\ndK0pECTKjAYn8VJR5lruUNc3+2hw8eiYA21f+x3DaMZx0MFtqBQ5288IkAOEie6V\n87zD4trStoSerhLsUbPXIYYK5kI+8xgIZuUwr068nhqG0JwmrTfLD9DPkLe43kqt\n7VHYvUkSlWk8NdYhy28nevsKH8RMVuklYWz6u9rGNLS5Ssf9ZenNQXUA/xNzg1BJ\nXwIDAQAB\n-----END PUBLIC KEY-----\n";
  } // end codeguard_public_key_contents


  ////////////////////////////////////////////////////////////////////////////////////
  // Web Application Security Functions
  ////////////////////////////////////////////////////////////////////////////////////

  // Compress and seal the data using openssl_seal and encode it for JSON transport.
  // This function is by Rietta Inc. It's licensed under the terms of the BSD license.
  private function compress_and_seal_with_json($dataToEncrypt) {
    $pubKey[] = openssl_pkey_get_public($this->codeguard_public_key_contents());
    $sealed = "";
    $ekeys = "";
    $result = openssl_seal(gzcompress($dataToEncrypt), $sealed, $ekeys, $pubKey);
    foreach($ekeys AS $key => $value) $ekeys_ascii[$key] = base64_encode($value);
    return array('encdata'=>base64_encode($sealed)
      ,'enckey'=>json_encode($ekeys_ascii)
    );
  } // end compress_and_seal_with_json

  // Using an RSA public key, verify that the data and timestamp were signed by the
  // associated RSA private key (that is not present here) using a SHA1 hash of the
  // data and its timestamp.
  // Return boolean TRUE if its valid.  FALSE, otherwise.
  // This function is by Rietta Inc. It's licensed under the terms of the BSD license.
  private function verify_with_rsa($received, $public_key_contents) {
    $to_return = false;
    $pubkeyid = openssl_get_publickey($public_key_contents);
    $signature = base64_decode($received->signature);
    if (openssl_public_decrypt($signature, $opened_signature, $pubkeyid)) {
      if(sha1($received->timestamp . $received->data) == $opened_signature) {
        $to_return = true;
      } // end if the SHA1 hash matches the signature
    } // end if openssl_public_decrypt worked with the signature
    openssl_free_key($pubkeyid);
    return $to_return;
  } // end verify_with_rsa_from_json

  // Wrapper for the encryption function that encrypts arbitrary data and returns
  // a JSON-encoded packet.  The data passed in must be able to be encoded with json.
  private function bundle_and_seal_with_json($data_to_seal) {
    return json_encode($this->compress_and_seal_with_json(json_encode($data_to_seal)));
  } // bundle_and_seal_with_json


  public function decrypt_chunked_rsa($array_of_chunks, $private_key_contents) {
    $to_return = "";
    $privkey = openssl_get_privatekey($private_key_contents);
    if (is_array($array_of_chunks) && count($array_of_chunks) > 0) {
      $errors = 0;
      $items = 0;
      unset($data);
      foreach($array_of_chunks as $echunk) {
        $ciphertext = base64_decode($echunk);
        if(openssl_private_decrypt($ciphertext, $plaintext, $privkey)) {
          $data[] = $plaintext;
          $items++;
        } else {
          $errors++;
        } // end if
      } // end foreach
      if ($items > 0 && $errors == 0) {
        $to_return = implode($data);
      }
    } // end if
    return $to_return;
  } // end decrypt_chunked_rsa

  ////////////////////////////////////////////////////////////////////////////////////
  // CodeGuard Backup Functions
  ////////////////////////////////////////////////////////////////////////////////////

  public function get_verified_and_decrypt_chunked_rsa($encoded_data, $private_key_contents) {
    $to_return = false;
    try {
      $chunked_rsa = $this->get_verified_string(base64_decode($encoded_data));
      if (false != $chunked_rsa) {
        if ($private_key_contents) {
          $to_return = $this->decrypt_chunked_rsa(json_decode($chunked_rsa), $private_key_contents);
        }
      } else {
        $to_return = false;
      }
    } catch (Exception $e) {
      $to_return = false;
    }
    return $to_return;
  } // end get_verified_and_decrypt_chunked_rsa


  public function get_verified_string($json_data_received) {
    $to_return = false;
    $received = json_decode($json_data_received);

    $pub_key = $this->codeguard_public_key_contents();

    if ($this->verify_with_rsa($received, $pub_key)) {
      $to_return = $received->data;
    } // end if
    return $to_return;
  } // end get_verified_string


  ////////////////////////////////////////////////////////////////////////////////////
  // CodeGuard File Backup
  ////////////////////////////////////////////////////////////////////////////////////

  public function get_file_list_stream($FILE_LIST_BUNDLE_SIZE = CodeGuard_Exporter::DEFAULT_FILE_LIST_BUNDLE_SIZE) {

    // Try to get the absolute path of the blog root
    try {
      $path = $this->get_wp_root_path();
      $path = realpath($path);
      if (substr($path, -1) !== DIRECTORY_SEPARATOR)
        $path .=  DIRECTORY_SEPARATOR;
    } catch (Exception $e) {
      return false;
    }

    $file_list = array(); 
    $start_at = time();
    $queue = array($path => 1);
    $done  = array();
    $index = 0;
    while(!empty($queue)) {
      // Pop the next element from the queue
      foreach($queue as $path => $unused) {
        unset($queue[$path]);
        $done[$path] = null;
        break;
      }
      unset($unused);

      $dh = @opendir($path);
      if (!$dh) continue;
      while(($filename = readdir($dh)) !== false) {
        if ($filename == '.' || $filename == '..')
          continue;

        $filename = $path . $filename;

        // Need to log
        if(realpath($filename) == false)
          continue;

        if (is_link($filename)) {
          $filename = realpath($filename);
          $file_list[] = $this->get_file_attributes( $filename );       
        } else if (is_dir($filename)) {
          if (substr($filename, -1) !== DIRECTORY_SEPARATORATOR)
            $filename .= DIRECTORY_SEPARATOR;

          // Skip if the item is already in the queue or has already been done
          if (array_key_exists($filename, $queue) || array_key_exists($filename, $done))
            continue;

          // Add directory to list
          $file_list[] = $this->get_file_attributes( realpath($filename) );       

          // Add dir to the queue
          $queue[$filename] = null;
        } else {
          // Add the file to the list
          $filename = realpath($filename);
          $file_list[] = $this->get_file_attributes( $filename );       
        }

        if (count($file_list) >= $FILE_LIST_BUNDLE_SIZE) {
          echo $this->bundle_and_seal_with_json($file_list) . "\n";
          $bundle_count++;
          unset($file_list);
        }
      }
      closedir($dh);
    }

    if (count($file_list) > 0) {
      echo $this->bundle_and_seal_with_json($file_list) . "\n";
      $bundle_count++;
      unset($file_list);
    }

    return array('comment' => true
      , 'bundles' => $bundle_count
      , 'runtime' => time() - $start_at);
  }

  public function get_file_attributes_with_sha1 ($filename) {
    $result = $this->get_file_attributes($filename);
    $result['sha1'] = sha1_file($filename);
    return $result;
  }

  private function get_file_attributes( $filename ) {

    $fs = filesize("$filename");
    $lm = filemtime("$filename");
    $perms = fileperms("$filename");
    $user = fileowner("$filename");
    $group = filegroup("$filename");
    $is_symlink = is_link("$filename");
    $is_dir = is_dir("$filename");

    $file_attributes = array(
      'path' => $filename
      ,'size' => $fs
      ,'perms' => $perms
      ,'user' => $user
      ,'group' => $group
      ,'symlink' => $is_symlink
      ,'dir' => $is_dir
      ,'mtime' => $lm
    );
    return $file_attributes;
  }

  private function get_wp_root_path() {
    if ( defined('ABSPATH') ) {
      return ABSPATH;
    } else {
      return null;
    }
  }

  public function get_file_data_in_chunks($file_name, $block_size = 2097152, $start_at = -1, $end_at = -1) {

    $start_at = time();

    // First check out the file to make sure things are good
    $error_message = "";

    unset($to_return);

    if (!(file_exists($file_name) && is_readable($file_name))) {
      $error_message = "error: the requested file could not be found";
    } else if (!is_file($file_name) || is_link($file_name )) {
      $error_message = "error: cannot dump a directory or symbolic link; only regular files are supported";
    } else {

      //echo $this->bundle_and_seal_with_json(array("Hi!")) . "\n";
      // Get the characteristics of the file
      $file_size = filesize($file_name);
      if($file_size > $block_size) {
        $block_count = $file_size / $block_size;
      } else {
        $block_count = 1;
      }
      $sent_block_count = 0;
      $sha1_sum = sha1_file($file_name);

      // Now read the file!
      $fp = fopen($file_name, 'rb');
      if ($fp) {

        while (!feof($fp)) {
          $block_content = fread($fp, $block_size);
          echo $this->bundle_and_seal_with_json(
            array('idx' => $sent_block_count, 'b64_data' => base64_encode($block_content))
          ) . "\n";
          $sent_block_count++;
        } // end while
        fclose( $fp );

        $to_return = array('comment' => true
          , 'error' => false
          , 'file_name' => $file_name
          , 'file_sha1' => $sha1_sum
          , 'file_size' => $file_size
          , 'block_size' => $block_size
          , 'block_count' => $block_count
          , 'sent_block_count' => $sent_block_count
          , 'runtime' => time() - $start_at);

      } else {
        $error_message = "error: could not open file for reading.";
      } // end if fopen succeeded
    } // end if

    if ($error_message != "" && !isset($to_return)) {
      $to_return = array('comment' => true
        , 'file_name' => $file_name
        , 'error' => true
        , 'message' => $error_message
        , 'runtime' => time() - $start_at);
    } // end if need an error message

    return $to_return;

  } // end get_file_contents_in_chunks

  ////////////////////////////////////////////////////////////////////////////////////
  // CodeGuard Database Backup
  ////////////////////////////////////////////////////////////////////////////////////

  private function get_table_sql_definition($table_name) {
    $safe_table = mysql_escape_string($table_name);
    $result = mysql_query("SHOW CREATE TABLE `$table_name`");
    $to_return = "";
    if ($record = mysql_fetch_array($result)) {
      if ($table_name == $record[0]) {
        $to_return = $record[1];
      }
    } // end if
    return $to_return;
  } // end get_table_sql_definition

  // Prepare a dump of the tables from this Wordpress site's database.
  // For each table ask MySQL for the table schema information - which
  // will return a copy of the SQL create statement that recreates it.
  private function get_tables_list_helper() {
    mysql_set_charset('utf8');
    $result = mysql_query("SHOW TABLES");
    unset($table_names);
    while($record = mysql_fetch_array($result)) {
      if ("" != $record[0]) {
        $tbl_def["name"] = $record[0];
        $tbl_def["definition"] = $this->get_table_sql_definition($record[0]);
        $tbl_def["record_count"] = $this->get_table_record_count($record[0]);
        $tbl_def["fetched_at"] = time();
        $table_names[] = $tbl_def;
      }
    } // end while
    return $table_names;
  } // end get_tables_list


  public function get_table_record_count($table_name) {
    $safe_table = mysql_escape_string($table_name);
    return $this->select_count_as_int("SELECT COUNT(*) FROM `$safe_table`");
  } // end get_user_count

  // Perform a basic MySQL dump function for the specified table.
  // Return each group of up to  records as a compressed, encrypted json array
  public function get_table_data($table_name, $limit_start = -1, $limit_end = -1) {

    $start_at = time();
    $RECORDS_PER_BUNDLE = CodeGuard_Exporter::TABLE_DATA_BUNDLE_SIZE;

    $safe_table = mysql_escape_string($table_name);
  
    //
    // The function takes two optional limit parameters. If one is specified, it
    // sets the limit to that.  Eg, LIMIT 1.  If two are specified, it sets the
    // sliding window limit, such as LIMIT 0, 100.
    //
    $limit_start = intval($limit_start);
    $limit_end = intval($limit_end);
    if ($limit_start >= 0 && $limit_end >= 0) {
      $limit_clause = " LIMIT $limit_start, $limit_end";
    } else if ($limit_start >= 0) { 
      $limit_clause = " LIMIT $limit_start";
    } else {
      $limit_clause = "";
    }
    $qry = trim("SELECT * FROM `$safe_table` $limit_clause");

    mysql_set_charset('utf8');
    $result = mysql_query($qry);
    unset($data);
    $i = 0;
    $bundle_count = 0;
    $insert_count = 0;
    while($record = mysql_fetch_assoc($result)) {
      // base64 encode the values so they can be blindly passed to json_encode
      foreach($record as $key => $value) $record[$key] = base64_encode($value);

      $data_entry['fetched_at'] = time();
      $data_entry['database_name'] = $database_name;
      $data_entry['table_name'] = $table_name;
      $data_entry['data'] = $record;
      $data[] = $data_entry;
      $i++;
      $insert_count++;

      if ($i > $RECORDS_PER_BUNDLE) {
        echo $this->bundle_and_seal_with_json($data) . "\n";
        unset($data);
        $i = 0;
        $bundle_count++;
      } // end if
    } // end while

    // If there are any records added since the last bundle and seal, output them now
    if ($i > 0) {
      //echo json_encode($data) . "\n";
      echo $this->bundle_and_seal_with_json($data) . "\n";
      $bundle_count++;
    }

    return array('comment' => true
      , 'inserts' => $insert_count
      , 'bundles' => $bundle_count
      , 'runtime' => time() - $start_at);
  } // end get_table_data

  public function get_tables_list() {
    $wpd['db_name'] = DB_NAME;
    $wpd['db_user'] = DB_USER;
    $wpd['db_host'] = DB_HOST;
    $wpd['random']  = mt_rand();
    $wpd['tables']  = $this->get_tables_list_helper();
    return $wpd;
  } // end get_tables_list_as_json

  // Run a simple, single-item count query.
  // This returns the first item of the first record as an integer.
  private function select_count_as_int($query_to_run) {
    $to_return = 0;
    $result = mysql_query($query_to_run);
    if ($record = mysql_fetch_array($result)) {
      $to_return = intval($record[0]);
    }
    return $to_return;
  } // end get_count

  private function select_as_hash($query_to_run) {
    $to_return = false;
    $result = mysql_query($query_to_run);
    while($record = mysql_fetch_assoc($result)) {
      $to_return[] = $record;  
    } // end while
    return $to_return;
  } // end select_as_hash

  private function get_taxonomy_counts() {
    global $wpdb; 
    $terms_tbl = $wpdb->prefix . "terms";
    $taxo_tbl = $wpdb->prefix . "term_taxonomy";
    return $this->select_as_hash("
      SELECT
      T.taxonomy AS type
      ,COUNT(DISTINCT L.term_id) as count
      FROM $terms_tbl L 
        INNER JOIN $taxo_tbl T ON
          L.term_id = T.term_id
      GROUP BY type
      ORDER BY type ASC;
    ");
  } // end get_taxonomy_counts


  private function get_post_counts() {
    global $wpdb; 
    $posts_tbl = $wpdb->prefix . "posts";
    return $this->select_as_hash("  
      SELECT
        post_type
        ,post_status
        ,COUNT(*) as count
      FROM $posts_tbl
      GROUP BY post_type, post_status
      ORDER BY post_type ASC, post_status ASC
      ");
  } // end get_post_counts

  private function get_comment_count() {
    global $wpdb; 
    return $this->get_table_record_count($wpdb->prefix . "comments");
    //return $this->select_count_as_int("SELECT COUNT(*) FROM wp_comments");
  } // end get_comment_count

  private function get_user_count() {
    global $wpdb; 
    return $this->get_table_record_count($wpdb->prefix . "users");
    //return $this->select_count_as_int("SELECT COUNT(*) FROM wp_users");
  } // end get_user_count

  private function get_all_bloginfo() {
    global $wpdb;
    $infos = Array('version', 'url', 'wpurl', 'description', 'rdf_url', 'rss_url', 'rss2_url', 'atom_url', 'comments_atom_url', 'comments_rss2_url', 'pingback_url', 'stylesheet_url', 'stylesheet_directory', 'template_directory', 'template_url', 'admin_email', 'charset', 'html_type', 'language', 'text_direction', 'name');
    unset($to_return);
    foreach($infos as $info) {
      $to_return[$info] = get_bloginfo($info);
    }
    $to_return['wp_db_prefix'] = $wpdb->prefix;
    return $to_return;
  } // end get_all_bloginfo


  public function get_wp_statistics() {
    return array(
      'comment_count' => $this->get_comment_count()
      ,'user_count'   => $this->get_user_count() 
      ,'taxonomies'   => $this->get_taxonomy_counts()
      ,'posts'        => $this->get_post_counts()
      ,'bloginfo'     => $this->get_all_bloginfo()
    );
  } // end get_wp_statistics

  // For server profiling purposes, we can collect the PHP information for this server.
  // The result will be encrypted to the CodeGuard backup key.
  public function phpinfo_data() {
    try {
      ob_start () ;
      phpinfo () ;
      $info_text = ob_get_contents () ;
      ob_end_clean () ;
    } catch (Exception $e) {
      $info_text = false;
    } // end try/catch
    return $info_text;
  } // end phpinfo_data

  public function respond_with($response) {
    //echo json_encode($response);
    $sealed_bundle = $this->bundle_and_seal_with_json($response);
    echo $sealed_bundle;
    return;
  } // end respond_with
} // end CodeGuard_Exporter 
