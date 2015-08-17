<?php

/*

CometChat
Copyright (c) 2014 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts
retains ownership of the Software and any copies of it, regardless of the
form in which the copies may exist. This license is not a sale of the
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each
installed instance of the Software, a separate license is required.
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts.

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form.

The Software source code may be altered (at your risk)

All Software copyright notices within the scripts must remain unchanged (and visible).

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to,
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets,
software piracy, and patents held by individuals, corporations, or other entities.

If any of the terms of this Agreement are violated, Inscripts reserves the right
to revoke the Software license at any time.

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/


?>

if (typeof(jqcc) === 'undefined') {
	jqcc = jQuery;
}
(function($) {
    var settings = {};
    settings = jqcc.cometchat.getcrAllVariables();
    var calleeAPI = jqcc.cometchat.getChatroomVars('calleeAPI');

    $.crfacebook = (function() {
            return {
                playsound: function() {
                        try	{
                            document.getElementById('messageBeep').play();
                        } catch (error) {
                            jqcc.cometchat.setChatroomVars('messageBeep',0);
                        }
                },
                sendChatroomMessage: function(chatboxtextarea) {
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height','18px');
                    var height = $[calleeAPI].crgetWindowHeight();
                    var contentDivHeight = height-parseInt($('.topbar').css('height'));
                    $("div.content_div").css('height',contentDivHeight);
                    var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                    $("#currentroom_convo").css('height',contentDivHeight-textareaHeight);
                    $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                    $(chatboxtextarea).css('overflow-y','hidden');
                    $(chatboxtextarea).focus();
                },
                createChatroom: function() {
                    $[calleeAPI].hidetabs();
                    $('#createtab').addClass('tab_selected');
                    $('#create').css('display','block');
                    $('.welcomemessage').html('<?php echo $chatrooms_language[5];?>');
                },
                getTimeDisplay: function(ts,id) {
                    var style ="style=\"display:none;\"";

                    if (typeof(jqcc.ccchattime)!='undefined' && jqcc.ccchattime.getEnabled(id,1)) {
                            style="style=\"display:inline-block;\"";
                    }
                    var time = getTimeDisplay(ts);
					if (ts < jqcc.cometchat.getChatroomVars('todays12am')) {
                            return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+" "+time.date+time.type+" "+time.month+")</span>";
                    } else {
                            return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+")</span>";
                    }
                },
                addChatroomMessage: function(fromid,incomingmessage,incomingid,selfadded,sent,fromname) {
                    if(typeof(fromname) === 'undefined' || fromname == 0 || fromid == settings.myid){
                        fromname = '<?php echo $chatrooms_language[6]; ?>';
                    }
                    var temp = '';
                    var controlparameters = {};
                    settings.timestamp=incomingid;
                    separator = '<?php echo $chatrooms_language[7]; ?>';
                    if(incomingmessage.indexOf('CC^CONTROL_') != -1){
                        var bannedOrKicked = incomingmessage.split('_');
                        var controlparameters = {"type":"modules", "name":"chatroom", "method":bannedOrKicked[1], "params":{"id":bannedOrKicked[2]}};
                    }
                    if (typeof(controlparameters.method) != 'undefined' && (controlparameters.method =='kicked' || controlparameters.method == 'banned')) {
                        if (settings.myid == controlparameters.params.id) {
                            if (controlparameters.method=='kicked') {
                                jqcc.cometchat.kickChatroomUser(controlparameters.method,incomingid);
                                alert ('<?php echo $chatrooms_language[36];?>');
                                jqcc.cometchat.leaveChatroom();
                            }
                            if (controlparameters.method=='banned') {
                                jqcc.cometchat.banChatroomUser(controlparameters.method,incomingid);
                                alert ('<?php echo $chatrooms_language[37];?>');
                                jqcc.cometchat.leaveChatroom(controlparameters.params.id, 1);
                            }
                        }
                    $("#cometchat_userlist_"+controlparameters.params.id).remove();
                    }  else if(typeof(controlparameters.method) != 'undefined' && controlparameters.method == "deletemessage") {
                        $("#cometchat_message_"+controlparameters.params.id).remove();
                    } else {
                        if ($("#cometchat_message_"+incomingid).length > 0) {
                            var processedmessage = incomingmessage;
                            if(controlparameters.length > 0){
                                processedmessage = jqcc.cometchat.processcontrolmessage(controlparameters);
                            }
                            $("#cometchat_message_"+incomingid).find("span.cometchat_chatboxmessagecontent").html(processedmessage);
                        } else {
                            if (typeof(controlparameters.method) == 'undefined' || controlparameters.method != 'deletemessage') {
                                sentdata = '';
                                if (sent != null) {
                                    var ts = new Date(parseInt(sent));
                                    sentdata = $[calleeAPI].getTimeDisplay(ts,incomingid);
                                }
                                if (!settings.fullName && fromname.indexOf(" ") != -1) {
                                    fromname = fromname.slice(0,fromname.indexOf(" "));
                                }
                                if (fromid != settings.myid) {
                                    temp += ('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incomingid+'"><span class="cometchat_chatboxmessagefrom"><strong>');
                                    if (settings.apiAccess && fromid != 0) {
                                        temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');">');
                                    }
                                    temp += fromname;
                                    if (settings.apiAccess && fromid != 0) {
                                        temp += ('</a>');
                                    }
                                    temp += ('</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent">'+incomingmessage+'</span>'+sentdata+'</div>');
                                } else {
                                    temp += ('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incomingid+'"><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+'</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent">'+incomingmessage+'</span>'+sentdata+'</div>');
                                }
                                $("#currentroom_convotext").append(temp);
                                if ($.cookie(jqcc.cometchat.getChatroomVars('cookiePrefix')+"sound") && $.cookie(jqcc.cometchat.getChatroomVars('cookiePrefix')+"sound") == 'true') { } else {
                                        $[calleeAPI].playsound();
                                }
                            }
                        }
                    }
                    if(jqcc.cometchat.getChatroomVars('owner')|| jqcc.cometchat.getChatroomVars('isModerator') || (jqcc.cometchat.getChatroomVars('allowDelete') == 1 && fromid == settings.myid)) {
                        if ($("#cometchat_message_"+incomingid).find(".delete_msg").length < 1) {
                                jqcc('#cometchat_message_'+incomingid).find('.cometchat_ts').after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incomingid+'\');">(<span class="hoverbraces"><?php echo $chatrooms_language[46]; ?></span>)</span>');
                        }
                        $(".cometchat_chatboxmessage").live("mouseover",function() {
                            $(this).find(".delete_msg").css('display','inline-block');
                        });
                        $(".cometchat_chatboxmessage").live("mouseout",function() {
                            $(this).find(".delete_msg").css('display','none');
                        });
                        $(".delete_msg").mouseover(function() {
                            $(this).css('display','inline-block');
                        });
                    }
                    var forced = (fromid == settings.myid) ? 1 : 0;
                    $[calleeAPI].chatroomScrollDown(forced);
                    if (settings.apiAccess == 1 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                        parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                    }
                },
                chatroomBoxKeyup: function(event,chatboxtextarea) {
                    var adjustedHeight = chatboxtextarea.clientHeight;
                    var maxHeight = 94;
                    var height = $[calleeAPI].crgetWindowHeight();

                    if (maxHeight > adjustedHeight) {
                        adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                        if (maxHeight)
                            adjustedHeight = Math.min(maxHeight, adjustedHeight);
                        if (adjustedHeight > chatboxtextarea.clientHeight) {
                            $(chatboxtextarea).css('height',adjustedHeight+6 +'px');
                            var contentDivHeight = height-parseInt($('.topbar').css('height'));
                            $("div.content_div").css('height',contentDivHeight);
                            var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                            $("#currentroom_convo").css('height',contentDivHeight-textareaHeight);
                            $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                            $[calleeAPI].chatroomScrollDown(1);
                        }
                    } else {
                        $(chatboxtextarea).css('overflow-y','auto');
                    }
                },
                hidetabs: function() {
                    $('li').removeClass('tab_selected');
                    $('#lobby').css('display','none');
                    $('#currentroom').css('display','none');
                    $('#create').css('display','none');
                    $('#plugins').css('display','none');
                },
                loadLobby: function() {
                    $[calleeAPI].hidetabs();
                    $('#lobbytab').addClass('tab_selected');
                    $('#lobby').css('display','block');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[1];?>');
                    clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                    jqcc.cometchat.chatroomHeartbeat(1);
                },
                crcheckDropDown: function(dropdown) {
                    var id = $('#type').attr("selectedIndex");
                    if (id == 1) {
                        $('div.password_hide').css('display','block');
                    } else {
                        $('div.password_hide').css('display','none');
                    }
                },
                loadRoom: function() {
                    var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                    var roomno = jqcc.cometchat.getChatroomVars('currentroom');

                    $[calleeAPI].hidetabs();
                    $('#plugins').css('display','block');
                    $('#currentroom').css('display','block');
                    $('#currentroomtab').css('display','block').addClass('tab_selected');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[4];?>'+'<span> | </span>'+'<?php echo $chatrooms_language[48];?>'+'<?php echo $chatrooms_language[39];?>');
                    document.cookie = '<?php echo $cookiePrefix;?>chatroom='+urlencode(roomno+':'+jqcc.cometchat.getChatroomVars('currentp')+':'+urlencode(roomname));
                    if ($('#currentroomtab').find('a').attr('show')==0) {
                        $('#unbanuser').remove();
                    }
                    var pluginshtml = '';
                    var plugins = jqcc.cometchat.getChatroomVars('plugins');
                    if (plugins.length > 0) {
                        pluginshtml += '<div class="cometchat_plugins">';
                        for (var i = 0;i < plugins.length;i++) {
                            var name = 'cc'+plugins[i];
                            if (typeof($[name]) == 'object') {
                                pluginshtml += '<div class="cometchat_pluginsicon cometchat_'+ settings.plugins[i] + '" title="' + $[name].getTitle() + '" name="'+name+'" to="'+roomno+'" chatroommode="1"></div>';
                            }
                        }
                        pluginshtml += '</div>';
                    }
                    $('#plugins').html(pluginshtml);
                    $('.cometchat_pluginsicon').click(function(){
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
                        if(typeof(parent) != 'undefined' && parent != null && parent != self && name != 'ccsave' && name != 'ccclearconversation' && name != 'ccchattime'){
                            var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                            jqcc[name].init(controlparameters);
                        }
                    });
                    $[calleeAPI].chatroomWindowResize();
                },
                chatroomWindowResize: function() {
                    var height = $[calleeAPI].crgetWindowHeight();
                    var contentDivHeight = height-parseInt($('.topbar').css('height'));
                    $("div.content_div").css('height',contentDivHeight);
                    var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                    $("#currentroom_convo").css('height',contentDivHeight-textareaHeight);

                    var width = $[calleeAPI].crgetWindowWidth();
                    $('#currentroom_left').css('width',width-144-48);
                    $('textarea.cometchat_textarea').css('width',width-174-48);

                    if (jqcc().slimScroll) {
                        $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                        $("#currentroom_right").find("div.slimScrollDiv").css('height',$("#currentroom_right").css('height'));
                    }
                },
                kickid: function(kickid) {
                    $("#chatroom_userlist_"+kickid).remove();
                },
                banid: function(banid) {
                    $("#chatroom_userlist_"+banid).remove();
                },
                chatroomScrollDown: function(forced) {
                	if(settings.newMessageIndicator == 1 && ($('#currentroom_convotext').outerHeight(false)+$('#currentroom_convotext').offset().top-$('#currentroom_convo').height()-$('#currentroom_convo').offset().top-$('.cometchat_chatboxmessage').height()-$('.cometchat_chatboxmessage').height()>0)){
                        if(($('#currentroom_convo').height()-$('#currentroom_convotext').outerHeight(false)) < 0){
                        	if(forced) {
    	                        if (jqcc().slimScroll) {
    	                            $('#currentroom_convo').slimScroll({scroll: '1'});
    	                        } else {
    	                            setTimeout(function() {
    	                            $("#currentroom_convo").scrollTop(50000);
    	                            },100);
    	                        }
    	                        if($('.talkindicator').length!=0){
	                            $('.talkindicator').fadeOut();
                                }
    	                    }else{
                                if($('.talkindicator').length!=0){
                                    $('.talkindicator').fadeIn();
                                }else{
                                    var indicator = "<a class='talkindicator' href='#'><?php echo $chatrooms_language[52];?></a>";
                                    $('#currentroom_convo').append(indicator);
                                    $('.talkindicator').click(function(e) {
                                        e.preventDefault();
                                        if (jqcc().slimScroll) {
                                            $('#currentroom_convo').slimScroll({scroll: '1'});
                                        } else {
                                            setTimeout(function() {
                                                $("#currentroom_convo").scrollTop(50000);
                                            },100);
                                        }
                                        $('.talkindicator').fadeOut();
                                    });
                                    $('#currentroom_convo').scroll(function(){
                                        if($('#currentroom_convotext').outerHeight(false) + $('#currentroom_convotext').offset().top - $('#currentroom_convo').offset().top <= $('#currentroom_convo').height()){
                                            $('.talkindicator').fadeOut();
                                        }
                                    });
                                }
                        	}
                        }
                    }else{
                        if (jqcc().slimScroll) {
                            $('#currentroom_convo').slimScroll({scroll: '1'});
                        } else {
                            setTimeout(function() {
                                $("#currentroom_convo").scrollTop(50000);
                            },100);
                        }
                    }
                },
                createChatroomSubmitStruct: function() {
                    var string = $('input.create_input').val();
                    var room={};
                    if (($.trim( string )).length == 0) {
                        return false;
                    }
                    var name = document.getElementById('name').value;
                    var type = document.getElementById('type').value;
                    var password = document.getElementById('password').value;
                    if (name != '' && name != null && name != '<?php echo $chatrooms_language[63];?>') {
                        name = name.replace(/^\s+|\s+$/g,"");
                        if (type == 1 && password == '') {
                            alert ('<?php echo $chatrooms_language[26];?>');
                            return false;
                        }
                        if (type == 2) {
                            password = 'i'+(Math.round(new Date().getTime()));
                        }
                        if (type == 0) {
                            password = '';
                        }
                        room['name'] = name;
                        room['password'] = password;
                        room['type'] = type;
                    }else{
                        alert('<?php echo $chatrooms_language[50];?>');
                        return false;
                    }
                    document.getElementById('name').value = '';
                    document.getElementById('password').value = '';
                    return room;
                },
                crgetWindowHeight: function() {
                    var windowHeight = 0;
                    if (typeof(window.innerHeight) == 'number') {
                        windowHeight = window.innerHeight;
                    } else {
                        if (document.documentElement && document.documentElement.clientHeight) {
                            windowHeight = document.documentElement.clientHeight;
                        } else {
                            if (document.body && document.body.clientHeight) {
                                windowHeight = document.body.clientHeight;
                            }
                        }
                    }
                    return windowHeight;
                },
                crgetWindowWidth: function() {
                    var windowWidth = 0;
                    if (typeof(window.innerWidth) == 'number') {
                        windowWidth = window.innerWidth;
                    } else {
                        if (document.documentElement && document.documentElement.clientWidth) {
                            windowWidth = document.documentElement.clientWidth;
                        } else {
                            if (document.body && document.body.clientWidth) {
                                windowWidth = document.body.clientWidth;
                            }
                        }
                    }
                    return windowWidth;
                },
                selectChatroom: function(currentroom,id) {
                    jqcc("#cometchat_userlist_"+currentroom).removeClass("cometchat_chatroomselected");
                    jqcc("#cometchat_userlist_"+id).addClass("cometchat_chatroomselected");
                },
                checkOwnership: function(owner,isModerator,name) {
                    var loadroom = 'javascript:jqcc["'+calleeAPI+'"].loadRoom()';
                   if (owner || isModerator) {
                        jqcc('#currentroomtab').html('<a href="javascript:void(0);" show=1 onclick='+loadroom+'>'+name+'</a>');
                    } else {
                        jqcc('#currentroomtab').html('<a href="javascript:void(0);" show=0 onclick='+loadroom+'>'+name+'</a>');
                    }
                    jqcc('#currentroom_convotext').html('');
                    jqcc("#currentroom_users").html('');
                },
                leaveRoomClass : function(currentroom) {
                    jqcc("#cometchat_userlist_"+currentroom).removeClass("cometchat_chatroomselected");
                },
                removeCurrentRoomTab : function() {
                    jqcc('#currentroomtab').css('display','none');
                },
                chatroomLogout : function() {
                    window.location.reload();
                },
                loadChatroomList : function(item) {
                    var temp = '';
                    var onlineNumber = 0;
                    $.each(item, function(i,room) {
                        longname = room.name;
                        shortname = room.name;

                        if (room.status == 'available') {
                            onlineNumber++;
                        }
                        var selected = '';

                        if (jqcc.cometchat.getChatroomVars('currentroom') == room.id) {
                            selected = ' cometchat_chatroomselected';
                        }
                        roomtype = '';
                        roomowner = '';
                        deleteroom = '';

                        if (room.type != 0) {
                            roomtype = '<?php echo $chatrooms_language[24];?>';
                        }

                        if (room.s == 1) {
                            roomowner = '<?php echo $chatrooms_language[25];?>';
                        }

                        if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                            deleteroom = '<img src="remove.png" />';
                        }

                        if (room.s == 2) {
                            room.s = 1;
                        }

                        temp += '<div id="cometchat_userlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="jQuery(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jQuery(this).removeClass(\'cometchat_userlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\');" ><span class="lobby_room_1">'+longname+'</span><span class="lobby_room_2">'+room.online+' <?php echo $chatrooms_language[34];?></span><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><div style="clear:both"></div></div>';
                    });
                    if (temp != '') {
                        jqcc('#lobby_rooms').html(temp);
                    }else{
                        jqcc('#lobby_rooms').html('<div class="lobby_noroom"><?php echo $chatrooms_language[53]; ?></div>');
                    }
                },
                displayChatroomMessage: function(item,fetchedUsers) {
                    var beepNewMessages = 0;
                    $.each(item, function(i,incoming) {
                        if(incoming.fromid == settings.myid){
                            incoming.from = '<?php echo $chatrooms_language[6];?>';
                        }
                        jqcc.cometchat.setChatroomVars('timestamp',incoming.id);

                        if (incoming.message != '') {
                                var temp = '';
                                var fromname = incoming.from;
                                var bannedKicked = incoming.message;
                                if (incoming.message.indexOf('CC^CONTROL_') != -1) {
                                    var bannedOrKicked = incoming.message.split('_');
                                    var controlparameters = {"type":"modules", "name":"chatroom", "method":bannedOrKicked[1], "params":{"id":bannedOrKicked[2]}};
                                    if (controlparameters.method =='kicked' || controlparameters.method =='banned') {
                                        if (settings.myid==controlparameters.params.id) {
                                            if (controlparameters.method=='kicked') {
                                                jqcc.cometchat.kickChatroomUser(controlparameters.method,incoming.id);
                                                alert ('<?php echo $chatrooms_language[36];?>');
                                                jqcc.cometchat.leaveChatroom();
                                            }
                                            if (controlparameters.method=='banned') {
                                                jqcc.cometchat.banChatroomUser(controlparameters.method,incoming.id);
                                                alert ('<?php echo $chatrooms_language[37];?>');
                                                jqcc.cometchat.leaveChatroom(controlparameters.params.id, 1);
                                            }
                                        }
                                        $("#cometchat_userlist_"+controlparameters.params.id).remove();
                                    } else if (controlparameters.method == "deletemessage") {
                                        $("#cometchat_message_"+controlparameters.params.id).remove();
                                    }
                                } else {
                                    if ($("#cometchat_message_"+incoming.id).length > 0) {
                                        $("#cometchat_message_"+incoming.id).find("span.cometchat_chatboxmessagecontent").html(incoming.message);
                                    } else {
                                        var ts = new Date(parseInt(incoming.sent)*1000);
                                        if (!settings.fullName && fromname.indexOf(" ") != -1) {
                                            fromname = fromname.slice(0,fromname.indexOf(" "));
                                        }
                                        if (incoming.fromid != settings.myid) {
                                            temp += ('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><span class="cometchat_chatboxmessagefrom"><strong>');
                                            if (settings.apiAccess && incoming.fromid != 0) {
                                                temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');">');
                                            }
                                            temp += fromname;
                                            if (settings.apiAccess && incoming.fromid != 0) {
                                                temp += ('</a>');
                                            }
                                            temp += ('</strong>:&nbsp;&nbsp;</span><span class="cometchat_chatboxmessagecontent">'+incoming.message+'</span>'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'</div>');
                                            jqcc.cometchat.setChatroomVars('newMessages',jqcc.cometchat.getChatroomVars('newMessages')+1);
                                            beepNewMessages++;
                                        } else {
                                            temp += ('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+'</strong>:&nbsp;&nbsp;</span><span class="cometchat_chatboxmessagecontent">'+incoming.message+'</span>'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'</div>');
                                        }
                                    }
                                }
                                $('#currentroom_convotext').append(temp);
                                if (jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    if ($("#cometchat_message_"+incoming.id).find(".delete_msg").length < 1) {
                                        jqcc('#cometchat_message_'+incoming.id).find(".cometchat_ts").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');">(<span class="hoverbraces"><?php echo $chatrooms_language[46]; ?></span>)</span>');
                                    }
                                    $(".cometchat_chatboxmessage").live("mouseover",function() {
                                        $(this).find(".delete_msg").css('display','inline-block');
                                    });
                                    $(".cometchat_chatboxmessage").live("mouseout",function() {
                                        $(this).find(".delete_msg").css('display','none');
                                    });
                                    $(".delete_msg").mouseover(function() {
                                        $(this).css('display','inline-block').find(".hoverbraces").css('text-decoration','underline');
                                    });
                                    $(".delete_msg").mouseout(function() {
                                        $(this).find(".hoverbraces").css('text-decoration','none');
                                    });
                                }
                                var forced = (incoming.fromid == settings.myid) ? 1 : 0;
                                $[calleeAPI].chatroomScrollDown(forced);
                            }
                        });
                        jqcc.cometchat.setChatroomVars('heartbeatCount',1);
                        jqcc.cometchat.setChatroomVars('heartbeatTime',settings.minHeartbeat);
                        if (settings.apiAccess == 1 && fetchedUsers == 0 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                            parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                        }
                        if ($.cookie(settings.cookiePrefix+"sound") && $.cookie(settings.cookiePrefix+"sound") == 'true') { } else {
                            if (beepNewMessages > 0 && fetchedUsers == 0) {
                                $[calleeAPI].playsound();
                            }
                        }
                    },
                    silentRoom: function(id, name, silent) {
                        if (settings.lightboxWindows == 1) {
                            loadCCPopup(settings.baseUrl+'modules/chatrooms/chatrooms.php?id='+id+'&basedata='+settings.basedata+'&name='+name+'&silent='+silent+'&action=passwordBox', 'passwordBox',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=320,height=110",320,110,name);
                        } else {
                            var temp = prompt('<?php echo $chatrooms_language[8];?>','');
                            if (temp) {
                                jqcc.cometchat.checkChatroomPass(id,name,silent,temp);
                            } else {
                                return;
                            }
                        }
                    },
                    updateChatroomUsers: function(item,fetchedUsers) {
                        var temp = '';
                        var temp1 = '';
                        var newUsers = {};
                        var newUsersName = {};
                        fetchedUsers = 1;
                        $.each(item, function(i,user) {
                            longname = user.n;
                            if (settings.users[user.id] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                var ts = new Date();
                                $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+user.n+'<?php echo $chatrooms_language[14]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                $[calleeAPI].chatroomScrollDown();
                            }
                            if (parseInt(user.b)!=1) {
                                var avatar = '';
                                if (user.a != '') {
                                    avatar = '<span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src='+user.a+'></span>';
                                }
                                newUsers[user.id] = 1;
                                newUsersName[user.id] = user.n;
                                userhtml='<div class="cometchat_subsubtitleusers"><hr class="hrleft"><?php echo $chatrooms_language[61];?><hr class="hrright"></div>';
                                moderatorhtml='<div class="cometchat_subsubtitle"><hr class="hrleft"><?php echo $chatrooms_language[62];?><hr class="hrright"></div>';
                                if (jQuery.inArray(user.id ,jqcc.cometchat.getChatroomVars('moderators') ) != -1 ) {
                                    if (user.id == settings.myid) {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');" userid='+user.id+' owner='+settings.owner+' username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                } else {
                                    if (user.id == settings.myid) {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');" userid='+user.id+' owner='+settings.owner+' username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                }
                            }
                        });
                        for (user in settings.users) {
                            if (settings.users.hasOwnProperty(user)) {
                                if (newUsers[user] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                    var ts = new Date();
                                    $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+settings.usersName[user]+'<?php echo $chatrooms_language[13]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                    $[calleeAPI].chatroomScrollDown();
                                }
                            }
                        }
                        if(temp1 != "" && temp !="")
                            jqcc('#currentroom_users').html(moderatorhtml+temp1+userhtml+temp);
                        else if(temp == "")
                            jqcc('#currentroom_users').html(moderatorhtml+temp1);
                        else
                            jqcc('#currentroom_users').html(userhtml+temp);
                        jqcc.cometchat.setChatroomVars('users',newUsers);
                        jqcc.cometchat.setChatroomVars('usersName',newUsersName);
                        jqcc.cometchat.setChatroomVars('initializeRoom',0);
                    },
                    loadCCPopup: function(url,name,properties,width,height,title,force,allowmaximize,allowresize,allowpopout){
                        if (jqcc.cometchat.getChatroomVars('lightboxWindows') == 1) {
                            var controlparameters = {"type":"modules", "name":"chatrooms", "method":"loadCCPopup", "params":{"url":url, "name":name, "properties":properties, "width":width, "height":height, "title":title, "force":force, "allowmaximize":allowmaximize, "allowresize":allowresize, "allowpopout":allowpopout}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var w = window.open(url,name,properties);
                            w.focus();
                        }
                    },
                    cometchatroomready: function () {
                        if ((jqcc.cometchat.chatroommessageBeep()) == 1) {
                            $('<audio id="messageBeep" style="display:none;"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.mp3" type="audio/mpeg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.ogg" type="audio/ogg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
                        }
                        try {
                            if (parent.jqcc.cometchat.ping() == 1) {
                                jqcc.cometchat.setChatroomVars('apiAccess',1);
                            }
                        } catch (e) {}
                        if(jqcc.cometchat.getChatroomVars('calleeAPI') !== 'mobilewebapp') {
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomWindowResize();
                        }
                        if (jqcc().slimScroll) {
                            jqcc("#currentroom_convo").slimScroll({height: jqcc("#currentroom_convo").css('height')});
                        }
                        window.onresize = function(event) {
                            if(jqcc.cometchat.getChatroomVars('calleeAPI') !== 'mobilewebapp') {
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomWindowResize();
                            }
                        }
                        jqcc('#currentroom').mouseover(function() {
                            jqcc.cometchat.setChatroomVars('newMessages',0);
                        });
                        jqcc.cometchat.chatroomHeartbeat(1);
                        jqcc("textarea.cometchat_textarea").keydown(function(event) {
                            return jqcc.cometchat.chatroomBoxKeydown(event,this);
                        });
                        jqcc("div.cometchat_tabcontentsubmit").click(function(event) {
                            return jqcc.cometchat.chatroomBoxKeydown(event,jqcc("textarea.cometchat_textarea"),1);
                        });
                        jqcc("textarea.cometchat_textarea").keyup(function(event) {
                            return jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomBoxKeyup(event,this);
                        });
                    },
                    chatroomready: function() {
                        if ((jqcc.cometchat.chatroommessageBeep()) == 1) {
                            $('<audio id="messageBeep" style="display:none;"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.mp3" type="audio/mpeg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.ogg" type="audio/ogg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
                        }
                        try {
                            if (parent.jqcc.cometchat.ping() == 1) {
                                jqcc.cometchat.setChatroomVars('apiAccess',1);
                            }
                        } catch (e) {}
                                if(jqcc.cometchat.getChatroomVars('calleeAPI') !== 'mobilewebapp') {
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomWindowResize();
                                }
                        if (jqcc().slimScroll) {
                            jqcc("#currentroom_convo").slimScroll({height: jqcc("#currentroom_convo").css('height')});
                            jqcc("#currentroom_users").slimScroll({height: jqcc("#currentroom_users").css('height')});
                        }
                        window.onresize = function(event) {
                            if(jqcc.cometchat.getChatroomVars('calleeAPI') !== 'mobilewebapp') {
                                    jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomWindowResize();
                            }
                        }
                        jqcc('#currentroom').mouseover(function() {
                            jqcc.cometchat.setChatroomVars('newMessages',0);
                        });
                        jqcc.cometchat.chatroomHeartbeat(1);
                        jqcc("textarea.cometchat_textarea").keydown(function(event) {
                            return jqcc.cometchat.chatroomBoxKeydown(event,this);
                        });
                        jqcc("div.cometchat_tabcontentsubmit").click(function(event) {
                            return jqcc.cometchat.chatroomBoxKeydown(event,jqcc("textarea.cometchat_textarea"),1);
                        });
                        jqcc("textarea.cometchat_textarea").keyup(function(event) {
                            return jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomBoxKeyup(event,this);
                        });
                    }
                };
        })();
})(jqcc);

if(typeof(jqcc.facebook) === "undefined"){
    jqcc.facebook=function(){};
}

jqcc.extend(jqcc.facebook, jqcc.crfacebook);

jqcc(document).ready(function(){

    var lang = '<?php echo $chatrooms_language[21];?>';

    jqcc('.inviteChatroomUsers').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=invite&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"inviteChatroomUser", "params":{"url":url, "action":"invite", "lang":lang}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.inviteChatroomUser();
        }
    });

    jqcc('.unbanChatroomUser').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=unban&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&time='+Math.random();

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"invite", "lang":lang}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.unbanChatroomUser();
        }
    });

    jqcc('.loadChatroomPro').live('click',function(){
        var owner = jqcc(this).attr('owner');
        var uid = jqcc(this).attr('userid');
        lang = jqcc(this).attr('username');
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=loadChatroomPro&apiAccess='+jqcc.cometchat.getChatroomVars('apiAccess')+'&owner='+owner+'&roomid='+roomid+'&basedata='+basedata+'&inviteid='+uid+'&roomname='+roomname;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"loadChatroomPro", "lang":lang}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.loadChatroomPro(uid,owner,lang,popoutmode);
        }
    });
});
