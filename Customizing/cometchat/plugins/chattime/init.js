<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($chattime_language as $i => $l) {
	$chattime_language[$i] = str_replace("'", "\'", $l);
}

?>

/*
		* CometChat
		* Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccchattime = (function () {
		var title = '<?php echo $chattime_language[0];?>';
		var enabled=new Array();
		return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				if(chatroommode == 1) {
					var currentroom = $("#currentroom");
					if (currentroom.find("span.cometchat_ts").css('display') == 'none') {
						currentroom.find("span.cometchat_ts").css('display','inline');
						enabled=1;
					} else {
						currentroom.find("span.cometchat_ts_date").css('display','none');
						currentroom.find("span.cometchat_ts").css('display','none');
						enabled=0;
					}

				} else {
					var cometchat_user_popup = $("#cometchat_user_"+id+"_popup");
					if (cometchat_user_popup.find("span.cometchat_ts").css('display') == 'none') {
						cometchat_user_popup.find("span.cometchat_ts").css('display','inline');
						enabled[id]=1;
					} else {
						cometchat_user_popup.find("span.cometchat_ts_date").css('display','none');
						cometchat_user_popup.find("span.cometchat_ts").css('display','none');
						enabled[id]=0;
					}
				}
			},

			getEnabled:function (id,chatroommode){
				if(chatroommode == 1) {
					if(typeof(enabled)=='undefined'){
						return 0;
					}
					return enabled;
				} else {
					if(typeof(enabled[id])=='undefined'){
						return 0;
					}
					return enabled[id];
				}

			}

		};
	})();
})(jqcc);