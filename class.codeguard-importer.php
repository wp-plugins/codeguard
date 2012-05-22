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

  public function execute_sql_file($file_path) {
    $result = null;
    if( file_exists($file_path) ) {
      $sql = explode(";\n", file_get_contents(realpath($file_path)));
      $rows_changed = 0;
      foreach ($sql as $key => $val) {
        mysql_query($val);
        $rows_changed += mysql_affected_rows();
      }
      $result = $rows_changed;
    }
    return $result;
  }

  public function put_file_data($file_path, $file_data) {

    // Could create the directory structure if it doesn't exist
    /*
    $file_directory = dirname($file_path);
    if(!is_dir($file_directory)) {
      mkdir($file_directory, 0755);
    }
    */

    $file_size = file_put_contents($file_path, $file_data, FILE_APPEND | LOCK_EX);
    if ($file_size == false) {
      return false;
    } else {
      return Array("file_path" => $file_path, "file_size" => $file_size);
    }
  }

  public function delete_file($file_path) {
    $result = false;
    if (unlink($file_path)) {
      $result = Array("delete_file" => $file_path);
    }
    return $result;
  }

  public function change_file_permissions($file_path, $permissions) {
    $result = false;
    if (chmod($file_path, $permissions)) {
      $result = Array("file_path" => $file_path, "file_permissions" => fileperms($file_path));
    }
    return $result;
  }
}
