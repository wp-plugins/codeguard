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
<?php
if ($this->has_valid_codeguard_tokens()) { 
?>
  <!-- <p><img class="codeguard-cgshield" src="http://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/> Your user credentials are valid!</p> -->
<?php
  if ($this->has_website_id() ) { 
?>
  <!-- <p><img class="codeguard-cgshield" src="http://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/> Your website has been added to CodeGuard!</p> -->
<?php
    $this->codeguard_dashboard_widget();
  } else {
?>
  <p>Your website has not yet been added.</p>
  <form action="" method="post" id="codeguard-add-website-form" style="">
    <input type="hidden" id="codeguard-add-website" name="codeguard-add-website" value="true" />
    <p class="submit"><input type="submit" name="submit" value="Add website" /></p>
  </form>
<?php
  }
} else {
?>
  <div class="cg_wpp_top_area">
    <div class="cg_wpp_top_intro">
      <h1>Welcome to the <span>CodeGuard</span> WordPress <span>Plugin</span>!</h1>
      <p>CodeGuard privides automatic backup for your WordPress blog, and keep your content safe! After creating your CodeGuard account and <strong>adding your API key</strong> below, we will begin backing up your WordPress site. All of your posts, themes, comments, and everything else associated with your blog will be backed up and securely stored with CodeGuard. Setup is easy, and takes <strong>less than 5 minutes!</strong>
      </p>
      <a href="https://www.codeguard.com/wordpress" id="cg_wpp_button" target="_blank">Let's get started</a>
    </div>
    <div class="cg_wpp_video">
      <iframe width="304" height="180" src="http://www.youtube.com/embed/OLrERaRblsQ" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>

  <div class="cg_wpp_bottom_area">
    <h1>Enter your <span>CodeGuard API Key</span> here</h1>
    <p>Enter your CodeGuard key below. Not sure what this is? That's ok.  <a target="_blank" href="https://www.codeguard.com/wordpress">Follow these instructions to obtain your key</a>.</p>

    <form action="" method="post" id="codeguard-tokens-form" style="">
      <label for="codeguard-tokens-combined">Key:<label>
      <p><textarea id="codeguard-tokens-combined" name="codeguard-tokens-combined" cols="45" rows="2" value="<?php echo $this->get_codeguard_api_access_token(); ?>" ></textarea></p>
      <p class="submit"><input id="cg_wpp_button" class="cg_wpp_submit" type="submit" name="submit" value="Add key and start backup" /></p>
    </form>
  </div>

 <div class="cg_wpp_middle_area">
    <h1>How does it <span>work</span>?</h1>
    <div class="cg_wpp_backup">
      <h2>Automatic Backups</h2>
      <p>CodeGuard offers the most reliable backup on the market with 99.99% reliability. We achieve this by replicating your data in secure locations across the world - again and again and again.
      </p>
      <p>Ever thought about how you'd find out about your site getting hacked? If your site is hacked, it could be days or weeks before you even know about it.  CodeGuard believes that you should be the first to know, and so our monitoring system diligently checks your site for changes.
      </p>
    </div>
    <div class="cg_wpp_monitor">
      <h2>Automatic Monitoring</h2>
      <p>When CodeGuard performs the backup, it compares what is on your sit with the last version of your website stored our system. If any changes are found we emails you with the details!
      </p>
      <p>When CodeGuard performs the backup, it compares what is on your website with the last version of your website stored in our system. We call this a differential backup, which is unique to CodeGuard and much more efficient at storing your data than other services.  If any changes are found, CodeGuard emails you with the details!
      </p>
    </div>
    <div class="cg_wpp_restore">
      <h2>One-Click Restore</h2>
      <p>CodeGuard helps should anything go wrong - deleted files are now recoverable, overwritten files are now obtainable, and if your site is hacked, the malware is easily removable. All of this with nothing to install.
      </p>
      <p>You can rest safe knowing that CodeGuard is also looking out for malware. We interact with Google on a regular basis to make sure your site is neither blacklisted nor infected.
      </p>
    </div>
  </div>

<?php
}
?>
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
          jQuery.post("", {"codeguard-plugin-reset" : true}, function() { location.reload() });
        }
      });
    });
  }
};
codeguard_admin_init();
</script>
