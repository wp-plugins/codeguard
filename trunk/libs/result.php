<?php
if (!class_exists('result')) {
    class result {
        public $result = 'success';
        public $error = '';
        public $data = array();
        public $size = '';

        public function __construct() {
            $this->result = RESULT_SUCCESS;
        }

        public function setResult($res)
        {
            $this->result = $res;
            return $this;
        }

        public function setData($res)
        {
            $this->data = $res;
            return $this;
        }

        public function setSize($res)
        {
            $this->size = $res;
            return $this;
        }
        public function setError($res)
        {
            $this->error = $res;
            return $this;
        }
        public function addElement($element, $value)
        {
            $this->{$element} = $value;
        }
        
    }
}