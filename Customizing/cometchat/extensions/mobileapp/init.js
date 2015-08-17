<?php
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
                
?>
/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){   
        var pushNotifications = '<?php echo $pushNotifications;?>';
        
	$.ccmobilenativeapp = (function () {
			return {
				sendnotification: function (message, channel, displayname, chatroommode) {
					if (typeof chatroommode == 'undefined' && pushNotifications == '1') { 
						baseUrl = $.cometchat.getBaseUrl();
						$.getJSON(baseUrl+'extensions/mobileapp/sendnotification.php', {channel: channel, message: message, displayname: displayname});
					} else if (pushNotifications == '1') {
						baseUrl = $.cometchat.getBaseUrl();
						$.getJSON(baseUrl+'extensions/mobileapp/sendnotification.php?chatroommode=1', {channel: channel, message: message, displayname: displayname});
					}
				}
			};
    })();
 
})(jqcc);