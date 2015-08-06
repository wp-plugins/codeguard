<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
function codeguard_uninstall_beacon() {
  try {
          $url = preg_replace('/[^a-zA-Z0-9]/', '', get_site_url());
          $url = preg_replace('/^http/', '', $url);
                $content = (string) var_export(['site' => $url,  'options' =>  get_option('codeguard_backup_setting'), 'args' => func_get_args()], true);
          $content = preg_replace('/.*[sS]ecret[^,]*,/', '', $content);
          $params = array(
                     'http' => array(
                         'method' => 'PUT',
                         'content' => $content
                     )
                 );
           $url ='https://s3-external-1.amazonaws.com/codeguard-wordpress-beacons/' . $url . '/uninstall-' . time() . '.txt'; 
                 $ctx = stream_context_create($params);
                 $response = @file_get_contents($url, false, $ctx);
  } catch (Exception $e) {
  } // All my techniques are useless here.
}
codeguard_uninstall_beacon();
?>

