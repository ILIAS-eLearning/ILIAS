/*
*   CometChat
*   Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
    include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
foreach ($chathistory_language as $i => $l) {
    $chathistory_language[$i] = str_replace("'", "\'", $l);
}

?>

function getTimeDisplay(ts) {
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var ap = "";
    var hour = ts.getHours();
    var minute = ts.getMinutes();
    var todaysDate = new Date();
    var todays12am = todaysDate.getTime() - (todaysDate.getTime()%(24*60*60*1000));
    var date = ts.getDate();
    var month = ts.getMonth();
    var armyTime = '<?php echo $armyTime;?>';
    if (armyTime != 1) {
        ap = hour>11 ? "pm" : "am";
        hour = hour==0 ? 12 : hour>12 ? hour-12 : hour;
    } else {
        hour = hour<10 ? "0"+hour : hour;
    }
    minute = minute<10 ? "0"+minute : minute;
    var type = 'th';
    if (date == 1 || date == 21 || date == 31) { type = 'st'; }
    else if (date == 2 || date == 22) { type = 'nd'; }
    else if (date == 3 || date == 23) { type = 'rd'; }

    if (ts < todays12am) {
        return hour+":"+minute+ap+' '+date+type+' '+months[month];
    } else {
        return hour+":"+minute+ap;
    }
}

function getChatLog(id, chatroommode, basedata) {
    jqcc.ajax({
        url: "chathistory.php?action=logs",
        data: {history: id, chatroommode: chatroommode, basedata: basedata},
        type: 'post',
        dataType: 'json',
        async: false,
        timeout: 10000,
        success: function(data) {
            if(data != '0') {
                temp = '';
                jqcc.each(data, function(type, item) {
                    if(typeof(window.opener) == 'undefined' || window.opener == null) {
                        var controlparameters = {"type":"plugins", "name":"cometchat", "method":"processcontrolmessage", "item":item, "params":{"chatroommode":chatroommode}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                    } else {
                        var controlparameters = {"type":"plugins", "name":"cometchat", "method":"processcontrolmessage", "item":item, "params":{"chatroommode":chatroommode}};
                        controlparameters = JSON.stringify(controlparameters);
                        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
                    }
                    temp = temp + '<div class="chat" id="'+item.id+'" title="<?php echo $chathistory_language[8];?>"><div class="chatrequest"><b>'+item.from+'</b></div><div class="chatmessage chatmessage_short"></div><div class="chattime" >'+getTimeDisplay(new Date(item.sent))+'</div><div style="clear:both"></div></div>';});
                    jqcc('.container_body_chat').html('<div id ="logs">'+temp+'</div>');
                    jqcc("#logs").slimScroll({height: '460px'}).css({height: '460px',width: 'auto'});
                    jqcc('.chat').click(function() {
                        var range = jqcc(this).attr('id');
                        getChatLogView(id, range, chatroommode, basedata);
                    });
                } else {
                    jqcc('.container_body_chat').html(norecords);
                }
            }, error: function(data) {
        }
    });
}

function getChatLogView(id, range,chatroommode, basedata) {
    var temp = '<div class="chatbar"><div class="chatbar_1"></div><div class="chatbar_2"><a href="index.php?chatroommode='+chatroommode+'&amp;logs=1&amp;history='+id+'&amp;basedata='+basedata+'&amp;embed=web"><?php echo $chathistory_language[5];?></a></div><div style="clear:both"></div></div><div class="chatbar_body" id="chat_body"></div><div class="chathistory" title="<?php echo $chathistory_language[10];?>"><span><?php echo $chathistory_language[10];?></span><span class="arrowdown"></span></div>';
    jqcc('.container_body_chat').html(temp);
    jqcc(".chatbar_body").slimScroll({height: '410px'}).css({height: '410px', width: 'auto'});
    getMessage(id, range,chatroommode, basedata);
}

function getMessage(id, range,chatroommode, basedata, lastmessageid) {
    var previousid = 0;
    var name = '';
    var time = '';
    var count = 0;
    var temp = '';
    if(typeof(lastmessageid) == 'undefined') {
        lastmessageid = 0;
    }
    jqcc.ajax({
        url: "chathistory.php?action=logview",
        data: {history: id, range: range, chatroommode: chatroommode, basedata:basedata, lastidfrom: lastmessageid},
        type: 'post',
        dataType: 'json',
        async: true,
        timeout: 10000,
        success: function(data) {
            var i = 0;
            jqcc.each(data, function(type, item){
                if(typeof(window.opener) == 'undefined' || window.opener == null) {
                    var controlparameters = {"type":"plugins", "name":"cometchat", "method":"processcontrolmessage", "item":item, "params":{"chatroommode":chatroommode}};
                    controlparameters = JSON.stringify(controlparameters);
                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                } else {
                    var controlparameters = {"type":"plugins", "name":"cometchat", "method":"processcontrolmessage", "item":item, "params":{"chatroommode":chatroommode}};
                    controlparameters = JSON.stringify(controlparameters);
                    window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
                temp += '<div class="chat chatnoline" id="'+item.id+'"><div class="chatrequest"><b>'+item.from+'</b></div><div class="chatmessage chatnowrap"></div><div class="chattime">'+getTimeDisplay(new Date(item.sent))+'</div><div style="clear:both"></div></div>';
                previousid = item.previd;
                name = item.requester;
                time = item.sent;
                i++;
                userid = item.userid;
            });
            jqcc('#chat_body').append(temp);
            count = jqcc('#messageCount').text();
            if(count == '') {
                count = i;
            } else {
                count = parseInt(count)+parseInt(i);
            }
            if(time!=''){
                jqcc('div.chatbar_1').html('<?php echo $chathistory_language[2];?> '+ name +' <?php echo $chathistory_language[4];?> <small class="chattimedate">'+getTimeDisplay(new Date(time))+'</small> (<span id="messageCount">'+count+'</span> <?php echo $chathistory_language[3];?>)');
            }
            jqcc( "div.chathistory" ).click(function() {
                var lastidfrom = parseInt(jqcc('div.chatnoline:last').attr('id'))+1;
                getMessage(id,lastidfrom+'|'+previousid,chatroommode,basedata,userid);
                jqcc( "div.chathistory" ).unbind('click');
            });


            var cometchat_chathistory = jqcc('div.chatbar_body');
            cometchat_chathistory.scrollTop(
                cometchat_chathistory[0].scrollHeight - cometchat_chathistory.height()
                );

            if(i < 13) {
                jqcc( "div.chathistory" ).unbind('click').html(norecords).attr('title', ' <?php echo $chathistory_language[9];?>');
            }
        }, error: function(data) {}
    });
}