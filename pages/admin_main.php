<?php
if(!defined("ABSPATH"))
  die(); 
?>
<div class="wrap">
    <img src="<?php echo $this->codeguard_plugin_image_url(); ?>cglogo_small_nav.png" />
    <div id="codeguard_inline_error"></div>
    <div id="cgadmin-content">
<?php
if ($this->has_valid_codeguard_tokens()) { 
?>
  <h2>CodeGuard Status</h2>
  <!-- <p><img class="codeguard-cgshield" src="http://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/> Your user credentials are valid!</p> -->
<?php
if ($this->has_website_id() ) { 
?>
  <!-- <p><img class="codeguard-cgshield" src="http://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/> Your website has been added to CodeGuard!</p> -->
<?php
  $this->codeguard_dashboard_widget();
} else {
?>
  <p><img src="http://codeguard.com/images/shield.red.png" style="height:24px;width:24px;"/> Your website has not yet been added.</p>
  <form action="" method="post" id="codeguard-add-website-form" style="">
    <input type="hidden" id="codeguard-add-website" name="codeguard-add-website" value="true" />
    <p class="submit"><input type="submit" name="submit" value="Add website" /></p>
  </form>
<?php
  }
} else {
?>

      <h2>What is CodeGuard?</h2>
      <p style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;">CodeGuard is an automatic daily backup service for your WordPress blog. After creating your CodeGuard account and adding your API key below, CodeGuard will begin backing up your WordPress site.  All of your posts, themes, comments, and everything else associated with your blog will be backed up and securely stored with CodeGuard.</p>
      <p class="submit" style="text-align:center;"><a href="https://www.codeguard.com/wordpress" style="font-size: 21px !important;padding: 3px 40px;" class="button-primary codeguard-button" target="_blank">Get Started Now!</a></p>

      <h2>How does it work?</h2>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td height="30px" colspan="3"><img src="https://www.codeguard.com/images/spacer.gif" height="30px" width="1px" style="display: block;"></td>
        </tr>
        <tr>
          <td width="235px" valign="top" align="left">
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;"><b>Automated Daily Backups that never let you down.</b></span><span style="line-height: 0.1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">CodeGuard offers the most reliable backup on the market - 99.999999999% reliable. We achieve this by replicating your data in secure locations across the world - again and again and again.</span></span><br><br>
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;"><b>Receive ChangeAlerts when content on your site changes.</b></span><span style="line-height: 0.1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">When CodeGuard takes the daily backup, it compares what is on your website with the last version of your website stored CodeGuard's system.  If any changes are found, CodeGuard emails you with the details!</span></span><br><br>
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;"><b>Use Time Machine to view older versions of your website!</b></span><span style="line-height: 0.1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">CodeGuard takes a picture of what your website looks like each time it takes a backup.  Then, when you need to sort through older versions of your site, it's much easier when you can look at them!</span></span></td>
          <td width="40px" valign="top" align="left"><img src="https://www.codeguard.com/images/spacer.gif" height="1px" width="30px" style="display: block;"></td>
          <td width="235px" valign="top" align="left">
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1em; color:#323232;"><b>Get UNDO Power for when anything goes wrong</b></span><span style="line-height: .1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">CodeGuard helps should anything go wrong - deleted files are now recoverable, overwritten files are now obtainable, and if your site is hacked, the malware is easily removable.  All of this with nothing to install.</span></span><br><br>
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;"><b>Easily Scan for Malware and Google Blacklisting</b></span><span style="line-height: 0.1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">You can rest safe knowing that CodeGuard is also looking out for malware.  We interact with Google on a regular basis to make sure your site is neither blacklisted nor infected.</span></span><br><br>
            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.1em; color:#323232;"><b>Source Code and Database Differential Storage</b></span><span style="line-height: 0.1em;"><br>&nbsp;<br></span><span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.3em; color: #787878;">CodeGuard seamlessly backs up your source and databases. And it does it in an elegant way that saves you space and makes it easy to see changes between each backup/version.</span></span><br><br>
          </td>
        </tr>
        <tr> 
          <td height="30px" colspan="3"><img src="https://www.codeguard.com/images/spacer.gif" height="30px" width="1px" style="display: block;"></td> 
        </tr> 
      </table>

      <h2>CodeGuard API Key</h2>
      <p>Enter your CodeGuard key below. Not sure what these are? <a target="_blank" href="https://www.codeguard.com/wordpress">Click here to find your CodeGuard key.</a></p>

      <form action="" method="post" id="codeguard-tokens-form" style="">
        <h3><label for="codeguard-tokens-combined">Key<label></h3>
        <p><textarea id="codeguard-tokens-combined" name="codeguard-tokens-combined" cols="45" rows="2" value="<?php echo $this->get_codeguard_api_access_token(); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;"></textarea></p>
        <p class="submit"><input type="submit" name="submit" value="Update" /></p>
      </form>
<?php
}


?>
    </div>
  </div>
<div class="clear"></div>
<a id="codeguard-plugin-reset" href="javascript:;">CodeGuard Plugin Reset</a>
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
          jQuery.post("", {"codeguard-plugin-reset" : true}, function() { location.reload() });
        }
      });
    });
  }
};
codeguard_admin_init();
</script>
