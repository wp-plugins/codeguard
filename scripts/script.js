var wp_codeguard = null;
var codeGuard_jQueryScriptOutputted = false;
function codeguard_initJQuery() {
	if (typeof(jQuery) == 'undefined') {
		if (! codeGuard_jQueryScriptOutputted) {
			codeGuard_jQueryScriptOutputted = true;
			document.write("<scr" + "ipt type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></scr" + "ipt>");
		}
		setTimeout("codeguard_initJQuery()", 50);
	} else {
		wp_codeguard = (function() {
				return {
					check_for_error_messages : function(from, to) {
						error_html = jQuery(from).html();
						if(error_html != null) {
							jQuery(to).html(error_html);
							jQuery(to).show();
						}
						return;
						}
					}
		})();
	}
}
codeguard_initJQuery();

