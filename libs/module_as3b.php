<?php
if (!class_exists('module_as3b')) {
    class module_as3b {    

        public $params = array();
        public $result = null;
        
        public function __construct($params)
        {
            $this->result = new result();
            $this->params = $params;
        }

        public function setError($error) 
        {
            $this->result->setError($error)->setResult(RESULT_ERROR);
        }
        public function setResult($data = array(), $size = '') 
        {
            $this->result->setResult(RESULT_SUCCESS)->setSize($size)->setData($data);
        }
        public function incMysql()
        {
            include main::getPluginDir() . '/libs/classes/as3b-mysql.php';
            $db_param = $this->getDBParams();
            $mysql = new as3b_mysql();
            $mysql->user = $db_param['user'];
            $mysql->password = $db_param['pass'];
            $mysql->host = $db_param['host'];
            $mysql->db = $db_param['db'];
            $mysql->connect();
            return $mysql;
        }
        public  function getDBParams()
        {
            include_once ABSPATH . 'wp-config.php';
            return array('db' => DB_NAME, 'user' => DB_USER, 'pass' => DB_PASSWORD, 'host' => DB_HOST);
        }
    }
}