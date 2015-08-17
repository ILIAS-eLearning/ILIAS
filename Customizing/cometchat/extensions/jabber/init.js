<?php
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
 }
 foreach($jabber_language as $i => $l){
    $jabber_language[$i] = str_replace("'", "\'", $l);
 }
 include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
 $connectPhrase = $jabber_language[0].''.$jabber_language[16];
 ?>
/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */

(function($){
    $.ccjabber = (function(){
        var session = '';
        var logout = {};
        logout['gtalk'] = '<?php echo $jabber_language[13];?><?php echo $jabber_language[16];?>';
        var ccjabber = [];
        ccjabber.title = 'Jabber Extension';
        ccjabber.hash = '';
        ccjabber.messageTimer = 0;
        ccjabber.friendsTimer = 0;
        ccjabber.minHeartbeat = 3000;
        ccjabber.maxHeartbeat = 30000;
        ccjabber.heartbeatTime = 3000;
        ccjabber.heartbeatCount = 1;
        ccjabber.login = '<?php echo $connectPhrase;?><div style="clear:both"></div>';
        ccjabber.theme = '<?php echo $theme;?>';
        ccjabber.crossDomain = '<?php echo CROSS_DOMAIN;?>';
        ccjabber.server = '<?php echo $cometchatServer;?>j';
        return {
            getTitle: function(){
                return title;
            },
            init: function(){
                $[ccjabber.theme].jabberInit();
            },
            login: function(){
                hash = '';
                baseUrl = $.cometchat.getBaseUrl();
                baseData = $.cometchat.getBaseData();
                baseDomain = document.domain;
                var controlparameters = {"type":"extensions", "name":"core", "method":"loadCCPopup", "params":{"url": baseUrl+"extensions/jabber/index.php?basedata="+baseData+"&basedomain="+baseDomain, "name":"jabber", "properties":"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=210,height=100", "width":"210", "height":"100", "title":"<?php echo $connectPhrase;?>", "force":null, "allowmaximize":null, "allowresize":null, "allowpopout":null, "windowMode":null}};
                controlparameters = JSON.stringify(controlparameters);
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            },
            logout: function(){
                $[ccjabber.theme].jabberLogout();
                jqcc.cookie('cc_jabber', 'false', {path: '/'});
            },
            process: function(){
                session = ';jsessionid='+$.cookie('cc_jabber_id');
                $[ccjabber.theme].jabberProcess();
            },
            sendMessage: function(id, message){
                var currenttime = new Date();
                currenttime = parseInt(currenttime.getTime());
                $[ccjabber.theme].addMessages([{"from": id, "message": message, "self": 1, "old": 0, "id": currenttime, "selfadded": 1, "sent": null}]);
                id = jqcc.ccjabber.decodeName(id);
                $.getJSON(ccjabber.server+session+"?json_callback=?", {
                    'action': 'sendMessage',
                    to: id,
                    msg: message
                }, function(data){
                    ccjabber.heartbeatCount = 1;
                    if(ccjabber.heartbeatTime>ccjabber.minHeartbeat){
                        ccjabber.heartbeatCount = 1;
                        clearTimeout(ccjabber.messageTimer);
                        ccjabber.heartbeatTime = ccjabber.minHeartbeat;
                        ccjabber.messageTimer = setTimeout(function(){
                            jqcc.ccjabber.getMessages();
                        }, ccjabber.minHeartbeat);
                    }
                });
            },
            getRecentData: function(id){
                var originalid = id;
                id = jqcc.ccjabber.decodeName(id);
                $.getJSON(ccjabber.server+session+"?json_callback=?", {
                    'action': 'getAllMessages',
                    user: id
                }, function(data){
                    if(data){
                        jqcc[ccjabber.theme].getRecentDataAjaxSuccess(data, id, originalid);
                    }
                });
            },
            getMessages: function(){
                $.ajax({
                    url: ccjabber.server+session+"?json_callback=?",
                    data: {
                        'action': 'getRecentMessages'
                    },
                    dataType: 'jsonp',
                    timeout: 6000,
                    error: function(){
                        clearTimeout(ccjabber.messageTimer);
                        ccjabber.messageTimer = setTimeout(function(){
                            jqcc.ccjabber.getMessages();
                        }, ccjabber.heartbeatTime);
                    },
                    success: function(data){
                        if(data){
                            if(data[0]&&data[0].error=='1'){
                                jqcc.ccjabber.logout();
                            }else{
                                $.each(data, function(id, message){
                                    message.from = jqcc.ccjabber.encodeName(message.from);
                                    $[ccjabber.theme].addMessages([{"from": message.from, "message": message.msg, "self": 0, "old": 0, "id": message.time, "selfadded": 0, "sent": null}]);
                                    ccjabber.heartbeatTime = ccjabber.minHeartbeat;
                                });
                                ccjabber.heartbeatCount++;
                                if(ccjabber.heartbeatTime!=ccjabber.maxHeartbeat){
                                    if(ccjabber.heartbeatCount>4){
                                        ccjabber.heartbeatTime *= 2;
                                        ccjabber.heartbeatCount = 1;
                                    }
                                    if(ccjabber.heartbeatTime>ccjabber.maxHeartbeat){
                                        ccjabber.heartbeatTime = ccjabber.maxHeartbeat;
                                    }
                                }else{
                                    if(ccjabber.heartbeatCount>30){
                                        jqcc.ccjabber.logout();
                                    }
                                }
                                clearTimeout(ccjabber.messageTimer);
                                ccjabber.messageTimer = setTimeout(function(){
                                    jqcc.ccjabber.getMessages();
                                }, ccjabber.heartbeatTime);
                            }
                        }
                    }
                });
            },
            getFriendsList: function(first){
                jqcc[ccjabber.theme].jabberGetFriendsList(first);
            },
            getFriendsListAjax: function(first){
                $.ajax({
                    url: ccjabber.server+session+"?json_callback=?",
                    data: {
                        'action': 'getOnlineBuddies',
                        md5: hash
                    },
                    dataType: "json",
                    type: "GET",
                    async: true,
                    success: function(data){
                        jqcc[ccjabber.theme].getFriendsListAjaxSuccess(data, first);
                    }
                });
            },
            encodeName: function(name){
                name = name.toLowerCase();
                name = name.replace('-', 'M');
                name = name.replace('@', 'A');
                name = name.replace(/\./g, 'D');
                return name;
            },
            decodeName: function(name){
                name = name.replace('M', '-');
                name = name.replace('A', '@');
                name = name.replace(/D/g, '\.');
                return name;
            },
            getJabberVariableLogout: function(name){
                return logout[name];
            },
            jabberLogout: function(){
                $.getJSON(ccjabber.server+session+"?json_callback=?", {'action': 'logout'});
            },
            getJabberVariableTheme: function(){
                return ccjabber.theme;
            },
            getCcjabberVariable: function(){
                return ccjabber;
            }
        };
    })();
})(jqcc);

 <?php
 if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.'.js')){
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.'.js');
 }else{
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.'standard'.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.'standard.js');
}
 ?>