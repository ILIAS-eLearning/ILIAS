<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($report_language as $i => $l) {
			$report_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccreport = (function () {

		var title = '<?php echo $report_language[0];?>';

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var mode = params.mode;
				if(typeof(mode) !== "undefined" && mode !== 'mobilewebapp') {
					chatroommode = mode;
				}

				if ($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").html() != '') {
					baseData = $.cometchat.getBaseData();
					baseUrl = $.cometchat.getBaseUrl();
					if (mode === 'mobilewebapp') {
						window.open(baseUrl+'plugins/report/index.php?id='+id+'&basedata='+baseData+'&callback=mobilewebapp');
					} else {
						loadCCPopup(baseUrl+'plugins/report/index.php?id='+id+'&basedata='+baseData, 'report',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $report_language[1];?>');
					}
				} else {
					alert('<?php echo $report_language[5];?>');
				}

			}

        };
    })();

})(jqcc);