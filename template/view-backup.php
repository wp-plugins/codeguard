<div class="wrap">
    <script>
        jQuery(document).ready(function(){
            jQuery('[data-toggle="tooltip"]').tooltip(); 
            
            jQuery(".update_settings").click(function() {
              jQuery(".cfTabsContainer").fadeToggle();
            });
        });
        process_flag = 0;
        function start_backup(type)
        {
            auth_param = <?php echo isset($amazon_option['access_key_id']) && !empty($amazon_option['access_key_id']) && 
                isset($amazon_option['secret_access_key']) && !empty($amazon_option['secret_access_key']) && 
                isset($amazon_option['bucket']) && !empty($amazon_option['bucket']) ? 'false' : 'true'; ?> ;
            if (auth_param === false || type == 'local') { 
                var data_backup = {};
                if (type == 'local') {
                    data_backup['action'] = 'amazon-s3-backup_local_backup';
                } else if(type == 's3') {
                    data_backup['action'] = 'amazon-s3-backup_create';
                }
                d = new Date();
                data_backup['time'] = Math.ceil(  (d.getTime() + (-d.getTimezoneOffset() * 60000 ) ) / 1000 );
                jQuery("#logs-form").show("slow");
                jQuery("#log-backup").html('');
                jQuery("#action-buttons").css('margin-top', '8px'); 
                jQuery("#support-button").css('margin-top', '8px'); 
                jQuery(".title-logs").css('display', 'block');
                jQuery(".title-status").css('display', 'none');
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data_backup,
                    beforeSend: function(){
                        process_flag = 1
                        processBar();
                    },
                    success: function(data){
                        process_flag = 0;
                        if (data.result == 'success') {
                            jQuery('.title-logs').css('display', 'none');
                            jQuery('.title-status').css({'display':'block'});
                            if (type == 'local') {
                                jQuery('.title-status').html('Local backup created successfully');
                            } else if (type == 's3') {
                                jQuery('.title-status').html('CodeGuard backup created successfully');
                            }
                            showData(data);
                        } else {
                            jQuery('.title-logs').css('display', 'none');
                            jQuery('.title-status').css({'display':'block', 'color':'red'});
                            if (type == 'local') {  
                                jQuery('.title-status').html('Local backup failed');
                            } else if(type == 's3') {
                                jQuery('.title-status').html('CodeGuard backup failed: ' + data.error);
                            }
                        }
                        jQuery('.table').css('display', 'table');

                    },
                    error: function(){
                        processStop();
                    },
                    dataType: 'json'
                });
            } else {
                jQuery('#is-amazon-auth').arcticmodal({
                    beforeOpen: function(data, el) {
                        jQuery('#is-amazon-auth').css('display','block');

                    },
                    afterClose: function(data, el) {
                        jQuery('#is-amazon-auth').css('display','none');
                        showSetting(false);
                        blick('app_key', 4);
                        blick('app_secret', 4);
                    }
                });
            }
        }
        function showData(data)
        {
            if ( (typeof data) == 'object' ) {
                size_backup = data.size / 1024 / 1024;
                info = "";
                for(i = 0; i < data.data.length; i++) {
                    e = data.data[i].split('/');
                    info += '<tr style="border: 0;">' +
                    '<td style="border: 0;padding: 0px;"><a href="<?php echo get_option('siteurl') . "/wpadm_backups/"?>' + data.name + '/' + e[e.length - 1] + '">' + e[e.length - 1] + '</td>' +
                    '</tr>' ;
                }
                jQuery('.table > tbody:last').after(
                '<tr>'+
                '<td class="pointer" style="text-align: left; padding-left: 7px;" >' +
                data.time + 
                '</td>' +
                '<td class="pointer">' +
                data.name +
                '</td>' +
                '<td class="pointer">' +
                size_backup.toFixed(2) + "Mb" +
                '</td>' +
                '<td>' +
                '<a href="javascript:void(0)" class="button-wpadm" title="Restore" onclick="recovery_form(\'' + data.type + '\', \'' + data.name + '\')"><span class="pointer dashicons dashicons-backup"></span>Restore</a> &nbsp;' +
                '<a href="javascript:void(0)" class="button-wpadm" title="Delete" onclick="delete_backup(\'' + data.name + '\', \'' + data.type + '\')"><span class="pointer dashicons dashicons-trash"></span>Delete</a> &nbsp;' +
                '</td>' +
                '</tr>')
            }
        }

    </script>
    <div>
        <?php if (!empty($error)) {
                echo '<div class="error" style="text-align: center; color: red; font-weight:bold;">
                <p style="font-size: 16px;">
                ' . $error . '
                </p></div>'; 
        }?>
        <?php if (!empty($msg)) {
                echo '<div class="updated" style="text-align: center; color: red; font-weight:bold;">
                <p style="font-size: 16px;">
                ' . $msg . '
                </p></div>'; 
        }?>
        <div id="is-amazon-auth" style="display: none; width: 400px; text-align: center; background: #fff; border: 2px solid #dde4ff; border-radius: 5px;">
            <div class="title-description" style="font-size: 20px; text-align: center;padding-top:45px; line-height: 30px;">
                Please, add your Amazon credentials:<br />
                <strong>"Secret Key"</strong>, <strong>"Key ID"</strong> & <strong>"Bucket"</strong> <br />
                in the Setting Form
            </div>
            <div class="button-description" style="padding:20px 0;padding-top:45px">
                <input type="button" value="OK" onclick="jQuery('#is-amazon-auth').arcticmodal('close');" style="text-align: center; width: 100px;" class="button-wpadm">
            </div>
        </div>
        <div id="helper-keys" style="display: none;width: 400px; text-align: center; background: #fff; border: 2px solid #dde4ff; border-radius: 5px;">
            <div id="key-info" style="display: none;">
                <div class="title-description" style="font-size: 20px; text-align: center;padding-top:20px; line-height: 30px;">
                    How to get Amazon S3<br /> Access Key ID & Secret Key?
                </div>
                <div class="button-description" style="padding:20px 10px;padding-top:20px; text-align: left;">
                    If you don’t have an Amazon Web Services account yet, you need to <a href="http://aws.amazon.com">sign up</a>.
                    Once you’ve signed up, you will need to <a href="https://console.aws.amazon.com/iam/home?region=us-east-1#users">create a new IAM user</a> and grant access to the specific services which this plugin will use (e.g. S3).
                </div>
            </div>
            <div id="bucket-info" style="display: none;">
                <div class="button-description" style="padding:20px 10px;padding-top:20px; text-align: left;">
                    <div class="title-description" style="font-size: 20px; text-align: left; line-height: 30px;margin-bottom: 5px;">
                        What is Amazon Bucket?
                    </div>
                    Bucket - it's Something like Folder in your PC, but the Bucket stay in the Cloud of your Cloud provider like Dropbox, Amazon S3 etc.<br />Read aditional documentation on <a href="http://docs.aws.amazon.com/AmazonS3/latest/dev/UsingBucket.html" target="_blank">Amazon User Guide</a>.<br />
                    <div class="title-description" style="font-size: 20px; text-align: left;padding-top:20px; line-height: 30px; margin-bottom: 5px;">
                        How to create an Amazon Bucket?
                    </div>
                    For creating a bucket using Amazon S3 console, go to <a href="http://docs.aws.amazon.com/AmazonS3/latest/UG/CreatingaBucket.html" target="_blank">Creating a Bucket</a>  in the <i>Amazon Simple Storage Service Console User Guide</i>.
                </div>
            </div>

            <div class="button-description" style="padding:20px 0;padding-top:10px">
                <input type="button" value="OK" onclick="jQuery('#helper-keys').arcticmodal('close');" style="text-align: center; width: 100px;" class="button-wpadm">
            </div>
        </div>
        <div class="block-content" style="margin-top:20px;">
            <div style="padding-top: 10px;"> 
                <div class="log-amazon" style="background-image: url(<?php echo plugins_url('/images/codeguard_logo.png', dirname(__FILE__));?>);">
                </div>

                <div class="cfTabsContainer" style="float: left; clear: both; padding-bottom: 0px; padding-top: 0px;<?php echo isset($amazon_option['codeguard_key']) ? 'display:none;' : ''?>">
                    <div id="setting_active" class="cfContentContainer" style="">
                        <form method="post" action="" >
                            <div class="stat-wpadm-registr-info" style="width: auto; margin-bottom: 5px;">
                                <div  style="margin-bottom: 12px; margin-top: 20px; font-size: 15px;">
                                    Copy and paste your <strong>Unique Access Key</strong> here:
                                </div>
                                <table class="form-table stat-table-registr" style="margin-top:2px">
                                    <tbody>
                                        <tr valign="top">
                                            <td>
                                                <input id="codeguard_key" oninput="toggleButton()" class="" type="text" name="codeguard_key" placeholder="Unique Access Key" value="<?php echo isset($amazon_option['codeguard_key']) ? $amazon_option['codeguard_key'] : ''?>" style="width: 100%; padding: 10px 13px; margin-bottom: 10px;">
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <td>
                                                <input class="btn-orange" type="submit" value="Save" style="height: 35px; min-height: 35px; max-height: 35px; margin-bottom: 10px; width: 68px; max-width: 68px; min-width: 68px;" id="enterKeyButton">
                                                <a href="https://www.codeguard.com/wordpress-plugin" target="_blank" class="new_key">Need a new Access Key?</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="clear"></div> 
                </div> 
            </div>

            <!-- create backup buttons -->
            <div class="" style="margin-top:10px;">
            <div id="logs-form" style="display: none; float:left; clear: both;">
                <div class="title-logs"><span style="font-size:16px;">Please wait...</div>
                <div class="title-status" style="font-size:16px; display: none;"></div>
                <div id="log-block">
                    <div id="log-backup" style="overflow: auto; height: 60px; border: 5px solid #fff; "></div>
                </div>
            </div>
            <div id="action-buttons" style="">
                <div style="float: left; margin-top: 20px; clear: both;">
                    <button style="width: 167px; min-width: 167px; max-width: 167px; height: 46px; min-height: 46px; max-height: 46px; color: #fff;" onclick="start_backup('s3')" class="backup_button">Create Backup</button> <br />
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
        <div style="margin-bottom: 10px; clear: both;"></div>
        <div>
            <form action="" method="post" id="form_auth_backup" name="form_auth_backup"></form>
            <form action="<?php echo admin_url( 'admin-post.php?action=amazon-s3-backup_delete_backup' )?>" method="post" id="delete_backups" name="delete_backups">
                <input type="hidden" name="backup-name" id="backup_name" value="" />
                <input type="hidden" name="backup-type" id="backup_type" value="" />
            </form>

            <table class="table" style="margin-top: 5px; display: <?php echo isset($data['md5']) && ($n = count($data['data'])) && is_array($data['data'][0]) ? 'table' : 'none'?>;">
                <thead>
                    <tr>
                        <th align="left">Backup Time</th>
                        <th>Name</th>
                        <th>Size</th>
                        <?php if(is_admin() || is_super_admin()) {?>
                            <th>Action</th>
                            <?php
                            }
                        ?> 
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($data['md5']) && isset($data['data']) && ($n = count($data['data'])) && is_array($data['data'][0])) { 
                            for($i = 0; $i < $n; $i++) {
                                $size = $data['data'][$i]['size'] / 1024 / 1024; /// MByte
                                $size = round($size, 2);
                            ?>
                            <tr>
                                <td class="pointer" style="text-align: left; padding-left: 7px;"><?php echo $data['data'][$i]['dt'];?></td>
                                <td class="pointer"><?php echo $data['data'][$i]['name'];?></td>
                                <td class="pointer"><?php echo $size . "Mb";?></td>
                                <?php if(is_admin() || is_super_admin()) {?>
                                    <td style="width: 220px;">
                                        <a class="button-wpadm" href="javascript:void(0)" title="Restore" onclick="recovery_form('<?php echo $data['data'][$i]['type'];?>', '<?php echo $data['data'][$i]['name']?>')" style="color: #fff;"><span class="pointer dashicons dashicons-backup" style="margin-top:3px;"></span>Restore</a>&nbsp;
                                        <a class="button-wpadm" href="javascript:void(0)" title="Delete" onclick="delete_backup('<?php echo $data['data'][$i]['name']; ?>', '<?php echo $data['data'][$i]['type'];?>')" style="color: #fff;"><span class="pointer dashicons dashicons-trash" style="margin-top:3px;"></span>Delete</a>&nbsp;
                                    </td>
                                    <?php
                                    }
                                ?>
                            </tr>
                            <?php 
                        } ?>

                        <?php } ?>
                </tbody>
            </table>
        </div>
        <a href="javascript:void(0);" class="update_settings">Update Settings</a>
        </div>
    </div>

</div>
