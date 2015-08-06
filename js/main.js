var blick_form = true;
function blick(id, border_)
{
    if (border_ == 'undefined') {
        border_ = 10;
    }
    jQuery('#' + id).css({
        outline: "0px solid #cd433d",
        border: "0px"
    }).animate({
        outlineWidth: border_ + 'px',
        outlineColor: '#cd433d'
    }, 400).animate({outlineWidth: '0px',outlineColor: '#cd433d' } , 400);
    if (blick_form) {
        setTimeout('blick("' + id + '", ' + border_ + ')', 800);
    }
}

var shows_id = ""
var shows_t = ""
function shows(id, t)
{
    if(document.getElementById(id).style.display == "none") {
        document.getElementById(id).style.display = "table-row";
        jQuery(t).parent("tr").addClass('border-shadow-bottom');
        if (shows_id == "") {
            shows_id = id;
            shows_t = t;
        } else {
            if(shows_id != id) {
                document.getElementById(shows_id).style.display = "none";
                jQuery(shows_t).parent("tr").removeClass('border-shadow-bottom');
            }
            shows_id = id;
            shows_t  = t;
        }
    } else if(document.getElementById(id).style.display == "table-row") {
        document.getElementById(id).style.display = "none";
        jQuery(t).parent("tr").removeClass('border-shadow-bottom');
    }
}
function setReadOnly(id)
{
    r = jQuery('#' + id).attr('readonly');
    if (r == 'readonly') {
        jQuery('#' + id).prop('readonly', false);

    } else {
        jQuery('#' + id).prop('readonly', true);

    }
}
function getHelperAmazon(type)
{
    jQuery('#helper-keys').arcticmodal({
        beforeOpen: function(data, el) {
            jQuery('#helper-keys').css('display','block');
            if (type == 'keys') {
                jQuery('#key-info').css('display','block');
            } else if (type == 'bucket') {
                jQuery('#bucket-info').css('display','block');
            }
        },
        afterClose: function(data, el) {
            jQuery('#helper-keys').css('display','none');
            jQuery('#bucket-info').css('display','none');
            jQuery('#key-info').css('display','none');
        }
    });
}
function auth_form(t)
{
    var button = jQuery(t);
    var form = button.closest('form');
    var data = {};

    var reg = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,6})+$/;
    mail = document.auth.username.value;
    send = false;
    if (!reg.test(mail)) {
        document.auth.username.style.border = "2px solid red";
    } else {
        document.auth.username.style.border = "1px solid #5b9dd9";
        if(document.auth.password.value.length == 0) {
            document.auth.password.style.border = "2px solid red";
        } else {
            send = true;
            document.auth.password.style.border = "1px solid #5b9dd9";
        }
    }
    if(send) {
        form.find('#message-form').css('display', 'none');
        data['password'] = document.auth.password.value;
        data['username'] = document.auth.username.value;
        backup = jQuery("#name_backup_restore").val();
        jQuery.ajax({
            url: form.attr('action'),
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                if( !data){
                    alert('error');
                } else if(data.error) {
                    if(form.find('#message-form').length) {
                        form.find('#message-form').html("");
                        form.find('#message-form').css('display', 'block');
                        form.find('#message-form').html(data.error);
                    }
                } else if(data.url) {
                    jQuery.ajax({
                        url: ajaxurl,
                        data: {'action' : 'set_user_mail', 'email' : document.auth.username.value},
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            location.reload();
                        }
                    });
                    form.attr('action', data.url);
                    jQuery(form).submit();
                }
            }

        });
    }
}
var logs = [];
function processBar()
{
    var data_log = {
        'action': 'amazon-s3-backup_logs',
    };
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data_log,
        success: function(response){
            eval("var data=" + response);
            for(s in data.log) {
                if (jQuery.inArray(s , logs) == -1) {
                    l = jQuery("#log-backup").html();
                    l = "<div>" + data.log[s] + "</div>" + l;
                    jQuery("#log-backup").html(l);
                }
            }
            if (process_flag == 1) {
                setTimeout('processBar()', 3000);
            }
        },
        error: function(){
            processStop();
        },
    });
}

function processStop()
{
    process_flag = 0;
}
function delete_backup(backup, type)
{
    document.delete_backups.backup_name.value = backup;
    document.delete_backups.backup_type.value = type;
    document.delete_backups.submit();
}
function recovery_form(type, name)
{

    var data_backup = {
        'action': 'amazon-s3-backup-as3b-recover',
        'name': name,
        'type': type,
    };
    jQuery("#log-backup").html('');
    jQuery(".title-logs").css('display', 'block');
    jQuery("#action-buttons").css('margin-top', '8px');
    jQuery("#action-buttons").css('margin-top', '8px');
    jQuery("#support-button").css('margin-top', '8px');
    jQuery(".title-status").css('display', 'none');
    jQuery("#logs-form").show("slow");
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
                jQuery('.title-status').css({'display':'block', 'color':'green'});
                if (type == 'local') {
                    jQuery('.title-status').html('Successfully restored local backup: ' + name);
                } else {
                    jQuery('.title-status').html('Successfully restored CodeGuard backup: ' + name);
                }
            } else {
                jQuery('.title-logs').css('display', 'none');
                jQuery('.title-status').css({'display':'block', 'color':'red'});
                if (type == 'local') {
                    jQuery('.title-status').html('Failed to restore local backup: ' + name);
                } else {
                    jQuery('.title-status').html('Failed to restore CodeGuard backup: ' + name);
                }
            }
        },
        error: function(){
            processStop();
        },
        dataType: 'json'
    });

}
function showSetting(show)
{
    display = jQuery('#setting_active').css('display');
    if (display == 'none') {
        jQuery('#setting_active').show('slow');
        jQuery('#setting-show').html("Hide");
        jQuery('#title-setting').css("padding" , "0px 0px");
        jQuery('#setting-choice-icon').removeClass("dashicons-arrow-down").addClass('dashicons-arrow-up');
    } else {
        if (show) {
            jQuery('#setting_active').hide('slow');
            jQuery('#setting-show').html("Show");
            jQuery('#title-setting').css("padding" , "20px 0px");
            jQuery('#setting-choice-icon').removeClass("dashicons-arrow-up").addClass('dashicons-arrow-down');
        }
    }
}
function showRegistInfo(show)
{
    display = jQuery('#cf_activate').css('display');
    if (display == 'none') {
        jQuery('#cf_activate').show('slow');
        jQuery('#registr-show').html("Hide");
        jQuery('#title-regisr').css("padding" , "0px 0px");
        jQuery('#registr-choice-icon').removeClass("dashicons-arrow-down").addClass('dashicons-arrow-up');
    } else {
        if (show) {
            jQuery('#cf_activate').hide('slow');
            jQuery('#registr-show').html("Show");
            jQuery('#title-regisr').css("padding" , "20px 0px");
            jQuery('#registr-choice-icon').removeClass("dashicons-arrow-up").addClass('dashicons-arrow-down');
        }
    }
}
var global={};
function blickForm(id, t)
{
    if(t.checked == true) {
        t.checked = false;
    }
    l = jQuery('#' + id).length;
    showRegistInfo(false);
    if (l > 0) {
        blick(id);
    }
}

function toggleButton() {
    var x = document.getElementById("secret_access_key").value;
    document.getElementById("enterKeyButton").disabled = false;
}
