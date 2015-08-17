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

include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($chatrooms_language as $i => $l) {
	$chatrooms_language[$i] = str_replace("'", "\'", $l);
}

$callbackfn = ''; if (!empty($_GET['callbackfn'])) { $callbackfn = $_GET['callbackfn']; }

if (!empty($callbackfn)) {
	$calleeAPI = $callbackfn;
} else {
	$calleeAPI = $theme;
}

?>

if (typeof(jqcc) === 'undefined') {
	jqcc = jQuery;
}

jqcc.ajaxSetup({scriptCharset: "utf-8", cache: "false"});

if (typeof(jqcc.cometchat)==='undefined') {
    var mode = 1;
    jqcc.cometchat = function() {};
}

jqcc.extend(jqcc.cometchat, {
    crvariables : {themename: '<?php echo $theme;?>',
                timestamp: '0',
                currentroom: '0',
                currentp: '',
                currentroomcode: '',
                myid: '0',
                owner: '0',
                isModerator: 0,
                cu_uids: [],
                heartbeatTimer: '',
                baseUrl: '<?php echo BASE_URL;?>',
                minHeartbeat: '<?php echo $minHeartbeat;?>',
                maxHeartbeat: '<?php echo $maxHeartbeat;?>',
                fullName: '<?php echo $displayFullName;?>',
                hideEnterExit: '<?php echo $hideEnterExit;?>',
                messageBeep: '<?php echo $messageBeep;?>',
                heartbeatTime: this.minHeartbeat,
                heartbeatCount: 1,
                todaysDate: new Date(),
                todays12am: (new Date()).getTime() - ((new Date()).getTime()%(24*60*60*1000)),
                clh: '',
                ulh: '',
                prepend: 0,
                users: {},
                usersName: {},
                initializeRoom: 0,
                password: '',
                currentroomname: '',
                armyTime: '<?php echo $armyTime;?>',
                specialChars: /([^\x00-\x80]+)|([&][#])+/,
                apiAccess: 0,
                lightboxWindows: '<?php echo $lightboxWindows;?>',
                newMessages: 0,
                plugins: ['<?php echo implode("', '",$crplugins);?>'],
                cookiePrefix: '<?php echo $cookiePrefix;?>',
                basedata: getURLParameter('basedata'),
                allowDelete: '<?php echo $allowDelete;?>',
                lastmessagetime : Math.floor(new Date().getTime()),
                floodControl: '<?php echo $floodControl;?>',
                calleeAPI: '<?php echo $calleeAPI; ?>',
                moderators: [<?php echo implode(",",$moderatorUserIDs);?>],
                windowCount: 0,
                windows: [],
                popoutmode: getURLParameter('popoutmode'),
                cookiePrefix: '<?php echo $cookiePrefix;?>',
				newMessageIndicator: '<?php echo $newMessageIndicator;?>',
                allowUsers: '<?php echo $allowUsers;?>'
            },
            getcrAllVariables: function() {
                return this.crvariables;
            },
            getChatroomVars: function(key) {
                if (typeof(this.crvariables[key])!=='undefined')
                    return this.crvariables[key];
            },
            setChatroomVars: function(key, value) {
                this.crvariables[key] = value;
            },
            chatroommessageBeep: function() {
                return this.crvariables.messageBeep;
            },

            getBaseUrl: function() {
                return this.crvariables.baseUrl;
            },

            getBaseData: function() {
				if (jqcc.cookie(this.crvariables.cookiePrefix + 'data') !== null) {
					return jqcc.cookie(this.crvariables.cookiePrefix + 'data');
				}
                return this.crvariables.basedata;
            },
            popoutChatroom: function() {
                jqcc.cometchat.leaveChatroom();
                myRef = window.open(self.location,'popoutchat','left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width=800,height=600');
                if (typeof(parent.jqcc.cometchat.closeModule) == "function")
                    parent.jqcc.cometchat.closeModule('chatrooms');
                setTimeout('window.location.reload()',3000);
            },
            checkModerator: function() {
                if(typeof jqcc.cometchat.getChatroomVars != 'undefined' && jqcc.cometchat.getChatroomVars('myid') != ''){
                    var moderators = jqcc.cometchat.getChatroomVars('moderators');
                    var userid = jqcc.cometchat.getChatroomVars('myid');
                    if(moderators.indexOf(userid) > -1){
                        jqcc.cometchat.setChatroomVars('isModerator','1');
                        return 1;
                    }
                }
                return 0;
            },
            chatroomBoxKeydown: function(event,chatboxtextarea,force) {
                var condition = 1;
                if ((event.keyCode == 13 && event.shiftKey == 0) || force == 1) {
                    var message = jqcc(chatboxtextarea).val();
                    message = message.replace(/^\s+|\s+$/g,"");
                    if (this.crvariables.floodControl != 0) {
                        condition = ((Math.floor(new Date().getTime())) - this.crvariables.lastmessagetime > 2000);
                    }
                    if (condition) {
                        var messageLength = message.length;
                        this.crvariables.lastmessagetime = Math.floor(new Date().getTime());
                        if (this.crvariables.currentroom != 0) {
                            if(typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].sendChatroomMessage) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].sendChatroomMessage(chatboxtextarea);
                            if (message != '') {
                                jqcc.cometchat.sendmessageProcess(message, this.crvariables.currentroom, this.crvariables.basedata, this.crvariables.currentroomname);
                            }
                        }
                        return false;
                    } else {
                        alert('<?php echo $chatrooms_language[49];?>');
                    }
                }
            },
            sendmessageProcess: function(message, currentroom, basedata, currentroomname) {

                if (message != '') {
					if (message.length > 1000){
						if (message.charAt(1000) == ' ') {
							messagecurrent = message.substring(0,1000);
						} else {
							messagecurrent = message.substring(0,1000);
							var spacePos = messagecurrent.length;
							while (messagecurrent.charAt(spacePos) != ' ') {
								  spacePos--;
							}
							messagecurrent = message.substring(0,spacePos);
						}
						messagenext = message.substring(messagecurrent.length);
						if (messagenext.length > 0) {
							messagecurrent = messagecurrent + "...";
						}
					} else {
							messagecurrent = message;
							messagenext = '';
					}
					message = messagecurrent;
                    jqcc.post(this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=sendmessage", {message: message , currentroom: currentroom, basedata:basedata, currentroomname: currentroomname} , function(data) {

                        if (data) {
                        if(data.hasOwnProperty('m')){
                            message = data.m;
                        }
                        <?php if (USE_COMET != 1 || COMET_CHATROOMS != 1):?>
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(jqcc.cometchat.getChatroomVars('myid'), message,data.id,1,Math.floor(new Date().getTime()),'0');
                        <?php endif;?>
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomScrollDown) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomScrollDown(1);
                        } else if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].crscrollToBottom) == "function") {
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].crscrollToBottom();
                        }
                        if (messagenext.length > 0) {
                                jqcc.cometchat.sendmessageProcess('...'+messagenext, currentroom, basedata, currentroomname);
                        }
                    },'json');

                }
            },
            confirmDelete: function(delid) {
                var confirmed = confirm('<?php echo $chatrooms_language[47];?>');
                if (confirmed==true) {
                    jqcc.cometchat.deleteMessage(delid);
                }
            },
            deleteMessage: function(delid) {
                jqcc.ajax({
                    url: this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=deleteChatroomMessage",
                    type: "POST",
                    data: {delid:delid,currentroom:this.crvariables.currentroom, basedata:this.crvariables.basedata},
                    success: function(s) {
                        if (s == 1) {
                            if(typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].deletemessage) == 'function'){
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].deletemessage(delid);
                                return;
                            }
                            jqcc("#cometchat_message_"+delid).remove();
                        }
                    }
                });
            },
            leaveChatroom: function(id, banflag, callbackfn) {
                var flag=0;
                var params = "action=leavechatroom";
                if (typeof(id) != 'undefined') {
                    flag=1;
                }
                if (typeof(banflag) != 'undefined') {
                    params = params + "&banflag=1";
                }
                <?php if (USE_COMET == 1 && COMET_CHATROOMS == 1):?>
                cometuncall_function(this.crvariables.currentroomcode);
                this.crvariables.currentroomcode = '';
                <?php endif;?>
                if (typeof(jqcc[this.crvariables.calleeAPI].leaveRoomClass) == "function")
                    jqcc[this.crvariables.calleeAPI].leaveRoomClass(this.crvariables.currentroom);
                jqcc.post(this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?"+params, {currentroom: this.crvariables.currentroom, flag:flag, basedata:this.crvariables.basedata}, function(data) {
                    if (data) {
                        document.cookie = '<?php echo $cookiePrefix;?>chatroom=';
                        jqcc.cometchat.setChatroomVars('currentp','');
                        jqcc.cometchat.setChatroomVars('currentroomname','');
                        jqcc.cometchat.setChatroomVars('currentroom',0);
                        if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].removeCurrentRoomTab) == "function")
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].removeCurrentRoomTab();
                        if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadLobby) == "function" && callbackfn != 'mobilewebapp')
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadLobby();
                    }
                });
            },
            createChatroomSubmit: function() {
                var room = jqcc[this.crvariables.calleeAPI].createChatroomSubmitStruct();
                if (room.name != '' && typeof(room.name) != 'undefined') {
                    jqcc.post(this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=createchatroom", {name: room.name, type:room.type, password: room.password, basedata:this.crvariables.basedata} , function(data) {
                        if (parseInt(data)!=0) {
                            data = JSON.parse(data);
                            jqcc.cometchat.setChatroomVars('currentp',SHA1(room.password))
                            room.name = data.n;
                            jqcc.cometchat.chatroom(data.id,room.name,room.type,jqcc.cometchat.getChatroomVars('currentp'),1);
                        } else {
                            alert('<?php echo $chatrooms_language[38];?>');
                        }
                    });
                }
                return false;
            },
            deleteChatroom: function(event,id){
                event.stopPropagation();
                var confirmDeletion = confirm('<?php echo $chatrooms_language[59];?>');
                if (confirmDeletion == true) {
                    if(typeof(jqcc.cookie('cc_chatroom')) == 'undefined') {
                        var currentroomid = base64_decode(jqcc.cookie('cc_chatroom')).split(":")[0];
                        if(currentroomid == id){
                            jqcc.cometchat.leaveChatroom();
                        }
                    }
                    jqcc.post(this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=deletechatroom", {id: id, basedata:this.crvariables.basedata} , function(data) {
                        if (data != '' && parseInt(data)!=0) {
                            alert('<?php echo $chatrooms_language[56];?>');
                            jqcc.cometchat.chatroomHeartbeat(1);
                        } else {
                            alert('<?php echo $chatrooms_language[57];?>');
                        }
                    });
                }
            },
            inviteChatroomUser: function() {
                loadCCPopup(this.crvariables.baseUrl+'modules/chatrooms/chatrooms.php?action=invite&roomid='+this.crvariables.currentroom+'&inviteid='+this.crvariables.currentp+'&basedata='+this.crvariables.basedata+'&roomname='+urlencode(this.crvariables.currentroomname), 'invite',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=200",400,200,'<?php echo $chatrooms_language[21];?>');
            },
            unbanChatroomUser: function() {
                loadCCPopup(this.crvariables.baseUrl+'modules/chatrooms/chatrooms.php?action=unban&roomid='+this.crvariables.currentroom+'&inviteid='+this.crvariables.currentp+'&basedata='+this.crvariables.basedata+'&roomname='+urlencode(this.crvariables.currentroomname)+'&time='+Math.random(), 'invite',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=200",400,200,'<?php echo $chatrooms_language[21];?>');
            },
            loadChatroomPro: function(uid,owner,longname,popoutmode) {
                if(popoutmode == 1){
                    showPrivateChat = 0;
                } else {
                    showPrivateChat = this.crvariables.checkBarEnabled;
                }
                loadCCPopup(this.crvariables.baseUrl+'modules/chatrooms/chatrooms.php?action=loadChatroomPro&apiAccess='+showPrivateChat+'&owner='+owner+'&roomid='+this.crvariables.currentroom+'&basedata='+this.crvariables.basedata+'&inviteid='+uid+'&roomname='+urlencode(this.crvariables.currentroomname), 'loadChatroomPro',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=365,height=100",365,75,longname);
            },
            silentroom: function(roomid, inviteid, roomname) {
                jqcc.cometchat.chatroom(roomid,roomname,1,inviteid,1);
            },
            checkChatroomPass: function(id,name,silent,password,clicked) {
                if (silent != 1) {
                    password=SHA1(password);
                }
                jqcc.post(this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=checkpassword", {password: password, id: id, basedata:this.crvariables.basedata} , function(data) {
                    if (data) {
                        if (data['s'] != 'INVALID_PASSWORD' && data['s'] != 'BANNED' && data['s'] !='INVALID_CHATROOM') {
                            <?php if (USE_COMET == 1 && COMET_CHATROOMS == 1):?>
                            cometuncall_function(jqcc.cometchat.getChatroomVars('currentroomcode'));
                            jqcc.cometchat.setChatroomVars('currentroomcode',data['cometid']);
                            chatroomcall_function(jqcc.cometchat.getChatroomVars('currentroomcode'),jqcc.cometchat.getChatroomVars('myid'));
                            <?php endif;?>
                            jqcc.cometchat.setChatroomVars('owner',parseInt(data['owner']));
                            jqcc.cometchat.setChatroomVars('myid',parseInt(data['userid']));
                            jqcc.cometchat.setChatroomVars('isModerator',parseInt(data['ismoderator']));
                            jqcc.cometchat.setChatroomVars('currentp',password);
                            jqcc.cometchat.setChatroomVars('initializeRoom',1);
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].hidetabs) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].hidetabs();
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].selectChatroom) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].selectChatroom(jqcc.cometchat.getChatroomVars('currentroom'),id);
                            jqcc.cometchat.setChatroomVars('currentroom',id);
                            jqcc.cometchat.setChatroomVars('ulh','');
                            jqcc.cometchat.setChatroomVars('timestamp',0);
                            jqcc.cometchat.setChatroomVars('currentroomname',name);
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].checkOwnership) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].checkOwnership(jqcc.cometchat.getChatroomVars('owner'),jqcc.cometchat.getChatroomVars('isModerator'),name);
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].setRoomName) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].setRoomName(name);
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadRoom) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadRoom(clicked);
                            clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadMobileChatroom) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadMobileChatroom();
                            jqcc.cometchat.setChatroomVars('cu_uids','');
                            jqcc.cometchat.chatroomHeartbeat(1);
                        } else {
                            if (data['s'] == 'BANNED') {
                                if (silent != 1) {
                                    alert ('<?php echo $chatrooms_language[37]; ?>');
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadMobileLobbyReverse) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadMobileLobbyReverse();
                                }
                            }else if (data['s'] == 'INVALID_CHATROOM'){
                                jqcc.cometchat.leaveChatroom();
                                alert('<?php echo $chatrooms_language[55];?>');
                            }else {
                                alert('<?php echo $chatrooms_language[23];?>');
                            }
                        }
                    }
                });
            },
            chatroom: function(id,name,type,invite,silent,clicked) {
                $('#cometchat_chatroomlist_'+id).find('.cometchat_msgcounttext_cr').text('0');
                $('#cometchat_chatroomlist_'+id).find('.cometchat_msgcount_cr').hide();
                name = urldecode(name);
                if (this.crvariables.currentroom != id) {
                    this.crvariables.password = '';
                    if (invite != '') {
                        this.crvariables.password = invite;
                    }
                    if (type == 1 || type == 2) {
                        if (silent != 1) {
                            if (typeof(jqcc[this.crvariables.calleeAPI].silentRoom) == "function")
                                jqcc[this.crvariables.calleeAPI].silentRoom(id, name, silent);
                        } else {
                            jqcc.cometchat.checkChatroomPass(id,name,silent,this.crvariables.password,clicked);
                        }
                    } else {
                        jqcc.cometchat.checkChatroomPass(id,name,silent,this.crvariables.password,clicked);
                    }
                } else {
                    if (typeof(jqcc[this.crvariables.calleeAPI].loadRoom) == "function")
                        jqcc[this.crvariables.calleeAPI].loadRoom(clicked);
                    clearTimeout(this.crvariables.heartbeatTimer);
                    jqcc.cometchat.chatroomHeartbeat(1);
                }
            },
            chatroomHeartbeat: function(forceUpdate) {
                jqcc.ajax({
                    url: this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=heartbeat",
                    data: {timestamp: this.crvariables.timestamp, currentroom: this.crvariables.currentroom, clh: this.crvariables.clh, ulh: this.crvariables.ulh, currentp: this.crvariables.currentp, popout:this.crvariables.apiAccess, force: forceUpdate ,basedata:this.crvariables.basedata},
                    type: 'post',
                    cache: false,
                    timeout: 10000,
                    error: function() {
                        clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                        jqcc.cometchat.setChatroomVars('heartbeatTime', jqcc.cometchat.getChatroomVars('minHeartbeat'));
                        jqcc.cometchat.setChatroomVars('heartbeatTimer', setTimeout( function() { jqcc.cometchat.chatroomHeartbeat(); },jqcc.cometchat.getChatroomVars('heartbeatTime')));
                    },
                    success: function(data) {
                        if (data) {
                            var fetchedUsers = 0;
                            jqcc.each(data, function(type,item) {
                                if (type == 'logout') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout();
                                }
                                if (type == 'userid') {
                                    jqcc.cometchat.setChatroomVars('myid',item);
                                }
                                if (type == 'chatrooms') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadChatroomList) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadChatroomList(item);
                                }
                                if (type == 'clh') {
                                    jqcc.cometchat.setChatroomVars('clh',item);
                                }
                                if (type == 'prepend') {
                                    jqcc.cometchat.setChatroomVars('prepend',item);
                                }
                                if (type == 'ulh') {
                                    jqcc.cometchat.setChatroomVars('ulh',item);
                                }
                                if (type == 'messages') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].displayChatroomMessage) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].displayChatroomMessage(item,fetchedUsers);
                                }
                                if (type == 'users') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateChatroomUsers) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateChatroomUsers(item,fetchedUsers);
                                }
								if (type == 'error') {
									if(item =='ROOM_DOES_NOT_EXISTS'){
										alert('<?php echo $chatrooms_language[55];?>');
									}
									jqcc.cometchat.leaveChatroom();
                                }
                            });
                        }
                        jqcc.cometchat.setChatroomVars('heartbeatCount',jqcc.cometchat.getChatroomVars('heartbeatCount')+1);
                        if (jqcc.cometchat.getChatroomVars('heartbeatCount') > 4) {
                            jqcc.cometchat.setChatroomVars('heartbeatTime',jqcc.cometchat.getChatroomVars('heartbeatTime') * 2);
                            jqcc.cometchat.setChatroomVars('heartbeatCount',1);
                        }
                        if (jqcc.cometchat.getChatroomVars('heartbeatTime') > jqcc.cometchat.getChatroomVars('maxHeartbeat')) {
                            jqcc.cometchat.setChatroomVars('heartbeatTime', jqcc.cometchat.getChatroomVars('maxHeartbeat'));
                        }
                        clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                        jqcc.cometchat.setChatroomVars('heartbeatTime', jqcc.cometchat.getChatroomVars('minHeartbeat'));
                        jqcc.cometchat.setChatroomVars('heartbeatTimer', setTimeout( function() { jqcc.cometchat.chatroomHeartbeat(); },jqcc.cometchat.getChatroomVars('heartbeatTime')));
                    }
                });
            },
            kickChatroomUser: function(kickid,kick){
                jqcc.ajax({
                    url: this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=kickUser",
                    type: "POST",
                    data: {kickid:kickid,currentroom:this.crvariables.currentroom,kick:kick, basedata:this.crvariables.basedata},
                    success: function(s) {
                        if (s == 1) {
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].kickid) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].kickid(kickid);
                            jqcc.cometchat.setChatroomVars('ulh','');
                        }
                    }
                });
            },
            banChatroomUser: function(banid,ban){
                jqcc.ajax({
                    url: this.crvariables.baseUrl+"modules/chatrooms/chatrooms.php?action=banUser",
                    type: "POST",
                    data: {banid:banid,currentroom:this.crvariables.currentroom,ban:ban, basedata:this.crvariables.basedata, popoutmode:this.crvariables.popoutmode},
                    success: function(s) {
                        if (s == 1) {
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].banid) == "function")
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].banid(banid);
                        }
                    }
                });
            },
            processcontrolmessage: function(incoming){
                if((incoming.message).indexOf('CC^CONTROL_')!=-1){
                    var data = incoming.message.split('_');
                    switch(data[1]){
                        case 'AVCHAT':
                            switch(data[2]){
                                case 'END':
                                    var controlparameters = {"type":"plugins", "name":"avchat", "method":"endcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'REJECT':
                                    var controlparameters = {"type":"plugins", "name":"avchat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'NO':
                                    var controlparameters = {"type":"plugins", "name":"avchat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'CANCEL':
                                    var controlparameters = {"type":"plugins", "name":"avchat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'BUSY':
                                    var controlparameters = {"type":"plugins", "name":"avchat", "method":"busycall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                default :
                                    message = '';
                                break;
                            }
                            break;
                        case 'AUDIOCHAT':
                            switch(data[2]){
                                case 'END':
                                    var controlparameters = {"type":"plugins", "name":"audiochat", "method":"endcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'REJECT':
                                    var controlparameters = {"type":"plugins", "name":"audiochat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'NO':
                                    var controlparameters = {"type":"plugins", "name":"audiochat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'CANCEL':
                                    var controlparameters = {"type":"plugins", "name":"audiochat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                case 'BUSY':
                                    var controlparameters = {"type":"plugins", "name":"audiochat", "method":"busycall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                default :
                                    message = '';
                                break;
                            }
                            break;
                        case 'BROADCAST':
                            switch(data[2]){
                                case 'END':
                                    var controlparameters = {"type":"plugins", "name":"broadcast", "method":"endcall", "params":{"grp":data[4], "chatroommode":1}};
                                break;
                                default :
                                    message = '';
                                break;
                            }
                            break;
                        default :
                            break;
                    }
                    return jqcc['cc'+controlparameters.name].processControlMessage(controlparameters);
                } else {
                    return incoming.message;
                }
            },
            chatroomready: function() {
                jqcc(function() {
                    if(jqcc.cometchat.getChatroomVars('calleeAPI') != 'mobilewebapp') {
                        attachPlaceholder();
                        if ((jqcc.cometchat.chatroommessageBeep()) == 1) {
                            jqcc('<audio id="messageBeep" style="display:none;"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.mp3" type="audio/mpeg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.ogg" type="audio/ogg"><source src="'+jqcc.cometchat.getChatroomVars('baseUrl')+'mp3/beep.wav" type="audio/wav"></audio>').appendTo(jqcc("body"));
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
                });
            }
        }
    );

<?php if (defined('USE_COMET') && USE_COMET == 1) { ?>
    function cometchatroomready() {
       jqcc.cometchat.chatroomready();
    }
<?php } else { ?>
    jqcc.cometchat.chatroomready();
<?php } ?>


    function SHA1(e){function rotate_left(n,s){var a=(n<<s)|(n>>>(32-s));return a};function lsb_hex(a){var b="";var i;var c;var d;for(i=0;i<=6;i+=2){c=(a>>>(i*4+4))&0x0f;d=(a>>>(i*4))&0x0f;b+=c.toString(16)+d.toString(16)}return b};function cvt_hex(a){var b="";var i;var v;for(i=7;i>=0;i--){v=(a>>>(i*4))&0x0f;b+=v.toString(16)}return b};function Utf8Encode(a){a=a.replace(/\r\n/g,"\n");var b="";for(var n=0;n<a.length;n++){var c=a.charCodeAt(n);if(c<128){b+=String.fromCharCode(c)}else if((c>127)&&(c<2048)){b+=String.fromCharCode((c>>6)|192);b+=String.fromCharCode((c&63)|128)}else{b+=String.fromCharCode((c>>12)|224);b+=String.fromCharCode(((c>>6)&63)|128);b+=String.fromCharCode((c&63)|128)}}return b};var f;var i,j;var W=new Array(80);var g=0x67452301;var h=0xEFCDAB89;var k=0x98BADCFE;var l=0x10325476;var m=0xC3D2E1F0;var A,B,C,D,E;var o;e=Utf8Encode(e);var p=e.length;var q=new Array();for(i=0;i<p-3;i+=4){j=e.charCodeAt(i)<<24|e.charCodeAt(i+1)<<16|e.charCodeAt(i+2)<<8|e.charCodeAt(i+3);q.push(j)}switch(p%4){case 0:i=0x080000000;break;case 1:i=e.charCodeAt(p-1)<<24|0x0800000;break;case 2:i=e.charCodeAt(p-2)<<24|e.charCodeAt(p-1)<<16|0x08000;break;case 3:i=e.charCodeAt(p-3)<<24|e.charCodeAt(p-2)<<16|e.charCodeAt(p-1)<<8|0x80;break}q.push(i);while((q.length%16)!=14)q.push(0);q.push(p>>>29);q.push((p<<3)&0x0ffffffff);for(f=0;f<q.length;f+=16){for(i=0;i<16;i++)W[i]=q[f+i];for(i=16;i<=79;i++)W[i]=rotate_left(W[i-3]^W[i-8]^W[i-14]^W[i-16],1);A=g;B=h;C=k;D=l;E=m;for(i=0;i<=19;i++){o=(rotate_left(A,5)+((B&C)|(~B&D))+E+W[i]+0x5A827999)&0x0ffffffff;E=D;D=C;C=rotate_left(B,30);B=A;A=o}for(i=20;i<=39;i++){o=(rotate_left(A,5)+(B^C^D)+E+W[i]+0x6ED9EBA1)&0x0ffffffff;E=D;D=C;C=rotate_left(B,30);B=A;A=o}for(i=40;i<=59;i++){o=(rotate_left(A,5)+((B&C)|(B&D)|(C&D))+E+W[i]+0x8F1BBCDC)&0x0ffffffff;E=D;D=C;C=rotate_left(B,30);B=A;A=o}for(i=60;i<=79;i++){o=(rotate_left(A,5)+(B^C^D)+E+W[i]+0xCA62C1D6)&0x0ffffffff;E=D;D=C;C=rotate_left(B,30);B=A;A=o}g=(g+A)&0x0ffffffff;h=(h+B)&0x0ffffffff;k=(k+C)&0x0ffffffff;l=(l+D)&0x0ffffffff;m=(m+E)&0x0ffffffff}var o=cvt_hex(g)+cvt_hex(h)+cvt_hex(k)+cvt_hex(l)+cvt_hex(m);return o.toLowerCase()}

    function MD5(j){function RotateLeft(a,b){return(a<<b)|(a>>>(32-b))}function AddUnsigned(a,b){var c,lY4,lX8,lY8,lResult;lX8=(a&0x80000000);lY8=(b&0x80000000);c=(a&0x40000000);lY4=(b&0x40000000);lResult=(a&0x3FFFFFFF)+(b&0x3FFFFFFF);if(c&lY4){return(lResult^0x80000000^lX8^lY8)}if(c|lY4){if(lResult&0x40000000){return(lResult^0xC0000000^lX8^lY8)}else{return(lResult^0x40000000^lX8^lY8)}}else{return(lResult^lX8^lY8)}}function F(x,y,z){return(x&y)|((~x)&z)}function G(x,y,z){return(x&z)|(y&(~z))}function H(x,y,z){return(x^y^z)}function I(x,y,z){return(y^(x|(~z)))}function FF(a,b,c,d,x,s,e){a=AddUnsigned(a,AddUnsigned(AddUnsigned(F(b,c,d),x),e));return AddUnsigned(RotateLeft(a,s),b)};function GG(a,b,c,d,x,s,e){a=AddUnsigned(a,AddUnsigned(AddUnsigned(G(b,c,d),x),e));return AddUnsigned(RotateLeft(a,s),b)};function HH(a,b,c,d,x,s,e){a=AddUnsigned(a,AddUnsigned(AddUnsigned(H(b,c,d),x),e));return AddUnsigned(RotateLeft(a,s),b)};function II(a,b,c,d,x,s,e){a=AddUnsigned(a,AddUnsigned(AddUnsigned(I(b,c,d),x),e));return AddUnsigned(RotateLeft(a,s),b)};function ConvertToWordArray(a){var b;var c=a.length;var d=c+8;var e=(d-(d%64))/64;var f=(e+1)*16;var g=Array(f-1);var h=0;var i=0;while(i<c){b=(i-(i%4))/4;h=(i%4)*8;g[b]=(g[b]|(a.charCodeAt(i)<<h));i++}b=(i-(i%4))/4;h=(i%4)*8;g[b]=g[b]|(0x80<<h);g[f-2]=c<<3;g[f-1]=c>>>29;return g};function WordToHex(a){var b="",WordToHexValue_temp="",lByte,lCount;for(lCount=0;lCount<=3;lCount++){lByte=(a>>>(lCount*8))&255;WordToHexValue_temp="0"+lByte.toString(16);b=b+WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2)}return b};function Utf8Encode(a){a=a.replace(/\r\n/g,"\n");var b="";for(var n=0;n<a.length;n++){var c=a.charCodeAt(n);if(c<128){b+=String.fromCharCode(c)}else if((c>127)&&(c<2048)){b+=String.fromCharCode((c>>6)|192);b+=String.fromCharCode((c&63)|128)}else{b+=String.fromCharCode((c>>12)|224);b+=String.fromCharCode(((c>>6)&63)|128);b+=String.fromCharCode((c&63)|128)}}return b};var x=Array();var k,AA,BB,CC,DD,a,b,c,d;var l=7,S12=12,S13=17,S14=22;var m=5,S22=9,S23=14,S24=20;var o=4,S32=11,S33=16,S34=23;var p=6,S42=10,S43=15,S44=21;j=Utf8Encode(j);x=ConvertToWordArray(j);a=0x67452301;b=0xEFCDAB89;c=0x98BADCFE;d=0x10325476;for(k=0;k<x.length;k+=16){AA=a;BB=b;CC=c;DD=d;a=FF(a,b,c,d,x[k+0],l,0xD76AA478);d=FF(d,a,b,c,x[k+1],S12,0xE8C7B756);c=FF(c,d,a,b,x[k+2],S13,0x242070DB);b=FF(b,c,d,a,x[k+3],S14,0xC1BDCEEE);a=FF(a,b,c,d,x[k+4],l,0xF57C0FAF);d=FF(d,a,b,c,x[k+5],S12,0x4787C62A);c=FF(c,d,a,b,x[k+6],S13,0xA8304613);b=FF(b,c,d,a,x[k+7],S14,0xFD469501);a=FF(a,b,c,d,x[k+8],l,0x698098D8);d=FF(d,a,b,c,x[k+9],S12,0x8B44F7AF);c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);a=FF(a,b,c,d,x[k+12],l,0x6B901122);d=FF(d,a,b,c,x[k+13],S12,0xFD987193);c=FF(c,d,a,b,x[k+14],S13,0xA679438E);b=FF(b,c,d,a,x[k+15],S14,0x49B40821);a=GG(a,b,c,d,x[k+1],m,0xF61E2562);d=GG(d,a,b,c,x[k+6],S22,0xC040B340);c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);b=GG(b,c,d,a,x[k+0],S24,0xE9B6C7AA);a=GG(a,b,c,d,x[k+5],m,0xD62F105D);d=GG(d,a,b,c,x[k+10],S22,0x2441453);c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);b=GG(b,c,d,a,x[k+4],S24,0xE7D3FBC8);a=GG(a,b,c,d,x[k+9],m,0x21E1CDE6);d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);c=GG(c,d,a,b,x[k+3],S23,0xF4D50D87);b=GG(b,c,d,a,x[k+8],S24,0x455A14ED);a=GG(a,b,c,d,x[k+13],m,0xA9E3E905);d=GG(d,a,b,c,x[k+2],S22,0xFCEFA3F8);c=GG(c,d,a,b,x[k+7],S23,0x676F02D9);b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);a=HH(a,b,c,d,x[k+5],o,0xFFFA3942);d=HH(d,a,b,c,x[k+8],S32,0x8771F681);c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);a=HH(a,b,c,d,x[k+1],o,0xA4BEEA44);d=HH(d,a,b,c,x[k+4],S32,0x4BDECFA9);c=HH(c,d,a,b,x[k+7],S33,0xF6BB4B60);b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);a=HH(a,b,c,d,x[k+13],o,0x289B7EC6);d=HH(d,a,b,c,x[k+0],S32,0xEAA127FA);c=HH(c,d,a,b,x[k+3],S33,0xD4EF3085);b=HH(b,c,d,a,x[k+6],S34,0x4881D05);a=HH(a,b,c,d,x[k+9],o,0xD9D4D039);d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);b=HH(b,c,d,a,x[k+2],S34,0xC4AC5665);a=II(a,b,c,d,x[k+0],p,0xF4292244);d=II(d,a,b,c,x[k+7],S42,0x432AFF97);c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);b=II(b,c,d,a,x[k+5],S44,0xFC93A039);a=II(a,b,c,d,x[k+12],p,0x655B59C3);d=II(d,a,b,c,x[k+3],S42,0x8F0CCC92);c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);b=II(b,c,d,a,x[k+1],S44,0x85845DD1);a=II(a,b,c,d,x[k+8],p,0x6FA87E4F);d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);c=II(c,d,a,b,x[k+6],S43,0xA3014314);b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);a=II(a,b,c,d,x[k+4],p,0xF7537E82);d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);c=II(c,d,a,b,x[k+2],S43,0x2AD7D2BB);b=II(b,c,d,a,x[k+9],S44,0xEB86D391);a=AddUnsigned(a,AA);b=AddUnsigned(b,BB);c=AddUnsigned(c,CC);d=AddUnsigned(d,DD)}var q=WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);return q.toLowerCase()}

    function base64_encode(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c,o2,o3,h1,h2,h3,h4,bits,i=0,ac=0,enc="",tmp_arr=[];if(!a){return a}a=this.utf8_encode(a+'');do{c=a.charCodeAt(i++);o2=a.charCodeAt(i++);o3=a.charCodeAt(i++);bits=c<<16|o2<<8|o3;h1=bits>>18&0x3f;h2=bits>>12&0x3f;h3=bits>>6&0x3f;h4=bits&0x3f;tmp_arr[ac++]=b.charAt(h1)+b.charAt(h2)+b.charAt(h3)+b.charAt(h4)}while(i<a.length);enc=tmp_arr.join('');switch(a.length%3){case 1:enc=enc.slice(0,-2)+'==';break;case 2:enc=enc.slice(0,-1)+'=';break}return enc}

    function base64_decode(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c,o2,o3,h1,h2,h3,h4,bits,i=0,ac=0,dec="",tmp_arr=[];if(!a){return a}a+='';do{h1=b.indexOf(a.charAt(i++));h2=b.indexOf(a.charAt(i++));h3=b.indexOf(a.charAt(i++));h4=b.indexOf(a.charAt(i++));bits=h1<<18|h2<<12|h3<<6|h4;c=bits>>16&0xff;o2=bits>>8&0xff;o3=bits&0xff;if(h3==64){tmp_arr[ac++]=String.fromCharCode(c)}else if(h4==64){tmp_arr[ac++]=String.fromCharCode(c,o2)}else{tmp_arr[ac++]=String.fromCharCode(c,o2,o3)}}while(i<a.length);dec=tmp_arr.join('');dec=this.utf8_decode(dec);return dec}

    function utf8_decode(a){var b=[],i=0,ac=0,c1=0,c2=0,c3=0;a+='';while(i<a.length){c1=a.charCodeAt(i);if(c1<128){b[ac++]=String.fromCharCode(c1);i++}else if((c1>191)&&(c1<224)){c2=a.charCodeAt(i+1);b[ac++]=String.fromCharCode(((c1&31)<<6)|(c2&63));i+=2}else{c2=a.charCodeAt(i+1);c3=a.charCodeAt(i+2);b[ac++]=String.fromCharCode(((c1&15)<<12)|((c2&63)<<6)|(c3&63));i+=3}}return b.join('')}

    function utf8_encode(a){var b=(a+'');var c="";var d,end;var e=0;d=end=0;e=b.length;for(var n=0;n<e;n++){var f=b.charCodeAt(n);var g=null;if(f<128){end++}else if(f>127&&f<2048){g=String.fromCharCode((f>>6)|192)+String.fromCharCode((f&63)|128)}else{g=String.fromCharCode((f>>12)|224)+String.fromCharCode(((f>>6)&63)|128)+String.fromCharCode((f&63)|128)}if(g!==null){if(end>d){c+=b.substring(d,end)}c+=g;d=end=n+1}}if(end>d){c+=b.substring(d,b.length)}return c}

    function urlencode (string) {
            return base64_encode(string);
    }

    function urldecode (string) {
            return base64_decode(string);
    }

    function getURLParameter (name) {
            return decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
    }

    /* Copyright (c) 2006 Klaus Hartl (stilbuero.de)
     http://www.opensource.org/licenses/mit-license.php*/

    jqcc.cookie=function(a,b,c){if(typeof b!='undefined'){c=c||{};if(b===null){b='';c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=jqcc.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};

    /* SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License
     http://www.opensource.org/licenses/mit-license.php */

    if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectCCUtil=="undefined"){deconcept.SWFObjectCCUtil=new Object();}deconcept.SWFObjectCC=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectCCUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObjectCC.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObjectCC.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectCCUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectCCUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObjectCC.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectCCUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectCCUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectCCUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObjectCC;var SWFObjectCC=deconcept.SWFObjectCC;

    function attachPlaceholder(){
        jqcc('[placeholder]').not('#password').focus(function() {
            var input = jqcc(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
                input.removeClass('placeholder');
            }
            }).blur(function() {
            var input = jqcc(this);
            if (input.val() == '') {
                input.addClass('placeholder');
                input.val(input.attr('placeholder'));
            }
        }).blur();

        jqcc('[placeholder]').parents('form').submit(function() {
            jqcc(this).find('[placeholder]').each(function() {
                var input = jqcc(this);
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            });
        });
    }


<?php
foreach ($crplugins as $plugin) {
	if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."init.js")) {
		include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."init.js");
	}
}

if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."init.js")) {
	include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."init.js");
}

if (USE_COMET == 1 && COMET_CHATROOMS == 1) {
	include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."transports".DIRECTORY_SEPARATOR.TRANSPORT.DIRECTORY_SEPARATOR.'config.php');
	include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."transports".DIRECTORY_SEPARATOR.TRANSPORT.DIRECTORY_SEPARATOR.'includes.php');
}

if ($lightboxWindows == 1) {
	include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'scroll.js');
}