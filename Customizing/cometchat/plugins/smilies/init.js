<?php
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($smilies_language as $i => $l) {
			$smilies_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccsmilies = (function () {

		var title = '<?php echo $smilies_language[0];?>';
		var height = <?php echo $smlHeight;?>;
		var width = <?php echo $smlWidth;?>;

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(chatroommode == 1){
					baseUrl = $.cometchat.getBaseUrl();
					basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/smilies/index.php?chatroommode=1&id='+id+'&basedata='+basedata, 'smilies',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0,width="+width+",height="+height,width,height,'<?php echo $smilies_language[1];?>',null,null,null,null,windowMode);
				} else {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/smilies/index.php?id='+id+'&basedata='+baseData, 'smilies',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height,'<?php echo $smilies_language[1];?>',null,null,null,null,windowMode);
				}
			},

			addtext: function (params) {
				var string = '';
				var id = params.to;
				var text = params.pattern;
				var chatroommode = params.chatroommode;
				if(chatroommode == 1) {
                    var currentroom_textarea = $('#currentroom').find('textarea.cometchat_textarea');
                    currentroom_textarea.focus();
					string = currentroom_textarea.val();
					if (string.charAt(string.length-1) == ' ') {
						currentroom_textarea.val(currentroom_textarea.val()+text);
					} else {
						if (string.length == 0) {
							currentroom_textarea.val(text);
						} else {
							currentroom_textarea.val(currentroom_textarea.val()+' '+text);
						}
					}
				} else {
					if($('#cometchat_user_'+id+'_popup').length > 0) {
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						cometchat_user_textarea.focus();
						jqcc.cometchat.chatWith(id);
						string = cometchat_user_textarea.val();

						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}
					} else {
						jqcc.cometchat.chatWith(id);
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						cometchat_user_textarea.focus();
						string = cometchat_user_textarea.val();

						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}
					}
				}
			}
        };
    })();

})(jqcc);
