<?php
if(!defined("ABSPATH"))
  die(); 
?>
<div class="wrap">
    <div class="cg_wpp_header">
      <a href="http://www.codeguard.com/"><img src="https://www.codeguard.com/images/cg_logo_white.png" /></a>
    </div>
    <div id="codeguard_inline_error"></div>
    <div id="cgadmin-content">
      <div class="cg_wpp_top_intro">
        <h1><span>CodeGuard</span> Settings</h1>
      </div>

      <div class="cg_wpp_bottom_area">
        <h2>Update API Key</h2>
        <p>You can update your API key in the field below. Changing your API key will cause your backup to be restarted using the CodeGuard account associated with the new API key. 
          </br> 
          Not sure where to find your API key? That's ok. <a target="_blank" href="https://www.codeguard.com/wordpress">Follow these instructions to obtain your key</a>.</p>
        <form action="" method="post" id="codeguard-tokens-form" style="">
          <label for="codeguard-tokens-combined">Key:<label>
          <p><textarea id="codeguard-tokens-combined" name="codeguard-tokens-combined" cols="45" rows="2" key="<?php echo $this->get_codeguard_api_access_token() . $this->get_codeguard_api_access_secret(); ?>"><?php echo $this->get_codeguard_api_access_token() . $this->get_codeguard_api_access_secret(); ?></textarea></p>
          <p class="submit"><input id="cg_wpp_button" class="cg_wpp_submit" type="submit" name="submit" value="Update Key and Restart" /></p>
        </form>
      </div>
      
      <div class="cg_wpp_bottom_area_reset_data">
        <h2>Reset Plugin Data</h2>
        <p>Resetting the plugin data will remove all CodeGuard related information from your database. It will also prevent CodeGuard from backing up your site. You should only click this button if the CodeGuard Support Team has advised you to do so or you intend to permanently deactivate the plugin..</p>
        <p class="submit"><input id="codeguard-plugin-reset" type="submit" name="submit" value="Delete ALL CodeGuard Data" /></p>
      </div>
    </div>
  </div>
<script type="text/javascript" >
var codeguard_admin_init = function() {
  if(typeof(jQuery) == 'undefined') {
    setTimeout("codeguard_admin_init()", 10);
  } else {
    jQuery(document).ready(function() {
      wp_codeguard.check_for_error_messages('#codeguard_hidden_error', '#codeguard_inline_error');
      // Unfortunately, the Jetpack banner gets positioned right in the middle of our header content, so we have to hide it.
      jQuery('div#message.updated.jetpack-message.jp-connect').hide();
      jQuery('#codeguard-plugin-reset').click(function() {
        if(confirm("Are you sure? This will prevent CodeGuard from backing up your WordPress site.")) {
          jQuery.post("", {"codeguard-plugin-reset" : true}, function() { location.reload(); });
        }
      });
      jQuery('#cg_wpp_button').click(function() {
        if(jQuery('#codeguard-tokens-combined').attr("key") === jQuery('#codeguard-tokens-combined').val()) {
          return false;
        } else {
          if(confirm("Are you sure?")) {
            jQuery.post("", {"codeguard-plugin-reset" : true}, function() {
              jQuery.post("", {"codeguard-tokens-combined" : jQuery('#codeguard-tokens-combined').val()}, 
                function() { 
                  document.location.href = "admin.php?page=codeguard-admin-menu";
                });
            });
            return false;
          }
        }
      });
    });
  }
};
codeguard_admin_init();
</script>
