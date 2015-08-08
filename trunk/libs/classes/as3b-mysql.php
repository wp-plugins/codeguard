<?php
if (!class_exists('as3b_mysql')) {
    class as3b_mysql {

        public $host = '';
        public $db = '';
        public $user = '';
        public $password = '';
        public $dbh ;
        public $log = array();

        public function connect() {
            main::log( lang::get('Connecting to database' , false) );
            if (! class_exists('wpdb')) {
                require_once ABSPATH . '/' . WPINC . '/wp-db.php';
            }
            $this->dbh = new wpdb( $this->user, $this->password, $this->db, $this->host );
            $errors = $this->dbh->last_error;
            if ($errors) {
                $this->setError( lang::get('MySQL error: ' , false) . $errors);
            }
            return $this->dbh;
        }

        public function optimize() {
            main::log( lang::get('Optimizing database tables' , false) );
            $n = $this->dbh->query('SHOW TABLES');
            $result = $this->dbh->last_result;
            if (!empty( $this->dbh->last_error ) && $n > 0) {
                $this->setError($this->dbh->last_error);
            } else {
                for($i = 0; $i < $n; $i++ ) {
                    $res = array_values( get_object_vars( $result[$i] ) );
                    $log = str_replace('%s', $res[0], lang::get('Optimizing table: %s' , false) );
                    main::log($log);
                    $this->dbh->query('OPTIMIZE TABLE '. $res[0]);
                    if (!empty( $this->dbh->last_error ) ) {
                        $log = str_replace('%s', $res[0], lang::get('Error: Could not optimize table: %s.' , false) );
                        main::log($log);
                    }
                }
                main::log( lang::get('Finished optimizing tables' , false) );
            }

        }

        // TODO (RM): lock tables. mysql backup does not lock tables
        // TODO (RM): look for better way to backup tables, possibly using mysql commands?
        public function backup($filename) {
            main::log( lang::get('Extracting database contents' , false) );
            $tables = array();
            $n = $this->dbh->query('SHOW TABLES');
            $result = $this->dbh->last_result;
            if (!empty( $this->dbh->last_error ) && $n > 0) {
                $this->setError($this->dbh->last_error);
            }
            for($i = 0; $i < $n; $i++ ) {
                $row = array_values( get_object_vars( $result[$i] ) );
                $tables[] = $row[0];
            }

            $return = '';
            foreach($tables as $table)
            {
                $log = str_replace('%s', $table, lang::get('Extracting contents from table: %s' , false) );
                main::log( $log );
                $num_fields = $this->dbh->query('SELECT * FROM ' . $table);
                $result = $this->dbh->last_result;
                if (!empty( $this->dbh->last_error ) && $n > 0) {
                    $this->setError($this->dbh->last_error);
                }

                $return.= 'DROP TABLE ' . $table.';';

                $ress = $this->dbh->query('SHOW CREATE TABLE ' . $table);
                $result2 = $this->dbh->last_result;
                if (!empty( $this->dbh->last_error ) && $n > 0) {
                    $this->setError($this->dbh->last_error);
                }
                $row2 = array_values( get_object_vars( $result2[0]  ) );
                $return.= "\n\n".$row2[1].";\n\n";
                if ($num_fields > 0) {
                    for ($i = 0; $i < $num_fields; $i++)
                    {
                        $row = array_values( get_object_vars( $result[$i] ) );
                        //main::log('row' . print_r($row, 1));
                        $rows_num = count($row);
                        if ($rows_num > 0) {
                            $return.= 'INSERT INTO '.$table.' VALUES(';
                            for($j=0; $j < $rows_num; $j++)
                            {
                                $row[$j] = addslashes($row[$j]);
                                $row[$j] = str_replace("\n","\\n",$row[$j]);
                                if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                                if ($j<($rows_num-1)) { $return.= ','; }
                            }
                            $return.= ");\n";
                        }

                    }
                }
                $return.="\n\n\n";
            }
            unset($this->dbh);
            $handle = fopen($filename,'w+');
            fwrite($handle,$return);
            fclose($handle);
            main::log( lang::get('Finished extracting database contents' , false) );
            return true;
        }

        private function setError($txt)
        {
            throw new Exception($txt);
        }

        public function restore($file)
        {
            main::log( lang::get('Restoring database contents' , false) );
            $fo = fopen($file, "r");
            if (!$fo) {
                main::log( lang::get('Error: Could not open database backup.' , false) );
                $this->setError( lang::get('Error: Could not open database backup' , false) );
                return false;
            }
            $sql = "";
            while(false !== ($char = fgetc($fo))) {
                $sql .= $char;
                if ($char == ";") {
                    $char_new = fgetc($fo);
                    if ($char_new !== false && $char_new != "\n") {
                        $sql .= $char_new;
                    } else {
                        $ress = $this->dbh->query($sql);
                        //$log = $this->parseMysql($sql);
                        if ( !empty( $log['message'] ) ) {
                            main::log( lang::get( str_replace("%s", $log['table'], $log['message'] ), false ) );
                        }
                        if (!empty( $this->dbh->last_error ) && $n > 0) {
                            $this->setError($this->dbh->last_error);
                            main::log(lang::get('MySQL Error: ' , false) . $this->dbh->last_error);
                            break;
                        };
                        $sql = "";
                    }
                }
            }
            main::log(lang::get('Finished restoring database contents' , false));
        }
        function parseMysql($sql)
        {
            $msg = $table = '';
            $res = array();
            if( stripos($sql, "create") ) {
                preg_match("/create table [`]{0,1}([a-zA-Z_]+)[`]{0,1}/i", $sql, $res);
            } elseif( stripos($sql, "drop") ) {
                preg_match("/drop table [`]{0,1}([a-zA-Z_]+)[`]{0,1}/i", $sql, $res);
            } elseif( stripos($sql, "insert") ) {
                preg_match("/insert into [`]{0,1}([a-zA-Z_]+)[`]{0,1}/i", $sql, $res);
            }
            if (isset($res[1])) {
                $table = $res[1];
            }
            if (!empty($table) && !isset($this->log['create'][$table])) {
                $msg = 'Creating table: %s';
                $this->log['create'][$table] = 1;
            }
            if (!empty($table) && !isset($this->log['drop'][$table])) {
                $msg = 'Dropping table: %s';
                $this->log['drop'][$table] = 1;
            }
            if (!empty($table) && !isset($this->log['insert'][$table])) {
                $msg = 'Inserting table: %s';
                $this->log['insert'][$table] = 1;
            }

            return array('message' => $msg, 'table' => $table);
        }
    }
}

