<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($games_language as $i => $l) {
			$games_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccgames = (function () {

		var title = '<?php echo $games_language[0];?>';
		var lastcall = 0;

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
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				loadCCPopup(baseUrl+'plugins/games/index.php?id='+id+'&basedata='+baseData, 'games_init',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=440,height=260",430,205,'<?php echo $games_language[2];?>',null,null,null,null,windowMode);
			},

			accept: function (params) {
				id = params.to;
				fid = params.rFrom;
				tid = params.rTo;
				rid = params.rOrder;
				gameId = params.gameId;
				gameWidth = params.gameWidth;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
                $.getJSON(baseUrl+'plugins/games/index.php?action=accept&callback=?', {to: id,fid: fid,tid: tid, rid: rid, gameId: gameId, gameWidth: gameWidth, basedata: baseData});
				loadCCPopup(baseUrl+'plugins/games/index.php?action=play&fid='+fid+'&tid='+tid+'&rid='+rid+'&gameId='+gameId+'&basedata='+baseData, 'games'+fid+''+tid,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(gameWidth-30)+",height=600",gameWidth-28,600,'<?php echo $games_language[12];?>',1,null,null,null,windowMode);
			},

			accept_fid: function (params) {
				id = params.to;
				fid = params.rFrom;
				tid = params.rTo;
				rid = params.rOrder;
				gameId = params.gameId;
				gameWidth = params.gameWidth;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				loadCCPopup(baseUrl+'plugins/games/index.php?action=play&fid='+fid+'&tid='+tid+'&rid='+rid+'&gameId='+gameId+'&basedata='+baseData, 'games'+fid+''+tid,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(gameWidth-30)+",height=600",gameWidth-28,600,'<?php echo $games_language[12];?>',1,null,null,null,windowMode);
			}

        };
    })();

})(jqcc);

jqcc(document).ready(function(){
	jqcc('.gameAccept').live('click',function(){
		var to = jqcc(this).attr('to');
		var rFrom = jqcc(this).attr('rFrom');
		var rTo = jqcc(this).attr('rTo');
		var rOrder = jqcc(this).attr('rOrder');
		var gameId = jqcc(this).attr('gameId');
		var gameWidth = jqcc(this).attr('gameWidth');
		if(typeof(parent) != 'undefined' && parent != null && parent != self){
			var controlparameters = {"type":"plugins", "name":"ccgames", "method":"accept", "params":{"to":to, "rFrom":rFrom, "rTo":rTo, "rOrder":rOrder, "gameId":gameId, "gameWidth":gameWidth}};
			controlparameters = JSON.stringify(controlparameters);
			if(typeof(parent) != 'undefined' && parent != null && parent != self){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			} else {
				window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
			}
		} else {
			var controlparameters = {"to":to, "rFrom":rFrom, "rTo":rTo, "rOrder":rOrder, "gameId":gameId, "gameWidth":gameWidth};
            jqcc.ccgames.accept(controlparameters);
		}
	});

	jqcc('.gameAccept_fid').live('click',function(){
		var to = jqcc(this).attr('to');
		var rFrom = jqcc(this).attr('rFrom');
		var rTo = jqcc(this).attr('rTo');
		var rOrder = jqcc(this).attr('rOrder');
		var gameId = jqcc(this).attr('gameId');
		var gameWidth = jqcc(this).attr('gameWidth');
		if(typeof(parent) != 'undefined' && parent != null && parent != self){
			var controlparameters = {"type":"plugins", "name":"ccgames", "method":"accept_fid", "params":{"to":to, "rFrom":rFrom, "rTo":rTo, "rOrder":rOrder, "gameId":gameId, "gameWidth":gameWidth}};
			controlparameters = JSON.stringify(controlparameters);
			if(typeof(parent) != 'undefined' && parent != null && parent != self){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			} else {
				window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
			}
		} else {
			var controlparameters = {"to":to, "rFrom":rFrom, "rTo":rTo, "rOrder":rOrder, "gameId":gameId, "gameWidth":gameWidth};
            jqcc.ccgames.accept_fid(controlparameters);
		}
	});
});