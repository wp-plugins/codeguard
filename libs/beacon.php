<?php
function codeguard_beacon($action, $args) {
  try {
          $url = preg_replace('/[^a-zA-Z0-9]/', '', get_site_url());
          $url = preg_replace('/^http/', '', $url);
          $content = (string) var_export(['site' => $url,  'options' =>  get_option(PREFIX_CODEGUARD . 'setting'), 'args' => func_get_arg(1)], true);
          $content = preg_replace('/.*[sS]ecret[^,]*,/', '', $content);
          $params = array(
                     'http' => array(
                         'method' => 'PUT',
                         'content' => $content
                     )
                 );
           $url ='https://s3-external-1.amazonaws.com/codeguard-wordpress-beacons/' . $url . '/' . $action . '-' . time() . '.txt'; 
                 $ctx = stream_context_create($params);
                 $response = @file_get_contents($url, false, $ctx);
  } catch (Exception $e) {
  } // All my techniques are useless here.
}
?>
