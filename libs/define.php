<?php
if (!defined("PHP_VERSION_DEFAULT")) {
    define("PHP_VERSION_DEFAULT", '5.2.4' );
}
if (!defined("PREFIX_CODEGUARD")) {
    define("PREFIX_CODEGUARD", 'codeguard_backup_' );
}
if (!defined("MYSQL_VERSION_DEFAULT")) {
    define("MYSQL_VERSION_DEFAULT", '5.0' );
}
if (!defined('RESULT_ERROR')) {
    define('RESULT_ERROR', 'error');
}

if (!defined('RESULT_SUCCESS')) {
    define('RESULT_SUCCESS', 'success');
}
if (!defined('BACKUP_DIR_NAME')) {
    define('BACKUP_DIR_NAME',  'codeguard_backups');
}
if (!defined('BACKUP_DIR')) {
    define('BACKUP_DIR',  WP_CONTENT_DIR . '/' . BACKUP_DIR_NAME);
}
if (!defined('PCLZIP_TEMPORARY_DIR')) {
    define('PCLZIP_TEMPORARY_DIR',  WP_CONTENT_DIR . '/' . BACKUP_DIR_NAME . '/pclzip' );
}
