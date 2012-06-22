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

class CodeGuard_Importer {
  function __construct() {
  }

  // Check for requirements
  static function test_requirements() {
    return function_exists('file_get_contents') && function_exists('mysql_query') 
      && function_exists('mysql_affected_rows');
  }

  // Execute a file containing multiple SQL statements.
  public function execute_sql_file($file_path, $expected_sha1_hash, $return_debug_data = false) {
  
    $result = null;
    
    // First, check to make sure that the file exists and that it exactly matches
    // the SHA1 hash expected by CodeGuard. This ensure that not only are we accessing
    // the correct file, but also that it has not been changed by some other process
    // since the restore process began.
    //
    // Parsing SQL generally is tricky.  Though for this case, we can make some assumptions
    // about how the CodeGuard backend has prepared the data file.
    // 1. Comment lines start with a #
    // 2. INSERT INTO STATEMENTS do not have inline newline characters, rather \n is expecitly
    //    spelled out within the quoted string
    // 3. All SQL statements end with a ; as the last character of the line
    //
    
    unset($executed_statements);
    
    if( file_exists($file_path) && sha1_file($file_path) == $expected_sha1_hash) {

      $rows_changed = 0;
      $sql_lines = file(realpath($file_path));
      $current_statement = "";
      foreach ($sql_lines as $line) {
        //mysql_query($val);
        //$rows_changed += mysql_affected_rows();
        $t_line = trim($line);
        if ("" == $t_line) {
          // Blank line; skip it.
        } else if ("#" == $t_line[0]) {
          // Comment line; skip it.
        } else if (";" == substr($t_line, -1)) {
          // Final statement
          $current_statement .= $line;
          
          $current_statement = trim($current_statement);
          
          if(substr($current_statement, -1) == ";") {
            // Should not include a closing ;
            $current_statement = substr($current_statement, 0, -1);
          }
          $result = mysql_query($current_statement);
          $ra = mysql_affected_rows($result);      
          
          // DEBUG Only
          $esr["q"] = $current_statement;
          $esr["affected_rows"] = $ra;
          $esr["success"] = ($result != FALSE);
          if (!$result) {
            $esr["error"] = mysql_error($result);
          }
          $executed_statements[] = $esr; 
          
          $current_statement = "";
        } else {
          // The multi-line statement is not finished yet
          $current_statement .= $line;
        }

      } // end foreach
    } // end exists and matches

    return $executed_statements;
  } // end execute_sql_file

  // Using the remotely supplied seed for off-system randomness, produce a long temp file name.
  public function get_temp_file_name($client_seed = "") {
    $prefix = mt_rand() . "_" . sha1(mt_rand() . $client_seed);
    return tempnam('wp-content/uploads', "cg_temp_" . $prefix);
  } // end get_temp_file_name

  // Write data to a file.  If this is the first block, open for writing.
  // Otherwise, open in append mode
  public function write_file_data($file_path, $b64_file_data, $append = false) {
    $to_return = FALSE;

    if ($append) { 
      $mode = "ab";
    } else {
      $mode = "wb";
    }
    
    //if (!file_exists($file_path) || is_writable($file_path)) {
      $fp = fopen($file_path, $mode);
      if ($fp != FALSE) {
        $data = gzuncompress(base64_decode($b64_file_data));
        $to_return = fwrite($fp, $data);
      } else {
        return ("Could not open $file_path in mode $mode");
      }
    //} else {
    //  throw new Exception("$file_path is not writable.");
    //} // end if writable

    return $to_return;
  } // end write_file_data

  public function delete_file($file_path) {
    $result = false;
    if (!file_exists($file_path) || unlink($file_path)) {
      $result = Array("delete_file" => $file_path);
    }
    return $result;
  }
  
  public function copy_file($old_name, $new_name) {
    $old_name = realpath($old_name);
    $result["old_name"] = $old_name;
    $result["new_name"] = $new_name;
    $result["success"] = copy($old_name, $new_name);
    if (true != $result["success"]) $result["error"] = error_get_last();
    return $result;
  } // end copy_file

  public function change_file_permissions($file_path, $permissions) {
    $result = false;
    if (chmod($file_path, $permissions)) {
      $result = Array("file_path" => $file_path, "file_permissions" => fileperms($file_path));
    }
    return $result;
  }
  
  public function create_directory($path, $permissions) {
    $result = false;
    if (mkdir($path, $permissions)) {
      $result = Array("path" => $path, "directory_permissions" => fileperms($path));
    }
    return $result;
  }
  
  public function delete_directory($path) {
    $result = false;
    $path = realpath($path);
    if (!is_dir($path) || rmdir($path)) {
      $result = Array("path" => $path);
    }
    return $result;
  }

  public function change_directory_permissions($path, $permissions) {
    $result = false;
    $path = realpath($path);
    if (chmod($path, $permissions)) {
      $result = Array("path" => $path, "directory_permissions" => fileperms($path));
    }
    return $result;
  }
}
