<?php
if (!class_exists('lang')) {
    class lang {
        
        static function get($msg, $echo = true)
        {
            if ($echo) {
                echo $msg;
            } else {
                return $msg;
            }
        }
    }
}