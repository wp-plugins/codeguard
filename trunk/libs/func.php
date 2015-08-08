<?php
    /**
    * General functions
    *
    */

    @set_time_limit(0);

    if ( ! function_exists( 'plugin_unpack' )) {
        function plugin_unpack( $str ) {
            return unserialize( base64_decode( $str ) );
        }
    }

    if ( ! function_exists('plugin_pack')) {
        function plugin_pack( $value ) {
            return base64_encode( serialize ( $value ) ) ;
        }
    }
    if (!function_exists('checkInstallWpadmPlugins')) {
        function checkInstallWpadmPlugins()
        {
            $return = false;
            $i = 1;
            foreach(main::$plugins as $plugin) {
                if (checkPlugin($plugin)) {
                    $i ++;
                }
            }
            if ($i > 2) {
                $return = true;
            }
            return $return;
        }
    }
    if (!function_exists('checkPlugin')) {
        function checkPlugin($name)
        {
            if (!empty($name)) {
                if ( ! function_exists( 'get_plugins' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $name2 = str_replace("-", "_", $name);
                $plugin = get_plugins("/$name");
                if (empty($plugin)) {
                    $plugin = get_plugins("/$name2");
                }

                if (count($plugin) > 0) {
                    if (in_array($name, main::$plugins) && (isset($plugin["$name.php"]) || isset($plugin["$name2.php"]))) {
                        if (is_plugin_active("$name/$name.php") || is_plugin_active("$name/$name2.php") || is_plugin_active("$name2/$name2.php")) {
                            return true;
                        }
                    }
                }
                return false;
            }
        }
    }
    if (!function_exists('wpadm_plugins')) {
        function wpadm_plugins()
        {
            global $wp_version;

            $c = get_system_data();
            $phpVersion         = $c['php_verion'];
            $maxExecutionTime   = $c['maxExecutionTime'];
            $maxMemoryLimit     = $c['maxMemoryLimit'];
            $extensions         = $c['extensions'];
            $disabledFunctions  = $c['disabledFunctions'];
            //try set new max time

            $newMaxExecutionTime = $c['newMaxExecutionTime'];
            $upMaxExecutionTime = $c['upMaxExecutionTime'];
            $maxExecutionTime = $c['maxExecutionTime'];

            //try set new memory limit
            $upMemoryLimit = $c['upMemoryLimit'];
            $newMemoryLimit = $c['newMemoryLimit'];
            $maxMemoryLimit = $c['maxMemoryLimit'];

            //try get mysql version
            $mysqlVersion = $c['mysqlVersion'];
        ?>
        <div class="clear" style="margin-bottom: 50px;"></div>
        <table class="wp-list-table widefat fixed" >
            <thead>
                <tr>
                    <th></th>
                    <th>Recommended value</th>
                    <th>Current value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">PHP Version</th>
                    <td><?php echo PHP_VERSION_DEFAULT ?> or greater</td>
                    <td><?php echo comparison_version($phpVersion , PHP_VERSION_DEFAULT) === false ? '<span style="color:#fb8004;font-weight:bold;">' . $phpVersion .'</span>' : $phpVersion ?></td>
                    <td><?php echo (comparison_version($phpVersion , PHP_VERSION_DEFAULT) ? '<span style="color:green;font-weight:bold;">OK</span>' : '<span style="color:#fb8004;font-weight:bold;">Please update your PHP version</span>') ?></td>
                </tr>
                <tr>
                    <th scope="row">MySQL Version</th>
                    <td><?php echo MYSQL_VERSION_DEFAULT ?> or greater</td>
                    <td><?php echo comparison_version($mysqlVersion , MYSQL_VERSION_DEFAULT) === false ? '<span style="color:#fb8004;font-weight:bold;">' . $mysqlVersion .'</span>' : $mysqlVersion; ?></td>
                    <td><?php echo (comparison_version($mysqlVersion , MYSQL_VERSION_DEFAULT) ? '<span style="color:green;font-weight:bold;">OK</span>' : '<span style="color:#fb8004;font-weight:bold;">Please update your MySQL version</span>') ?></td>
                </tr>
                <tr>
                    <th scope="row">Max Execution Time</th>
                    <td><?php echo $newMaxExecutionTime ?></td>
                    <td><?php echo ($upMaxExecutionTime == 0) ? '<span style="color:#fb8004;font-weight:bold;">' . $maxExecutionTime .'</span>' : $maxExecutionTime; ?></td>
                    <td><?php echo ($upMaxExecutionTime == 1) ? '<span style="color:green; font-weight:bold;">OK</span>' : '<span style="color:#fb8004;font-weight:bold;">Backups may not work correctly</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">Max Memory Limit</th>
                    <td><?php echo $newMemoryLimit . 'M' ?></td>
                    <td><?php echo ($upMemoryLimit == 0) ? '<span style="color:#fb8004;font-weight:bold;">' . $maxMemoryLimit .'</span>' : $maxMemoryLimit  ?></td>
                    <td><?php echo ($upMemoryLimit == 1) ? '<span style="color:green;font-weight:bold;">OK</span>' : '<span style="color:#fb8004;font-weight:bold;">Backups may not work correctly</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">PHP Extensions</th>
                    <?php $ex = $c['ex']; ?>
                    <td><?php echo ( $ex ) === false ? 'All present' : '<span style="color:#ffba00;font-weight:bold;">' . implode(", ", $ex) . '</span>'; ?></td>
                    <td><?php echo ( $ex ) === false ? 'Found' : '<span style="color:#ffba00;font-weight:bold;">Not Found</span>'; ?></td>
                    <td><?php echo ( $ex ) === false ? '<span style="color:green;font-weight:bold;">Ok</span>' : '<span style="color:#fb8004;font-weight:bold;">Backups may not work correctly</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">Disabled Functions</th>
                    <td colspan="3" align="left"><?php echo ( $func = $c['func']) === false ? '<span style="color:green;font-weight:bold;">None</span>' : '<span style="color:#fb8004;font-weight:bold;">Please enable the following functions: ' . implode(", ", $func) . '</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">Plugin Access</th>
                    <td colspan="3" align="left"><?php echo ( ( is_admin() && is_super_admin() ) ? "<span style=\"color:green; font-weight:bold;\">Granted</span>" : "<span style=\"color:red; font-weight:bold;\">Denied</span>")?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
if (! function_exists('comparison_function')) {
    function comparison_function($func, $search, $type = false)
    {
        if (is_string($func)) {
            $func = explode(", ", $func);
        }
        if (is_string($search)) {
            $search = explode(", ", $search);
        }
        $res = false;
        $n = count($search);
        for($i = 0; $i < $n; $i++) {
            if (in_array($search[$i], $func) === $type) {
                $res[] = $search[$i];
            }
        }
        return $res;
    }
}

if (! function_exists('comparison_version')) {
    function comparison_version($version1, $version2)
    {
        return version_compare($version1, $version2, ">");
    }
}
if (!function_exists("get_system_data")) {
    function get_system_data()
    {

        global $wp_version;

        $php                = phpversion();
        $maxMemLimit        = ini_get('memory_limit');
        $extensions         = implode(', ', get_loaded_extensions());
        $disabledFunctions  = ini_get('disable_functions');

        $maxExTime   = ini_get('max_execution_time');
        $upMaxExTime = 0;
        $newMaxExTime = intval($maxExTime) + 60;
        @set_time_limit( $newMaxExTime );
        if( ini_get('max_execution_time') == $newMaxExTime ){
            $upMaxExTime = 1;
            $maxExTime = ini_get('max_execution_time');
        }
        $mysql       = '';
        if (! class_exists('wpdb')) {
            require_once ABSPATH . '/' . WPINC . '/wp-db.php';
        }
        $mysqli = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
        $errors = $mysqli->last_error;
        if (empty($errors)) {
            $mysql = $mysqli->db_version();
        }
        $upMemLimit = 0;
        $newMemLimit = intval($maxMemLimit) + 60;
        ini_set('memory_limit', $newMemLimit.'M');
        if( ini_get('memory_limit') == $newMemLimit ){
            $upMemLimit = 1;
            $maxMemLimit = ini_get('memory_limit');
        }
        $extensions_s = array('curl', 'json', 'mysqli', 'sockets', 'zip', 'ftp');
        $disabledFunctions_s = array('set_time_limit', 'curl_init', 'fsockopen', 'ftp_connect');

        $ext = comparison_function($extensions, $extensions_s);
        $function = comparison_function($disabledFunctions, $disabledFunctions_s, true);

        return array('wp_version' => $wp_version,'wp_lang' => get_option('WPLANG'), 'php_verion' => $php,
        'maxExecutionTime' => $maxExTime, 'extensions' => $extensions, 'disabledFunctions' => $disabledFunctions,
        'mysqlVersion' => $mysql, 'upMaxExecutionTime'  => $upMaxExTime,
        'newMaxExecutionTime' => $newMaxExTime, 'upMemoryLimit' => $upMemLimit,
        'newMemoryLimit' => $newMemLimit, 'maxMemoryLimit' => $maxMemLimit,
        'ex' => $ext, 'func' => $function,
        );
    }
}
if (!function_exists('readDirectrory')) {
    function readDirectrory($dir, $format = '')
    {
        $result = array();
        if (is_dir($dir)) {
            $dir_open = opendir($dir);
            if ($dir_open) {
                while($d = readdir($dir_open)) {
                    if ($d != '.' && $d != '..' && $d != BACKUP_DIR_NAME ) {

                        if (is_dir($dir . "/" . $d)) {
                            $result = array_merge($result, readDirectrory($dir . "/" . $d, $format));
                        } elseif (is_file($dir . "/" . $d)) {
                            if (empty($format)) {
                                $result[] = $dir . "/" . $d;
                            } elseif ( !empty($format) && is_string($format) ) {
                                if(substr($d, -4) == $format) {
                                    $result[] = $dir . "/" . $d;
                                }
                            } elseif (!empty($format) && is_array($format) ) {
                                if(in_array( substr($d, -4), $format ) ) {
                                    $result[] = $dir . "/" . $d;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
}
