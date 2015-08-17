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

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
	include (dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
unset($_SESSION['cometchat']['cometchat_chatroomslist']);
if (!empty($_REQUEST['basedata'])) {
	$_SESSION['basedata'] = $_REQUEST['basedata'];
}
if ($userid == 0 || in_array($userid,$bannedUserIDs) || in_array($_SERVER['REMOTE_ADDR'],$bannedUserIPs) || ($userid > 10000000 && !$crguestsMode)){
	if (in_array($userid,$bannedUserIDs)) {
		$chatrooms_language[0] = $bannedMessage;
	}
	$baseUrl = BASE_URL;
	$loggedOut = $chatrooms_language[0];
	if(USE_CCAUTH == 1){
		$loggedOut .= ' <a href="javascript:void(0);" class="socialLogin">'.$chatrooms_language[65].'</a> '.$chatrooms_language[66];
	}
	echo <<<EOD
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="cache-control" content="no-cache">
			<meta http-equiv="pragma" content="no-cache">
			<meta http-equiv="expires" content="-1">
			<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
			<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms" />
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
			<script type="text/javascript">
				$('.socialLogin').live('click',function(){
					if(typeof(parent) != 'undefined' && parent != null && parent != self){
						var controlparameters = {"type":"functions", "name":"socialauth", "method":"login", "params":{"url":"{$baseUrl}functions/login/loginOptions.php"}};
						controlparameters = JSON.stringify(controlparameters);
						parent.postMessage('CC^CONTROL_'+controlparameters,'*');
					} else {

					}
			    });
			</script>
		</head>
		<body>
			<div class="containermessage">
			{$loggedOut}
			</div>
		</body>
	</html>
EOD;
} else {
	$joinroom = '';
	$dynamicChatroom = 0;
	$leaveroom = "";
	if((!empty($_REQUEST['action']) && $_REQUEST['action']='dynamicChatroom') && (!empty($_REQUEST['name']))){
		global $userid;
		global $cookiePrefix;
		$name = $_REQUEST['name'];
		$type = '3';
		$sql = ("select id,name,type from cometchat_chatrooms where name = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($name))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$result = mysqli_fetch_assoc($query);
		if(empty($result['id'])) {
			if ($userid > 0) {
				$password = '';
				$sql = ("insert into cometchat_chatrooms (name,createdby,lastactivity,password,type) values ('".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($name))."','".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".getTimeStamp()."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($password))."','3')");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				$currentroom = mysqli_insert_id($GLOBALS['dbh']);
				$_GET['id'] = $currentroom;
			}
		}elseif($result['type'] == 3){
			$_GET['id'] =$result['id'];
		}
		$leaveroom = "setTimeout(function(){\$('.welcomemessage a:first, span:first').remove();},500);";
		$dynamicChatroom = 1;
	}
	if (!empty($_COOKIE[$cookiePrefix.'chatroom']) && empty($_GET['roomid']) && empty($_GET['id'])) {
		$info = explode(':',base64_decode($_COOKIE[$cookiePrefix.'chatroom']));
		$_GET['roomid'] = intval($info[0]);
		$_GET['inviteid'] = $info[1];
		$_GET['roomname'] = $info[2];
	}
	if (!empty($_GET['roomid'])) {
		$joinroom = "jqcc.cometchat.silentroom('{$_GET['roomid']}','{$_GET['inviteid']}','{$_GET['roomname']}');";
		$autoLogin = 0;
	}
	if (empty($_GET['id']) && !empty($autoLogin)) {
		$_GET['id'] = $autoLogin;
	}
	if (!empty($_GET['id'])) {
		$sql = ("select id,name,type from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_GET['id'])."' and (type = '0' or type='3') limit 1");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$room = mysqli_fetch_assoc($query);
		if ($room['id'] > 0) {
			$roomname = base64_encode($room['name']);
			$joinroom = "jqcc.cometchat.silentroom('{$_GET['id']}','','{$roomname}');";
		}
	}
	$loadjs = "$(function() {
					".$joinroom."
					".$leaveroom."
				});";

	if (defined('USE_COMET') && USE_COMET == 1) {
		$loadjs = "function chatroomready(){
					".$loadjs."
				   }";
	}
	$ccauthlogout = '';
	if(USE_CCAUTH == "1"){
    	$ccauthlogout = '<div class="cometchat_tooltip" id="cometchat_authlogout" title="'.$language[80].'"></div>';
	}

	$listItems = "";
	if ($dynamicChatroom == 0) {
		$listItems .= <<<EOD
				<li id="lobbytab" class="tab_selected">
					<a href="javascript:void(0);" onclick="jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadLobby()">{$chatrooms_language[3]}</a>
				</li>
EOD;
		if ($allowUsers == 1 ) {
			$listItems .= <<<EOD
				<li id="createtab">
					<a href="javascript:void(0);" onclick="javascript:jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].createChatroom()">{$chatrooms_language[2]}</a>
				</li>
EOD;
		}
	}

	echo <<<EOD
	<!DOCTYPE html>
		<html>
			<head>
				<title>{$chatrooms_language[35]}</title>
				<meta http-equiv="cache-control" content="no-cache">
				<meta http-equiv="pragma" content="no-cache">
				<meta http-equiv="expires" content="-1">
				<meta http-equiv="content-type" content="text/html; charset="utf-8"/>
				<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms" />
				<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
				<script src="../../js.php?type=module&name=chatrooms&basedata={$_REQUEST['basedata']}"></script>
				<script src="../../js.php?type=core&name=scroll"></script>
				<script type="text/javascript">
					{$loadjs}
				</script>
				<script type="text/javascript">
					var controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{}};
		            controlparameters = JSON.stringify(controlparameters);
		            if(typeof(parent) != 'undefined' && parent != null && parent != self){
		                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
		            } else {
		                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
		            }
		            var cookiePrefix = '<?php echo $cookiePrefix; ?>';
		            $(document).ready(function(){
			            var auth_logout = $("div#cometchat_authlogout");
			            var baseUrl = jqcc.cometchat.getBaseUrl();
			            auth_logout.mouseenter(function(){
		                    auth_logout.css('opacity','1');
		                });
		                auth_logout.mouseleave(function(){
		                    auth_logout.css('opacity','0.5');
		                });
	                    auth_logout.click(function(event){
	                    	auth_logout.unbind('click');
	                        event.stopPropagation();
	                        auth_logout.css('background','url('+baseUrl+'themes/standard/images/loading.gif) no-repeat top left');
	                        jqcc.ajax({
	                            url: baseUrl+'functions/login/logout.php',
	                            dataType: 'jsonp',
	                            success: function(){
	                            	auth_logout.css('background','url('+baseUrl+'themes/standard/images/logout.png) no-repeat top left');
	                            	if(typeof(jqcc.cometchat.getThemeVariable) != 'undefined') {
		                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').click();
		                                jqcc.cometchat.setSessionVariable('openChatboxId', '');
		                            }
	                                jqcc.cookie(cookiePrefix+"loggedin", null, {path: '/'});
	                                jqcc.cookie(cookiePrefix+"state", null, {path: '/'});
	                                jqcc.cookie(cookiePrefix+"jabber", null, {path: '/'});
	                                jqcc.cookie(cookiePrefix+"jabber_type", null, {path: '/'});
	                                jqcc.cookie(cookiePrefix+"hidebar", null, {path: '/'});
	                                var controlparameters = {"type":"themes", "name":"cometchat", "method":"loggedout", "params":{}};
						            controlparameters = JSON.stringify(controlparameters);
						            if(typeof(parent) != 'undefined' && parent != null && parent != self){
						            	parent.postMessage('CC^CONTROL_'+controlparameters,'*');
						            } else {
						                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
						            }
	                            },
	                            error: function(){
	                            	alert(language[81]);
	                            }
	                        });
	                    });
					});
				</script>
			</head>
			<body>
				<div id="container">
					<div class="topbar">
						<ol class="tabs">
							{$listItems}
							<li id="currentroomtab" style="display:none">
							</li>
					    </ol>
					    {$ccauthlogout}
						<div style="clear:both"></div>
						<div class="topbar_text">
							<div class="welcomemessage">{$chatrooms_language[1]}</div>
							<div id="plugins"></div>
						</div>
						<div style="clear:both"></div>
					</div>
					<div style="clear:both"></div>
					<div id="lobby">
						<div class="lobby_rooms content_div" id="lobby_rooms"></div>
					</div>
					<div class="content_div" id="currentroom" style="display:none">
						<div id="currentroom_left" class="content_div">
							<div id="currentroom_convo">
								<div id="currentroom_convotext"></div>
							</div>
							<div style="clear:both"></div>
							<div class="cometchat_tabcontentinput">
								<textarea class="cometchat_textarea" placeholder='$chatrooms_language[64]'></textarea>
								<div class="cometchat_tabcontentsubmit" style="display:none"></div>
							</div>
						</div>
						<div id="currentroom_right" class="content_div">
							<div id="currentroom_users" class="content_div"></div>
						</div>
					</div>
					<div class="content_div" id="create" style="display:none">
						<div id="currentroom_left" class="content_div">
							<form class="create" onsubmit="javascript:jqcc.cometchat.createChatroomSubmit(); return false;">
								<div style="clear:both;padding-top:10px"></div>
								<div class="create_name">{$chatrooms_language[27]}</div>
								<div class="create_value">
									<input type="text" id="name" class="create_input" placeholder="{$chatrooms_language[63]}" />
								</div>
								<div style="clear:both;padding-top:10px"></div>
								<div class="create_name">{$chatrooms_language[28]}</div>
								<div class="create_value" >
									<select id="type" onchange="jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].crcheckDropDown(this)" class="create_input">
										<option value="0">{$chatrooms_language[29]}</option>
										<option value="1">{$chatrooms_language[30]}</option>
										<option value="2">{$chatrooms_language[31]}</option>
									</select>
								</div>
								<div style="clear:both;padding-top:10px"></div>
								<div class="create_name password_hide">{$chatrooms_language[32]}</div>
								<div class="create_value password_hide">
									<input id="password" type="password" autocomplete="off" class="create_input" />
								</div>
								<div style="clear:both;padding-top:10px"></div>
								<div class="create_name">&nbsp;</div>
								<div class="create_value">
									<input type="submit" class="invitebutton" value="{$chatrooms_language[33]}" />
								</div>
							</form>
						</div>
					</div>
				</div>
				<script>
				</script>
			</body>
		</html>
EOD;
}
?>