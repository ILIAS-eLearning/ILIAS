<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($transliterate_language as $i => $l) {
	$transliterate_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
	* CometChat
	* Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

 (function($){
 	$.cctransliterate = (function () {
 		var title = '<?php echo $transliterate_language[0];?>';
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
 				if(chatroommode == 1) {
	 				baseUrl = $.cometchat.getBaseUrl();
	 				basedata = $.cometchat.getBaseData();
	 				loadCCPopup(baseUrl+'plugins/transliterate/index.php?chatroommode=1&id='+id+'&basedata='+basedata, 'transliterate',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $transliterate_language[0];?>',null,null,null,null,windowMode);
 				} else {
	 				baseUrl = $.cometchat.getBaseUrl();
	 				baseData = $.cometchat.getBaseData();
	 				loadCCPopup(baseUrl+'plugins/transliterate/index.php?id='+id+'&basedata='+baseData, 'transliterate',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $transliterate_language[0];?>',null,null,null,null,windowMode);
 				}
 			},

 			setTitle: function(params) {
 				var lang = params.lang;
 				if(typeof(params.formatLang) == 'undefined'){
					$(document.getElementById("cometchat_container_transliterate")).find( ".cometchat_container_title span" ).html(lang);
 				} else {
 					$(document.getElementById("cometchat_container_transliterate")).find( ".cometchat_container_title span" ).html(lang + ' : ' + params.formatLang);
 				}
 			},

 			appendMessage: function(params) {
 				var to = params.to;
 				var data = params.data;
 				var chatroommode = params.chatroommode;
 				var e = {'keyCode':13, 'shiftKey':0};
 				if(chatroommode == 1){
 					if(jqcc('#currentroom').length != 0) {
 						jqcc('#currentroom .cometchat_textarea').focus();
	 					jqcc('#currentroom .cometchat_textarea').val(data);
						jqcc('#currentroom .cometchat_tabcontentsubmit').click();
					}
 				} else {
 					if(jqcc('#cometchat_user_'+to+'_popup').length != 0) {
 						var theme = jqcc.cometchat.getSettings().theme;
 						jqcc('#cometchat_user_'+to+'_popup .cometchat_textarea').focus();
 						jqcc('#cometchat_user_'+to+'_popup .cometchat_textarea').val(data);
 						jqcc[theme].chatboxKeydown(e,'#cometchat_user_'+to+'_popup .cometchat_textarea',to);
					}
 				}
 			}

 		};
 	})();
 })(jqcc);