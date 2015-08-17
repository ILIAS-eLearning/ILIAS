<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($clearconversation_language as $i => $l) {
	$clearconversation_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */

 (function($){

 	$.ccclearconversation = (function () {

 		var title = '<?php echo $clearconversation_language[0];?>';

 		return {

 			getTitle: function() {
 				return title;
 			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
 				if(chatroommode == 1) {
 					if ($("#currentroom_convotext").html() != '') {
 						baseUrl = $.cometchat.getBaseUrl();
 						basedata = $.cometchat.getBaseData();
						var currentroom = params.roomid;
 						$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&basedata='+basedata+'&chatroommode=1&callback=?', {clearid: currentroom});
 						$("#currentroom_convotext").html('');
 					}
 				} else {
 					if ($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").html() != '') {
 						baseUrl = $.cometchat.getBaseUrl();
 						baseData = $.cometchat.getBaseData();
 						$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&callback=?', {clearid: id, basedata: baseData});
 						if($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").length == 0){
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_messagebox").remove();
 						}else{
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > table.cometchat_iphone").remove();
 						}
 					}
 				}
 			}

 		};
 	})();

 })(jqcc);