<?php
if(!defined("ABSPATH"))
  die(); 
?>

<div id="codeguard-warning" class="updated fade error">
  <p>
    <?php 
    printf(
      __('<strong>ERROR</strong>: Your WordPress site is using an outdated version of PHP, %s.  Version 5.2 of PHP is required to use the CodeGuard plugin. Please ask your host to update.', 'codeguard'),
      PHP_VERSION
    );
    ?>
  </p>
</div>
