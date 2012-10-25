<?php
if(!defined("ABSPATH"))
  die(); 
?>
<div style="">
<?php
  if(!$this->on_admin_page()) {
?>
   <div class="cg_wpp_header">
      <a href="http://www.codeguard.com/"><img src="https://www.codeguard.com/images/cg_logo_white.png" /></a>
   </div>
<?php
  }
?>
  <div class="cg_wpp_dashboard">
    <div class="wrap_left_stuff">
      <h3>Plugin Dashboard</h3>
      <a href="javascript:;" class="cg_login_to_dashboard"><img id="cg_wpp_visit" src="https://www.codeguard.com/images/btn_cgwpp_visit.png" /></a>
    </div>
  </div>
  <div id="codeguard_inline_error"></div>
  <div class="codeguard_widget_section">
<?php
if(isset($site_stats->last_backup_time)) {                                                                                                              
?>
    <h5 id="special_h5">Last Backup:<h5>
    <h1 class="cg_wpp_special_h1"><?php echo date('l\, F dS Y', strtotime($site_stats->last_backup_time)) ?> <span class="cg_wpp_time_span"> / <?php echo date(' h:i:s A', strtotime($site_stats->last_backup_time)) ?></span></h1>
    <div class="cg_wpp_table">
      <h5 id="special_h52">Content:</h5>
      <table>
        <tr id="tr_top">
          <td>Posts</td>
          <td>Comments</td>
          <td>Pages</td>
          <td>Users</td>
          <td>Categories</td>
          <?php
            if(isset($site_stats->parent_stats->site_size)) {
          ?>
            <td>Website Size</td>
          <?php
            }
          ?>
        </tr>
        <tr>
          <td><span><?php echo $site_stats->posts ?></span> Total</td>
          <td><span><?php echo $site_stats->comments ?></span> Total</td>
          <td><span><?php echo $site_stats->pages ?></span> Total</td>
          <td><span><?php echo $site_stats->users ?></span> Total</td>
          <td><span><?php echo $site_stats->categories ?></span> Total</td>
          <?php
            if(isset($site_stats->parent_stats->site_size)) {
          ?>
            <td><span><?php echo $site_stats->parent_stats->site_size ?></span> Total</td>
          <?php
            }
          ?>
        </tr>
      </table>
      <p class="cg_nospacer">This is a backup of all your WordPress content. It includes WordPress posts, comments, pages, images, uploads, core files, plugins, themes, the whole shebang.  By visiting the CodeGuard dashboard you will have access to all of this information and much more.  <a id="cg_wpp_a" href="javascript:;" class="cg_login_to_dashboard">Try logging in to see!</a>
      </p>
    </div>

    <div class="cg_wpp_bottom_dashboard">
      <div>
        <p class="tiny_header">Website Backup</p>
        <h2>We are content security made easy.</h2>
        <p>CodeGuard offers the most reliable backup on the market with 99.99% reliability. We achieve this by replicating your data in secure locations across the world and backing up your site automatically.
        </p>
        <p>The <a href="javascript:;" class="cg_login_to_dashboard">CodeGuard dashboard</a> tells you exactly when your next backup will occur, how many files were <strong>added</strong>, <strong>changed</strong>, or <strong>deleted</strong> in your previous backups, and lays all of this information out in an easy to understand way.
      </div>

      <div>
        <p class="tiny_header">Website Monitoring</p>
        <h2>We stay ahead of the curve.</h2>
        <p>Ever thought about how you'd find out about your site getting hacked? If your site is hacked, it could be days or weeks before you even know about it.  CodeGuard believes that you should be the first to know, and so our monitoring system diligently checks your site for changes.
        </p>
        <p>When CodeGuard performs the backup, it compares what is on your website with the last version of your website stored in our system. We call this a differential backup, which is unique to CodeGuard and much more efficient at storing your data than other services.  If any changes are found, CodeGuard emails you with the details!
        </p>
      </div>

      <div>
        <p class="tiny_header">Website Restore</p>
        <h2>We can help you fix the problems.</h2>
        <p>Should anything go wrong, CodeGuard is there to help.  Deleted files are now recoverable, overwritten files are now obtainable, and if your site is hacked, the malware is easily removable.
        </p>
        <p>After selecting which backup version you want restored CodeGuard gives you a few options to choose from.  You can perform a manual restore of your file content or database by requesting a zipped version of your backup, or perform a one-click automatic restore of your database and let us do the heavy-lifting.  You can also restore individual files as needed.  To perform a restore on your WordPress site login to the <a href="javascript:;" class="cg_login_to_dashboard">CodeGuard dashboard</a> and click the "Restore" tab.
      </div>
    </div>
<?php
} else if ( isset($error_message) ) {
?>
  </ul>
  <h1 id="error">Oh no!  It seems an <span>error</span> has occurred</h1>
  <p><?php echo $error_message ?></p>
<?php
} else {
?>
  </ul>
  <h1 id="special_widget_h1">Congratulations!  You have <span>successfully</span> added your site to CodeGuard!</h1>
  <p>CodeGuard is now performing your first backup.  To check on the status of your first backup you can <a href="javascript:;" class="cg_login_to_dashboard">login to CodeGuard</a> and see real-time progress updates.  There you can see the estimated time your backup will be completed and where it is in the backup process. <br /><br />
  Depending on the size of your site this could take a couple of hours.  We thank you in advance for your 
  patience, and are excited for you to start protecting your site!</p>
  <a href="javascript:;" class="cg_login_to_dashboard" id="cg_wpp_button">Login to CodeGuard</a>
<?php
}
?>
  </div>
  <p style="clear:both;"></p>
</div>

<script type="text/javascript" >
var codeguard_dashboard_init = function() {
	if(typeof(jQuery) == 'undefined') {
		setTimeout("codeguard_dashboard_init()", 10);
	} else {
      jQuery(document).ready(function() {
        jQuery('body').on('click', '.cg_login_to_dashboard', function() {
            window.location.href = "<?php echo $login_url ?>";
            return false;
            });
        wp_codeguard.check_for_error_messages('#codeguard_hidden_error', '#codeguard_inline_error');
      });
	}
};
codeguard_dashboard_init();
</script>