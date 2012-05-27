<?php
if(!defined("ABSPATH"))
  die(); 
?>
<div style="">
<?php
  if(!$this->on_admin_page()) {
?>
  <img src="<?php echo $this->codeguard_plugin_image_url(); ?>cglogo_small_nav.png" />
  <br/>
<?php
  }
?>
  <div id="codeguard_inline_error"></div>
  <div class="codeguard_widget_section">
<?php
if(isset($site_stats->last_backup_time)) {
?>
    <div class="codeguard-submit-div">
      <form>
        <p class="submit"><input type="submit" id="codeguard_login_to_dashboard" name="codeguard_login_to_dashboard" class="button-primary codeguard-button" value="Login to CodeGuard" /></p>
      </form>
    </div>

    <p style="clear:both;"></p>
    <h2>Database Backup</h2>
    <p>This is a backup of your database. It covers all your content: posts, pages, comments, and users.</p>
    <p style="font-size:14px;">Last Backup: <?php echo date('l\, F dS Y h:i:s A', strtotime($site_stats->last_backup_time)) ?></p>
    <div class="table table_content">
    <table width="100%">
      <tr>
      <td>Posts: <?php echo $site_stats->posts ?></td>
      <td>Comments: <?php echo $site_stats->comments ?></td>
      </tr><tr>
      <td>Users: <?php echo $site_stats->users ?></td>
      <td>Categories: <?php echo $site_stats->categories ?></td>
      </tr><tr>
      <td>Pages: <?php echo $site_stats->pages ?></td>
      </tr>
    </table>
    </div>
    <p style="clear:both;"></p>
    <h2>File Backup</h2>
    <p>This is a backup of your physical files. It includes wordpress core files, plugins, themes, the whole shebang.<p>
<?php
  if(isset($site_stats->parent_stats) && isset($site_stats->parent_stats->site_size)) {
?>
    <p style="font-size:14px;">Last Backup: <?php echo date('l\, F dS Y h:i:s A', strtotime($site_stats->parent_stats->last_backup_time)) ?></p>
     <table width="100%">
      <tr>
      <td>Size: <?php echo $site_stats->parent_stats->site_size ?></td>
      </tr>
    </table>
    <ul>
      <li style="first_col">
    </ul>
<?php 
  } else {
?>
  <p><img src="https://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/>Don't worry, we're still working on your first backup. It should be done in a few hours.<p>
<?php
  }
} else if ( isset($error_message) ) {
?>
  </ul>
  <p style="font-weight:bold;"><img class="codeguard-cgshield" src="https://codeguard.com/images/shield.red.png" style="height:24px;width:24px;"/> <?php echo $error_message ?></p>
<?php
} else {
?>
  </ul>
  <p><img src="https://codeguard.com/images/shield.green.png" style="height:24px;width:24px;"/>Great job! CodeGuard is now performing your first backup. It should be done in a few hours.<p>
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
				jQuery('#codeguard_login_to_dashboard').click(function() {
					window.location.href = "<?php echo $login_url ?>";
					return false;
					});
				wp_codeguard.check_for_error_messages('#codeguard_hidden_error', '#codeguard_inline_error');
				});
	}
};
codeguard_dashboard_init();
</script>
