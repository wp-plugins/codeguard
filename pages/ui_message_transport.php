<?php
  if(!defined("ABSPATH"))
    die(); 

  global $codeguard_ui_messages, $hidden_ui_messages;
  if(isset($hidden_ui_messages) && $hidden_ui_messages == true) {
?>
  <div id="codeguard_hidden_error" style="display:none;"><?php echo $codeguard_ui_messages; ?></div>
<?php
  } else {
?>
  <div id="codeguard_signup" class="updated fade">
    <h2>CodeGuard needs your attention!</h2>
    <div id="codeguard_inline_error" style="display:block;"><?php echo $codeguard_ui_messages; ?></div>
    <p style="clear:both"></p>
  </div>
<?php
  }
?>
