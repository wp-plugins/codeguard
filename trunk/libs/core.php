<?php

if (!class_exists('core')) {
    class core {

        private $auth = true;
        public  $result = null;
        public  $request = array();
        public  $params = array();
        public  $method = '';
        private $method_access = array('connect', 'reconnect', 'ping');

        function __construct($method = '', $request = array(), $auth = false)
        {
            $this->result = new result();
            $this->auth = $auth;
            $this->method = $method;
            $this->request = $request;
            if (isset($this->request['params'])) {
                $this->params = $this->request['params']; 
            }
            if (in_array($method, $this->method_access)) {
                $this->runCommand();
            } else {
                if ($auth === false) {
                    $this->runCommand();
                } elseif ( $this->authentication() ) {
                    $this->runCommand();
                } else {
                    $this->setError('Error Auth');
                }
            }
        }
        private function authentication()
        {
            $this->pub_key = get_option('wpadm_pub_key');
            $sign = md5(serialize($this->request['params']));
            //openssl_public_decrypt($this->request['sign'], $request_sign, $this->pub_key);
            $ret = $this->verifySignature($this->request['sign'], $this->request['sign2'], $this->pub_key, $sign);
            //$ret = ($sign == $request_sign);
            if (!$ret) {
                $this->setError("Signature Error");
            }
            return $ret;
        }
        
        private function verifySignature()
        {
            if (function_exists('openssl_public_decrypt')) {
                openssl_public_decrypt($sign, $request_sign, $pub_key);
                $ret = ($text == $request_sign);
                return $ret;
            } else {
                set_include_path(main::getPluginDir() . '/libs/phpseclib');
                require_once 'Crypt/RSA.php';
                $rsa = new Crypt_RSA();
                $rsa->loadKey($pub_key);
                $ret = $rsa->verify($text, $sign2);
                return $ret;
            }
        }

        private function runCommand()
        {
            $this->backupFolder();
            if (file_exists(main::getPluginDir() . "/modules/{$this->method}.php")) {
                include main::getPluginDir() . "/modules/{$this->method}.php";
                $this->method = str_replace("-", "_", $this->method);
                $command = new $this->method($this->params);
                $command->run();
                $this->result = $command->result;
                if (!empty($command->dirs) && !empty($command->size)) {
                    $this->result->setData($command->dirs);
                    $this->result->setSize($command->size);
                }
                if ($this->auth === false && isset($this->request['sets'])) {  
                    $this->result->addElement('time', $command->time);
                    $this->result->addElement('type', 'local');
                    $this->result->addElement('name', $command->name);
                    $res = (array)$this->result;
                    $this->result->addElement('counts', count($res['data']));
                    $this->result->addElement('md5_data', md5( print_r($res['data'], 1) ) );
                }
            } else {
                $this->setError('Command not found');
            }
        }
        private function backupFolder()
        {
            $dir = BACKUP_DIR;
            if (!is_dir($dir)) {
                mkdir($dir, 0755);
                main::setIndex($dir);
            }
        }

        public function __toString()
        {
            return plugin_pack((array)$this->result);
        }
    }
}