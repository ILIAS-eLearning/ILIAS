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

if (typeof($) === 'undefined') {
    $ = jqcc;
}

(function($) {
    var settings = {};
    settings = jqcc.cometchat.getcrAllVariables();
    var calleeAPI = jqcc.cometchat.getChatroomVars('calleeAPI');
    var baseUrl = jqcc.cometchat.getBaseUrl();
    var tabWidth = 'width: 50%;right: 0;';
    var chromeReorderFix = '_';
    var newmess;
    var newmesscr;
    $.crsynergy = (function() {
            return {
                chatroomInit: function(){
                    var createChatroom='';
                    if(settings.allowUsers == 1){
                        createChatroom='<div id="createChatroomOption" class="cometchat_tabsubtitle"><?php echo $chatrooms_language[2];?> &#9658;</div><div class="content_div" id="create" style="display:none"><div id="create_chatroom" class="content_div"><form class="create" onsubmit="javascript:jqcc.cometchat.createChatroomSubmit(); return false;"><div style="clear:both;padding-top:10px"></div><div class="create_value"><input type="text" id="name" class="create_input" placeholder="<?php echo $chatrooms_language[27];?>" /></div><div style="clear:both;padding-top:10px"></div><div class="create_value" ><select id="type" onchange="jqcc[\''+calleeAPI+'\'].crcheckDropDown(this)" class="create_input"><option value="0"><?php echo $chatrooms_language[29];?></option><option value="1"><?php echo $chatrooms_language[30];?></option><option value="2"><?php echo $chatrooms_language[31];?></option></select></div><div class="password_hide" style="clear:both;padding-top:10px"></div><div class="create_value password_hide"><input id="password" type="password" autocomplete="off" class="create_input" placeholder="<?php echo $chatrooms_language[32];?>" /></div><div class="create_value"><input type="submit" class="createroombutton" value="<?php echo $chatrooms_language[33];?>" /></div></form></div></div>';
                    }
                    var chatroomsTab = '<span id="cometchat_chatroomstab" class="cometchat_tab" style="'+tabWidth+'"><span id="cometchat_chatroomstab_text" class="cometchat_tabstext"><?php echo $chatrooms_language[100];?></span></span>';
                    var chatroomstabpopup = '<div id="cometchat_chatroomstab_popup">'+createChatroom+'<div id="lobby"><div class="cometchat_tabsubtitle" id="cometchat_chatroom_searchbar"><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light" id="cometchat_chatroom_search" value="<?php echo $chatrooms_language[60];?>"></div><div class="lobby_rooms content_div cometchat_tabpopup" id="lobby_rooms"></div></div></div>';
		    var currentroom = '<div class="content_div" id="currentroom" style="display:none"><div id="currentroom_left" class="content_div cometchat_tabpopup"><div class="cometchat_tabsubtitle"><div class="cometchat_chatboxLeftDetails"><div class="cometchat_userscontentavatar"><img src="'+baseUrl+'modules/chatrooms/group.png" class="cometchat_chatroomavatarimage" /></div><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_chatroomdisplayname"></div></div></div><div title="<?php echo $chatrooms_language[72];?>" class="cometchat_user_closebox">X</div><div class="cometchat_chatboxMenuOptions"><div class="cometchat_menuOption cometchat_chatroomUsersOption"><img title="<?php echo $chatrooms_language[71];?>" class="cometchat_chatroomUsersIcon cometchat_menuOptionIcon" src="'+baseUrl+'modules/chatrooms/chatroomusers.png"/><div id="chatroomusers_popup" class="menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="chatroomuser_container"></div></div></div><div class="cometchat_menuOption cometchat_pluginsOption"><img title="<?php echo $chatrooms_language[69];?>" class="cometchat_pluginsIcon cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+calleeAPI+'/images/pluginsicon.png"/><div id="cometchat_plugins" class="cometchat_plugins menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="plugin_container"></div></div></div><div class="cometchat_menuOption cometchat_chatroomModOption"><img title="<?php echo $chatrooms_language[70];?>" class="cometchat_chatroomUserOptionsIcon cometchat_menuOptionIcon" src="'+baseUrl+'modules/chatrooms/chatroommod.png"/><div id="cometchat_moderator_opt" class="cometchat_moderator_opt menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="moderator_container"></div></div></div></div></div><div id="currentroom_convo"><div id="currentroom_convotext" class="cometchat_message_container"></div></div><div style="clear:both"></div><div class="cometchat_tabinputcontainer"><div title="Send" class="cometchat_tabcontentsubmit cometchat_sendicon"></div><div class="cometchat_tabcontentinput"><div style="margin-right:28px;"><textarea class="cometchat_textarea"></textarea></div></div></div></div></div>';
                    $('#cometchat_userstab').after(chatroomsTab);
                    $('#cometchat_userstab_popup').after(chatroomstabpopup);
                    $('#cometchat_righttab').append(currentroom);
                    if(jqcc().slimScroll){
                        $('#lobby_rooms').slimScroll({height: 'auto'});
                        $("#plugin_container").slimScroll({width: 'auto'});
                        $("#moderator_container").slimScroll({width: 'auto'});
                        $("#chatroomuser_container").slimScroll({width: 'auto'});
                    }
                    $('#createChatroomOption').click(function(){
                        if($('#create').is(':visible')){
                            $(this).html('<?php echo $chatrooms_language[2];?>  &#9658;');
                            $('#create').hide('slow',function(){
                                $[calleeAPI].chatroomWindowResize();
                            });
                        }else{
                            $(this).html("<?php echo $chatrooms_language[2];?>  &#9660;");
                            var lobbyroomsHeight = $('#lobby_rooms').height();
                            $('#lobby_rooms').parent('.slimScrollDiv').css('height',lobbyroomsHeight-$('#create').outerHeight(true)+'px');
                            $('#lobby_rooms').css('height',lobbyroomsHeight-$('#create').outerHeight(true)+'px');
                            $('#create').show('slow');
                        }
                    });
                    var currentroom = $('#currentroom');
                    currentroom.find('.cometchat_chatroomUsersOption').click(function(){
                        if($('#chatroomusers_popup').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#chatroomusers_popup').addClass('cometchat_tabopen');
                        }
                    });
                    currentroom.find('.cometchat_pluginsOption').click(function(){
                        if($('#cometchat_plugins').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#cometchat_plugins').addClass('cometchat_tabopen');
                        }
                    });
                    currentroom.find('.cometchat_chatroomModOption').click(function(){
                        if($('#cometchat_moderator_opt').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#cometchat_moderator_opt').addClass('cometchat_tabopen');
                        }
                    });
                    currentroom.find('div.cometchat_user_closebox').click(function(){
                        jqcc.cometchat.leaveChatroom();
                        jqcc.cometchat.setThemeVariable('trayOpen','');
                        jqcc.cometchat.setSessionVariable('trayOpen', '');
                        currentroom.hide();
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        var nextChatBox;
                        for(chatBoxId in chatBoxesOrder){
                            nextChatBox = chatBoxId.replace('_','');
                        }
                        $("#cometchat_user_"+nextChatBox+"_popup").addClass('cometchat_tabopen');
                        jqcc.cometchat.setThemeVariable('openChatboxId', nextChatBox);
                        jqcc.cometchat.setSessionVariable('openChatboxId', nextChatBox);
                        jqcc.cometchat.orderChatboxes();
                        jqcc[calleeAPI].windowResize();
                        $('.cometchat_noactivity').css('display','block');
                    });
                    setTimeout(function(){
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        for (var key in chatBoxesOrder)
                        {
                            if (chatBoxesOrder.hasOwnProperty(key))
                            {
                                if(typeof (jqcc.synergy.addPopup)!=='undefined'){
                                    jqcc.synergy.addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                                }
                            }
                        }
                    },500);
                    $('.cometchat_noactivity').css('display','none');
                },
                chatroomTab: function(){
                    var cometchat_chatroom_search = $("#cometchat_chatroom_search");
                    var lobby_rooms = $('#lobby_rooms');
                    cometchat_chatroom_search.click(function(){
                        var searchString = $(this).val();
                        if(searchString=='<?php echo $chatrooms_language[60];?>'){
                            cometchat_chatroom_search.val('');
                            cometchat_chatroom_search.addClass('cometchat_search_light');
                        }
                    });
                    cometchat_chatroom_search.blur(function(){
                        var searchString = $(this).val();
                        if(searchString==''){
                            cometchat_chatroom_search.addClass('cometchat_search_light');
                            cometchat_chatroom_search.val('<?php echo $chatrooms_language[60];?>');
                        }
                    });
                    cometchat_chatroom_search.keyup(function(){
                        var searchString = $(this).val();
                        if(searchString.length>0&&searchString!='<?php echo $chatrooms_language[60];?>'){
                            lobby_rooms.find('div.lobby_room').hide();
                            lobby_rooms.find('span.lobby_room_1:icontains('+searchString+')').parents('div.lobby_room').show();
                            cometchat_chatroom_search.removeClass('cometchat_search_light');
                        }else{
                            lobby_rooms.find('div.lobby_room').show();
                        }
                    });
                    var cometchat_userstab = $('#cometchat_userstab');
                    var cometchat_chatroomstab = $('#cometchat_chatroomstab');
                    cometchat_chatroomstab.click(function(){
                        jqcc[calleeAPI].hideMenuPopup();
                        $('#cometchat_chatroomstab_text').text('<?php echo $chatrooms_language[100];?>');
                        if(typeof(newmess)!="undefined"){
                            clearInterval(newmess);
                        }
                        newmess = setInterval(function(){
                            if($("#cometchat_chatroomstab.cometchat_tabclick").length>0){
                                var newOneonOneMessages = 0;
                                jqcc('#cometchat_activechatboxes_popup .cometchat_msgcount').each(function(){
                                    newOneonOneMessages += parseInt(jqcc(this).children('.cometchat_msgcounttext').text());
                                });
                                if(newOneonOneMessages>0){
                                    $('#cometchat_userstab_text').text('<?php echo $language[88]?> ('+newOneonOneMessages+')');
                                }
                                setTimeout(function(){
                                    $('#cometchat_userstab_text').text('<?php echo $language[9];?> ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                                },2000);
                            }else{
                                if(typeof(newmess)!='undefined'){
                                    clearInterval(newmess);
                                }
                            }
                        },4000);
                        if(jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                            jqcc[calleeAPI].removeUnderline();
                            $("#cometchat_self .cometchat_userscontentdot").addClass('cometchat_available');
                            $('.cometchat_optionsstatus.available').css('text-decoration', 'underline');
                            $('#cometchat_userstab_text').html('<?php echo $language[9];?> ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            $("#cometchat_optionsbutton_popup").find("span.available").click();
                        }

                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                        jqcc.cometchat.chatroomHeartbeat(1);
                        $(this).addClass("cometchat_tabclick");
                        cometchat_userstab.removeClass("cometchat_tabclick");
                        $('#cometchat_userstab_popup').removeClass("cometchat_tabopen");
                        $('#cometchat_chatroomstab_popup').addClass("cometchat_tabopen");
                        $[calleeAPI].chatroomWindowResize();
                    });
                },
                chatroomOffline: function(){
                    $('#cometchat_chatroomstab_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_chatroomstab').removeClass('cometchat_tabclick');
                    jqcc.cometchat.leaveChatroom();
                },
                playsound: function() {
                    try	{
                        document.getElementById('messageBeep').play();
                    } catch (error) {
                        jqcc.cometchat.setChatroomVars('messageBeep',0);
                    }
                },
                sendChatroomMessage: function(chatboxtextarea) {
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height','25px');
                    $(chatboxtextarea).css('overflow-y','hidden');
                    $[calleeAPI].chatroomWindowResize();
                    $(chatboxtextarea).focus();
                },
                createChatroom: function() {
                    $('#createtab').addClass('tab_selected');
                    $('#create').css('display','block');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[5];?>');
                },
                getTimeDisplay: function(ts,id) {
                    var time = getTimeDisplay(ts);
                    if (ts < jqcc.cometchat.getChatroomVars('todays12am')) {
							return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+" "+time.date+time.type+" "+time.month+")</span>";
                    } else {
                            return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+")</span>";
                    }
                },
                deletemessage: function(delid) {
                    $("#cometchat_message_"+delid).prev(".cometchat_ts").remove();
                    $("#cometchat_message_"+delid).remove();
                    $("#cometchat_usersavatar_"+delid).remove();
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
                        if (settings.myid==controlparameters.params.id) {
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
                        $("#cometchat_chatroomlist_"+controlparameters.params.id).remove();
                    }  else if(typeof(controlparameters.method) != 'undefined' && controlparameters.method == "deletemessage") {
                        $("#cometchat_message_"+controlparameters.params.id).remove();
                    } else {
                        if ($("#cometchat_message_"+incomingid).length > 0) {
                                $("#cometchat_message_"+incomingid).find("span.cometchat_chatboxmessagecontent").html(incomingmessage);
                        } else {
                            if (typeof(controlparameters.method) == 'undefined' || controlparameters.method != 'deletemessage') {
                                sentdata = '';
                                if (sent != null) {
                                    var ts = parseInt(sent);
                                    sentdata = $[calleeAPI].getTimeDisplay(ts,incomingid);
                                }
                                if (!settings.fullName && fromname.indexOf(" ") != -1) {
                                    fromname = fromname.slice(0,fromname.indexOf(" "));
                                }
                                if (fromid != settings.myid) {
                                    if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', fromid))=='undefined'){
                                        jqcc.cometchat.getUserDetails(fromid);
                                    }
                                    var fromavatar = '<a id="cometchat_usersavatar_'+incomingid+'" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');"><img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', fromid)+'"></a>';
                                    temp += ('<div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage" id="cometchat_message_'+incomingid+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>');
                                    if (settings.apiAccess && fromid != 0) {
                                        temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');">');
                                    }
                                    temp += fromname;
                                    if (settings.apiAccess && fromid != 0) {
                                        temp += ('</a>');
                                    }
                                    temp += ('</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent">'+incomingmessage+'</span></div></div>');
                            } else {
                                temp += ('<div class="cometchat_messagebox">'+sentdata+'<div class="cometchat_chatboxmessage cometchat_self" id="cometchat_message_'+incomingid+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+'</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent">'+incomingmessage+'</span></div></div>');
                                }
                                $("#currentroom_convotext").append(temp);
                                if ($.cookie(jqcc.cometchat.getChatroomVars('cookie_prefix')+"sound") && $.cookie(jqcc.cometchat.getChatroomVars('cookie_prefix')+"sound") == 'true') { } else {
                                    $[calleeAPI].playsound();
                                }
                            }
                        }
                    }

                    if(jqcc.cometchat.getChatroomVars('owner')|| jqcc.cometchat.getChatroomVars('isModerator') || (jqcc.cometchat.getChatroomVars('allowDelete') == 1 && fromid == settings.myid)) {
                        if ($("#cometchat_message_"+incomingid).find(".delete_msg").length < 1) {
                            jqcc('#cometchat_message_'+incomingid).find(".cometchat_chatboxmessagefrom").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incomingid+'\');"><img class="hoverbraces" src="'+baseUrl+'modules/chatrooms/bin.png"></span>');
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
                    if(jqcc('#currentroom:visible').length<1){
                            $('.cometchat_msgcounttext_cr').text('0');
                            $('.cometchat_msgcount_cr').hide();
                            var newMessagesCount = jqcc.cometchat.getChatroomVars('newMessages');
                            $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcounttext_cr').text(newMessagesCount);
                            if(newMessagesCount > 0){
                                $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcount_cr').show();
                            }
                        }
                    if (settings.apiAccess == 1 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                        parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                    }
                },
                chatroomBoxKeyup: function(event,chatboxtextarea) {
                    if(event.keyCode==8&&$(chatboxtextarea).val()==''){
                        $(chatboxtextarea).css('height', '25px');
                        $[calleeAPI].chatroomWindowResize();
                    }
                    var chatboxtextareaheight  = $(chatboxtextarea).height();
                    var maxHeight = 94;
                    chatboxtextareaheight = Math.max(chatboxtextarea.scrollHeight, chatboxtextareaheight);
                    chatboxtextareaheight = Math.min(maxHeight, chatboxtextareaheight);
                    if(chatboxtextareaheight>chatboxtextarea.clientHeight && chatboxtextareaheight<maxHeight){
                        $(chatboxtextarea).css('height', chatboxtextareaheight+'px');
                    }else if(chatboxtextareaheight>chatboxtextarea.clientHeight){
                        $(chatboxtextarea).css('height', maxHeight+'px');
                        $(chatboxtextarea).css('overflow-y', 'auto');
                    }
                    $[calleeAPI].chatroomWindowResize();
                },
                hidetabs: function() {

                },
                loadLobby: function() {
                    $[calleeAPI].hidetabs();
                    $('#lobbytab').addClass('tab_selected');
                    $('#lobby').css('display','block');
                    $('div.moderator_container').html('<?php echo $chatrooms_language[1];?>');
                    clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                    if(typeof(jqcc.cometchat.getThemeVariable) == 'undefined' || jqcc.cometchat.getThemeVariable('currentStatus') != 'offline'){
                        jqcc.cometchat.chatroomHeartbeat(1);
                    }
                },
                crcheckDropDown: function(dropdown) {
                    var id = dropdown.selectedIndex;
                    if (id == 1) {
                        $('div.password_hide').css('display','block');
                    } else {
                        $('div.password_hide').css('display','none');
                    }
                    $[calleeAPI].chatroomWindowResize();
                },
                loadRoom: function(clicked) {
                        jqcc[calleeAPI].hideMenuPopup();
                    var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                    var roomno = jqcc.cometchat.getChatroomVars('currentroom');
                    if(clicked==1){
                        jqcc.cometchat.setThemeVariable('trayOpen','chatrooms');
                        jqcc.cometchat.setSessionVariable('trayOpen', 'chatrooms');
                        $('.cometchat_userchatbox').removeClass('cometchat_tabopen');
                    }
                    if($('#create').is(':visible')){
                        $(this).html('<?php echo $chatrooms_language[2];?>  &#9658;');
                        $('#create').hide('slow',function(){
                            $[calleeAPI].chatroomWindowResize();
                        });
                    }
                    $('#currentroom').css('display','block');
                    $('#currentroom').find('.cometchat_chatroomdisplayname').text(roomname);
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[4];?>'+'<span> | </span>'+'<?php echo $chatrooms_language[48];?>'+'<?php echo $chatrooms_language[39];?>');
                    $('#moderator_container').html('<div class="mod_list_item inviteChatroomUsers"><img class="mod_option_icons" src="'+baseUrl+'themes/'+calleeAPI+'/images/inviteuser.png"/><a href="javascript:void(0);" ><?php echo $chatrooms_language[67];?></a></div><div class="mod_list_item unbanChatroomUser" id="unbanuser"><img class="mod_option_icons" src="'+baseUrl+'themes/'+calleeAPI+'/images/unbanuser.png"/><a  href="javascript:void(0);" ><?php echo $chatrooms_language[68];?></a></div>');
                    document.cookie = '<?php echo $cookiePrefix;?>chatroom='+urlencode(roomno+':'+jqcc.cometchat.getChatroomVars('currentp')+':'+urlencode(roomname));
                    if(jqcc.cometchat.getChatroomVars('isModerator')==undefined||jqcc.cometchat.getChatroomVars('isModerator')==0){
                       jqcc('#unbanuser').remove();
                    }
                    var pluginshtml = '';
                    var plugins = jqcc.cometchat.getChatroomVars('plugins');
                    var avchathtml = '';
                    var smilieshtml = '';
                    var filetransferhtml = '';
                    if (plugins.length > 0) {
                        for (var i=0;i<plugins.length;i++) {
                            var name = 'cc'+plugins[i];
                            if(settings.plugins[i]=='avchat'){
                                avchathtml='<div class="cometchat_menuOption cometchat_avchatOption"><img class="ccplugins  cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+calleeAPI+'/images/avchaticon.png" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1" /></div>';
                            }else if(settings.plugins[i]=='smilies'){
                                smilieshtml='<div class="ccplugins cometchat_smilies" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1">&#9786;</div>';
                            }else if(settings.plugins[i]=='filetransfer'){
                                filetransferhtml='<img src="'+baseUrl+'themes/'+calleeAPI+'/images/attachment.png" class="ccplugins cometchat_transfericon cometchat_filetransfer" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1"/>';
                            }else if (typeof($[name]) == 'object') {
                                if(name != 'ccchattime'){
                                    pluginshtml += '<div class="ccplugins cometchat_pluginsicon cometchat_'+ settings.plugins[i] + '" title="' + $[name].getTitle() + '" name="'+name+'" to="'+roomno+'" chatroommode="1"><span>'+$[name].getTitle()+'</span></div>';
                                }
                            }
                        }
                    }
                    if($('#currentroom_left .cometchat_avchatOption').length > 0){
                        $('#currentroom_left .cometchat_avchatOption').remove();
                    }
                    $('#currentroom_left .cometchat_chatboxMenuOptions').prepend(avchathtml);
                    if($('#currentroom_left .cometchat_smilies').length > 0){
                        $('#currentroom_left .cometchat_smilies').remove();
                    }
                    $('#currentroom_left .cometchat_tabcontentinput').prepend(smilieshtml);
                    if($('#currentroom_left .cometchat_filetransfer').length > 0){
                        $('#currentroom_left .cometchat_filetransfer').remove();
                    }
                    $('#currentroom_left .cometchat_tabinputcontainer').prepend(filetransferhtml);

                    $('#plugin_container').html(pluginshtml);
                    $('.ccplugins').click(function(event){
                        event.stopImmediatePropagation();
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
                        if(window.top == window.self) {
                            var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                            jqcc[name].init(controlparameters);
                        } else {
                            var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        }
                    });
                    $[calleeAPI].chatroomWindowResize();
                },
                chatroomWindowResize: function() {
                    var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],winWidth=w.innerWidth||e.clientWidth||g.clientWidth,winHt=w.innerHeight||e.clientHeight||g.clientHeight;
                    var searchbar_Height = $('#cometchat_chatroom_searchbar').is(':visible') ? $('#cometchat_chatroom_searchbar').outerHeight(true) : 0;
                    var createChatroomHeight = $('#create').is(':visible') ? $('#create').outerHeight(true) : 0;
                    var lobbyroomsHeight = winHt-$('#cometchat_self_container').outerHeight(true)-$('#cometchat_tabcontainer').outerHeight(true)-$('#cometchat_trayicons').outerHeight(true)-$('#createChatroomOption').outerHeight(true)-searchbar_Height-createChatroomHeight+'px';
                    $('#lobby_rooms').parent('.slimScrollDiv').css('height',lobbyroomsHeight);
                    $('#lobby_rooms').css('height',lobbyroomsHeight);
                    var roomConvoHeight = winHt-$('.cometchat_tabinputcontainer').outerHeight(true)-($('#currentroom_left').find('.cometchat_tabsubtitle').outerHeight(true));
                    $("#currentroom_convo").css('height',roomConvoHeight+'px');
                    $("#currentroom_convo").parent("div.slimScrollDiv").css('height',roomConvoHeight+'px');
                },
                kickid: function(kickid) {
                    $("#chatroom_userlist_"+kickid).remove();
                },
                banid: function(banid) {
                    $("#chatroom_userlist_"+banid).remove();
                },
                chatroomScrollDown: function(forced) {
                	if(settings.newMessageIndicator == 1 && ($('#currentroom_convotext').outerHeight()+$('#currentroom_convotext').offset().top-$('#currentroom_convo').height()-$('#currentroom_convo').offset().top-(2*$('.cometchat_chatboxmessage').outerHeight(true))>0)){
                        if(($('#currentroom_convo').height()-$('#currentroom_convotext').outerHeight()) < 0){
                        	if(forced) {
    	                        if (jqcc().slimScroll) {
    	                            $('#currentroom_convo').slimScroll({scroll: '1'});
    	                        } else {
    	                            setTimeout(function() {
    	                            $("#currentroom_convo").scrollTop(50000);
    	                            },100);
    	                        }
    	                        if($('.talkindicator').length != 0){
	                            $('.talkindicator').fadeOut();
                                }
    	                    }else{
                                if($('.talkindicator').length != 0){
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
                                        if($('#currentroom_convotext').outerHeight() + $('#currentroom_convotext').offset().top - $('#currentroom_convo').offset().top <= $('#currentroom_convo').height()){
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
                    if (name != '' && name != null && name != '<?php echo $chatrooms_language[27];?>') {
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
                    jqcc("#cometchat_chatroomlist_"+currentroom).removeClass("cometchat_chatroomselected");
                    jqcc("#cometchat_chatroomlist_"+id).addClass("cometchat_chatroomselected");
                },
                checkOwnership: function(owner,isModerator,name) {
                    var loadroom = 'javascript:jqcc["'+calleeAPI+'"].loadRoom()';
                    if (owner || isModerator) {
                    	jqcc.cometchat.setChatroomVars('isModerator',1);
                    } else {
                        jqcc('#currentroomtab').html('<a href="javascript:void(0);" show=0 onclick='+loadroom+'>'+name+'</a>');
                        jqcc.cometchat.setChatroomVars('isModerator',0);
                    }
                    jqcc('#currentroom_convotext').html('');
                    jqcc("#chatroomuser_container").html('');
                },
                leaveRoomClass : function(currentroom) {
                    jqcc("#cometchat_chatroomlist_"+currentroom).removeClass("cometchat_chatroomselected");
                },
                removeCurrentRoomTab : function() {
                    jqcc('#currentroom').css('display','none');
                },
                chatroomLogout : function() {
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
                            roomtype = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/lock.png" />';
                        }

                        if (room.s == 1) {
                            roomowner = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/user.png" />';
                        }

                        if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                            deleteroom = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/remove.png" />';
                        }

                        if (room.s == 2) {
                            room.s = 1;
                        }
                        temp += '<div id="cometchat_chatroomlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="$(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="$(this).removeClass(\'cometchat_chatroomlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\',\'1\');" ><span class="lobby_room_1" title="'+longname+'">'+longname+'</span><span class="lobby_room_2" title="'+room.online+' <?php echo $chatrooms_language[34];?>">('+room.online+')</span><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><span class="lobby_room_6"><span class="cometchat_msgcount_cr"><div class="cometchat_msgcounttext_cr">0</div></span></span></span><div style="clear:both"></div></div>';
                    });
                    if (temp != '') {
                        jqcc('#lobby_rooms').html(temp);
                    }else{
                        jqcc('#lobby_rooms').html('<div class="lobby_noroom"><?php echo $chatrooms_language[53]; ?></div>');
                    }
                    if(jqcc('#currentroom:visible').length<1){
                        var newMessagesCount = jqcc.cometchat.getChatroomVars('newMessages');
                        $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcounttext_cr').text(newMessagesCount);
                        if(newMessagesCount > 0){
                            $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcount_cr').show();
                        }
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
                                    if (controlparameters.method=='kicked' || controlparameters.method=='banned') {
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
                                        $("#cometchat_chatroomlist_"+controlparameters.params.id).remove();
                                    } else if (controlparameters.method == "deletemessage") {
                                        $("#cometchat_message_"+controlparameters.params.id).remove();
                                    }
                                } else {
                                    if ($("#cometchat_message_"+incoming.id).length > 0) {
                                        $("#cometchat_message_"+incoming.id).find("span.cometchat_chatboxmessagecontent").html(incoming.message);
                                    } else {
                                        var ts = parseInt(incoming.sent)*1000;
                                        if (!settings.fullName && fromname.indexOf(" ") != -1) {
                                            fromname = fromname.slice(0,fromname.indexOf(" "));
                                        }
                                        if (incoming.fromid != settings.myid) {
                                            if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid))=='undefined'){
                                                jqcc.cometchat.getUserDetails(incoming.fromid);
                                            }
                                            var fromavatar = '<a id="cometchat_usersavatar_'+incoming.id+'" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');"><img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid)+'"></a>';
                                            temp += ('<div class="cometchat_messagebox">'+fromavatar+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>');
                                            if (settings.apiAccess && incoming.fromid != 0) {
                                                temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');">');
                                            }
                                            temp += fromname;
                                            if (settings.apiAccess && incoming.fromid != 0) {
                                                temp += ('</a>');
                                            }
                                            temp += ('</strong>&nbsp;:&nbsp;&nbsp;</span><span class="cometchat_chatboxmessagecontent">'+incoming.message+'</span></div></div>');
                                            jqcc.cometchat.setChatroomVars('newMessages',jqcc.cometchat.getChatroomVars('newMessages')+1);
                                            beepNewMessages++;
                                        } else {
                                            temp += ('<div class="cometchat_messagebox">'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage cometchat_self" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+'</strong>:&nbsp;&nbsp;</span><span class="cometchat_chatboxmessagecontent">'+incoming.message+'</span></div></div>');
                                        }
                                    }
                                }
                                $('#currentroom_convotext').append(temp);
                                if (jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    if ($("#cometchat_message_"+incoming.id).find(".delete_msg").length < 1) {
                                        jqcc('#cometchat_message_'+incoming.id).find(".cometchat_chatboxmessagefrom").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');"><img class="hoverbraces" src="'+baseUrl+'modules/chatrooms/bin.png"></span>');
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
                                    $(".delete_msg").mouseout(function() {
                                    });
                                }
                                var forced = (incoming.fromid == settings.myid) ? 1 : 0;
                                $[calleeAPI].chatroomScrollDown(forced);
                            }
                        });
                        jqcc.cometchat.setChatroomVars('heartbeatCount',1);
                        jqcc.cometchat.setChatroomVars('heartbeatTime',settings.minHeartbeat);

                        if(jqcc('#currentroom:visible').length<1){
                            $('.cometchat_msgcounttext_cr').text('0');
                            $('.cometchat_msgcount_cr').hide();
                            var newMessagesCount = jqcc.cometchat.getChatroomVars('newMessages');
                            $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcounttext_cr').text(newMessagesCount);
                            if(newMessagesCount > 0){
                                $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_msgcount_cr').show();
                            }
                            if(typeof(newmesscr)!="undefined"){
                                clearInterval(newmesscr);
                            }
                            newmesscr = setInterval(function(){
                                if($("#cometchat_chatroomstab.cometchat_tabclick").length<1){
                                    var newCrMessages = jqcc.cometchat.getChatroomVars('newMessages');
                                    if(newCrMessages>0){
                                        $('#cometchat_chatroomstab_text').text('<?php echo $language[88]?> ('+newCrMessages+')');
                                    }
                                    setTimeout(function(){
                                        $('#cometchat_chatroomstab_text').text('<?php echo $chatrooms_language[100];?>');
                                    },2000);
                                }else{
                                    if(typeof(newmesscr)!='undefined'){
                                        clearInterval(newmesscr);
                                    }
                                }
                            },4000);
                        }

                        if (settings.apiAccess == 1 && fetchedUsers == 0 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                            parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                        }
                        if ($.cookie(settings.cookie_prefix+"sound") && $.cookie(settings.cookie_prefix+"sound") == 'true') { } else {
                            if (beepNewMessages > 0 && fetchedUsers == 0) {
                                $[calleeAPI].playsound();
                            }
                        }
                    },
                    silentRoom: function(id, name, silent) {
                        if (settings.lightboxWindows == 1) {
                            var controlparameters = {"type":"modules", "name":"core", "method":"loadCCPopup", "params":{"url": settings.baseUrl+'modules/chatrooms/chatrooms.php?id='+id+'&basedata='+settings.basedata+'&name='+name+'&silent='+silent+'&action=passwordBox', "name":"passwordBox", "properties":"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=320,height=130", "width":"320", "height":"110", "title":name, "force":null, "allowmaximize":null, "allowresize":null, "allowpopout":null, "windowMode":null}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var temp = prompt('<?php echo $chatrooms_language[8];?>','');
                            if (temp) {
                                jqcc.cometchat.checkChatroomPass(id,name,silent,temp);
                            } else {
                                return;
                            }
                        }
                    },
                    updateChatroomsTabtext: function(){
                        $('#cometchat_chatroomstab_text').text('<?php echo $chatrooms_language[100];?>');
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
                                var nowTime = new Date();
                                var ts = Math.floor(nowTime.getTime()/1000);
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
                                userhtml='<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft"><?php echo $chatrooms_language[61]?><hr style="height:3px;" class="hrright"></div>';
                                moderatorhtml='<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft"><?php echo $chatrooms_language[62]?><hr style="height:3px;" class="hrright"></div>';
                                if ($.inArray(user.id ,jqcc.cometchat.getChatroomVars('moderators') ) != -1 ) {
                                    if (user.id == settings.myid) {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid="'+user.id+'" owner="'+settings.owner+'" username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                } else {
                                    if (user.id == settings.myid) {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid="'+user.id+'" owner="'+settings.owner+'" username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                }
                            }
                        });
                        for (user in settings.users) {
                            if (settings.users.hasOwnProperty(user)) {
                                if (newUsers[user] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                    var nowTime = new Date();
                                    var ts = Math.floor(nowTime.getTime()/1000);
                                    $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+settings.usersName[user]+'<?php echo $chatrooms_language[13]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                    $[calleeAPI].chatroomScrollDown();
                                }
                            }
                        }
                        if(temp1 != "" && temp !="")
                            jqcc('#chatroomuser_container').html(moderatorhtml+temp1+userhtml+temp);
                        else if(temp == "")
                            jqcc('#chatroomuser_container').html(moderatorhtml+temp1);
                        else
                            jqcc('#chatroomuser_container').html(userhtml+temp);

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
                    }
                };
        })();
})(jqcc);

if(typeof(jqcc.lite) === "undefined"){
    jqcc.synergy=function(){};
}

jqcc.extend(jqcc.synergy, jqcc.crsynergy);

jqcc(document).ready(function(){
    jqcc('.inviteChatroomUsers').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var lang = '<?php echo $chatrooms_language[21];?>';
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=invite&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&popoutmode='+popoutmode;

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
        var lang = '<?php echo $chatrooms_language[21];?>';
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=unban&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&popoutmode='+popoutmode+'&time='+Math.random();

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
        var lang = '<?php echo $chatrooms_language[21];?>';
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=loadChatroomPro&apiAccess='+jqcc.cometchat.getChatroomVars('checkBarEnabled')+'&owner='+owner+'&roomid='+roomid+'&basedata='+basedata+'&inviteid='+uid+'&roomname='+roomname;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"loadChatroomPro", "lang":lang, "synergy":1}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.loadChatroomPro(uid,owner,lang);
        }
    });
});
