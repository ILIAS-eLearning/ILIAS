<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($style_language as $i => $l) {
			$style_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccstyle = (function () {

		var title = '<?php echo $style_language[0];?>';
		var chatroommode = 0;

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				baseUrl = $.cometchat.getBaseUrl();
				basedata = $.cometchat.getBaseData();
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				loadCCPopup(baseUrl+'plugins/style/index.php?id='+id+'&basedata='+basedata, 'style',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=260,height=130",260,80,'<?php echo $style_language[1];?>',null,null,null,null,windowMode);
			},

			updatecolor: function (params) {
				var text = params.pattern;
				if (text != '' && text != null) {
					document.cookie = '<?php echo $cookiePrefix;?>chatroomcolor='+text+'; path=/';
				}
				$('#currentroom').find("textarea.cometchat_textarea").focus();

			}

        };
    })();

})(jqcc);