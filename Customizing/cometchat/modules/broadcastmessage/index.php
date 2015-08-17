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
function index() {
	$baseUrl = BASE_URL;
	global $userid;
	global $broadcastmessage_language;
	global $language;
	global $embed;
	global $embedcss;
	global $guestsMode;
	global $basedata;
	global $sleekScroller;
	global $inviteContent;
	if (!empty($_REQUEST['basedata'])) {
		$basedata = $_REQUEST['basedata'];
	}
	$popoutmode = 0;
	if(empty($userid)){
		echo <<<EOD
		    <!DOCTYPE html>
			<html>
				<head></head>
				<body style="margin:0px">
					<div style="background: #FFF;font-family: Tahoma,Verdana,Arial,'Bitstream Vera Sans',sans-serif;font-size: 12px;padding: 10px;" class="container">
					$broadcastmessage_language[7]
					</div>
				<body>
			</html>
EOD;
		exit;
	}
	if(!empty($_GET['popoutmode'])){
		$popoutmode = 1;
	}

	userSelection(1);

	$embedcss = 'web';

	$extrajs = "";
	if ($sleekScroller == 1) {
		$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
	}

	if($popoutmode==1){
		$addmsg = 1;
	}else{
		$addmsg = 0;
	}

echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<script src="../../js.php?type=core&name=jquery"></script>
		<script src="../../js.php?type=module&name=broadcastmessage&basedata={$basedata}"></script>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=broadcastmessage" />
		{$extrajs}
	</head>
	<body>
		<div style="background: #FFF;">
			<div class="container">
				<div>
					<div class="cometchat_broadcastMessage" >
						<div class="cc_broadcasttopbar" >
							<div id="cc_selectallusers" onclick="cc_selectallusers()">{$broadcastmessage_language[5]}</div>
							<div id="cc_deselectallusers" onclick="cc_deselectallusers()">{$broadcastmessage_language[6]}</div>
							<div style=" display: inline-block; margin-left: 3px;margin-right: 3px;"> | </div>
							<div id="cc_refreshbroadcastusers">{$broadcastmessage_language[1]}</div>
						</div>
						<div id="cometchat_broadcastsearchbar" style="display: block;">
							<input type="text" name="cometchat_broadcastsearch" class="cometchat_broadcastsearch" id="cometchat_broadcastsearch" placeholder="{$broadcastmessage_language[4]}">
						</div>
						<div class="inviteuserboxes" id="inviteuserboxes">
							{$inviteContent}
						</div>

						<div class="broadcastMessage_textarea_container" >
							<div class="cometchat_tabcontentsubmit"></div>
							<div class="cometchat_tabcontentinput">
									<textarea class="cometchat_textarea" addmsg="{$addmsg}" id="cometchat_broadcastMessage_textarea" placeholder="{$broadcastmessage_language[11]}"></textarea>
							</div>
							</div>
							<div id="ccbroadcastsucc" class="ccbroadcastnotif">{$broadcastmessage_language[8]}</div>
							<div id="ccbroadcastuserrel" class="ccbroadcastnotif">{$broadcastmessage_language[10]}</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
EOD;
}

function userSelection($silent = 0) {
	$baseUrl = BASE_URL;
	global $userid;
	global $broadcastmessage_language;
	global $language;
	global $embed;
	global $embedcss;
	global $guestsMode;
	global $basedata;
	global $sleekScroller;
	global $inviteContent;
	global $chromeReorderFix;
	global $hideOffline;
	global $plugins;
	$status['available'] = $language[30];
	$status['busy'] = $language[31];
	$status['offline'] = $language[32];
	$status['invisible'] = $language[33];
	$status['away'] = $language[34];
	$time = getTimeStamp();

	$onlineCacheKey = 'all_online';
	if($userid > 10000000){
		$onlineCacheKey .= 'guest';
	}

	if (!is_array($buddyList = getCache($onlineCacheKey))) {
		$buddyList = array();
		$sql = getFriendsList($userid,$time);
		if($guestsMode){
	    	$sql = getGuestsList($userid,$time,$sql);
		}
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		while ($chat = mysqli_fetch_assoc($query)) {
			if (((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') || $chat['isdevice'] == 1) {
				if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
					$chat['status'] = 'available';
				}
			} else {
				$chat['status'] = 'offline';
			}

			$avatar = getAvatar($chat['avatar']);

			if (!empty($chat['username'])) {
				if (function_exists('processName')) {
					$chat['username'] = processName($chat['username']);
				}
				if ($chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 's' => $chat['status']);
				}
			}
		}
	}

	if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
		$tempBuddyList = array();
		if (!is_array($friendIds = getCache('friend_ids_of_'.$userid))) {
			$friendIds=array();
			$sql = getFriendsIds($userid);
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) == 1 ){
				$buddy = mysqli_fetch_assoc($query);
				$friendIds = explode(',',$buddy['friendid']);
			}else{
				while($buddy = mysqli_fetch_assoc($query)){
					$friendIds[]=$buddy['friendid'];
				}
			}
			setCache('friend_ids_of_'.$userid,$friendIds, 30);
		}
		foreach($friendIds as $friendId) {
			$friendId = $chromeReorderFix.$friendId;
			if (isset($buddyList[$friendId])) {
				$tempBuddyList[$friendId] = $buddyList[$friendId];
			}
		}
		$buddyList = $tempBuddyList;
	}

	if (function_exists('hooks_forcefriends') && is_array(hooks_forcefriends())) {
		$buddyList = array_merge(hooks_forcefriends(),$buddyList);
	}

	$blockList = array();
	if (in_array('block',$plugins)) {

		$blockedIds = getBlockedUserIDs();

		foreach ($blockedIds as $bid) {
			array_push($blockList,$bid);
			if (isset($buddyList[$chromeReorderFix.$bid])) {
				unset($buddyList[$chromeReorderFix.$bid]);
			}
		}
	}

	if (isset($buddyList[$chromeReorderFix.$userid])) {
		unset($buddyList[$chromeReorderFix.$userid]);
	}

	if(empty($silent)){
		$buddyOrder = array();
		$buddyGroup = array();
		$buddyStatus = array();
		$buddyName = array();
		$buddyGuest = array();
		foreach ($buddyList as $key => $row) {
			if (empty($row['g'])) { $row['g'] = ''; }
			$buddyGroup[$key]  = strtolower($row['g']);
			$buddyStatus[$key] = strtolower($row['s']);
			$buddyName[$key] = strtolower($row['n']);
			if ($row['g'] == '') {
				$buddyOrder[$key] = 1;
			} else {
				$buddyOrder[$key] = 0;
			}
			$buddyGuest[$key] = 0;
			if ($row['id']>10000000) {
				$buddyGuest[$key] = 1;
			}
		}
		array_multisort($buddyOrder, SORT_ASC, $buddyGroup, SORT_STRING, $buddyStatus, SORT_STRING, $buddyGuest, SORT_ASC, $buddyName, SORT_STRING, $buddyList);
		$response['buddyList'] = $buddyList;
		$response['status'] = $status;
	}else{
		$s['available'] = '';
		$s['away'] = '';
		$s['busy'] = '';
		$s['offline'] = '';
		foreach ($buddyList as $buddy) {
			$s[$buddy['s']] .= '<div class="invite_1"><div class="invite_2" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><img height=30 width=30 src="'.$buddy['a'].'" /></div><div class="invite_3" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><span class="invite_name">'.$buddy['n'].'</span><br/><span class="invite_5">'.$status[$buddy['s']].'</span></div><input type="checkbox" name="to[]" value="'.$buddy['id'].'" id="check_'.$buddy['id'].'" class="invite_4" /></div>';
		}

		$inviteContent = '';
		$invitehide = '';
		$inviteContent = $s['available']."".$s['away']."".$s['offline'];
		if(empty($inviteContent)) {
			$inviteContent = '<div style= "padding-top:6px">'.$broadcastmessage_language[2].'</div>';
			$invitehide = 'style="display:none;"';
		}
	}
	if(empty($silent)){
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'('.json_encode($response).')';
	}else{
		return $inviteContent;
	}

}

function sendbroadcast() {
	global $userid;
	global $bannedUserIDs;
	global $bannedUserIPs;
	$message = $_REQUEST['message'];
	$broadcast_toids = (explode(",",$_REQUEST['to']));
	$message = sanitize($_REQUEST['message']);
	$broadcast = array();
	if (!in_array($userid,$bannedUserIDs) && !in_array($_SERVER['REMOTE_ADDR'],$bannedUserIPs)) {
		for ($i=0; $i <sizeof($broadcast_toids) ; $i++) {
			$tempMsg = array('to' => $broadcast_toids[$i],'message' => $message, 'dir' => 0 );
			array_push($broadcast, $tempMsg);
		}

		$_REQUEST['broadcast'] = 1;
		$response = broadcastMessage($broadcast,$broadcast_toids);
		if (isset($_GET['callback'])) {
			header('content-type: application/json; charset=utf-8');
			sendCCResponse($_GET['callback'].'('.json_encode($response).')');
		} else {
			sendCCResponse(json_encode($response));
		}
		publishCometMessages($broadcast);
		foreach ($response as $rkey => $rvalue) {
			parsePusher($to,$rvalue['id'],$_SESSION['cometchat']['user']['n'].": ".$rvalue['m']);
		}
	}
}


$allowedActions = array('index','sendbroadcast','userSelection');
$action = 'index';

if (!empty($_GET['action']) && function_exists($_GET['action']) && in_array($_GET['action'],$allowedActions)) {
       $action = $_GET['action'];
}
call_user_func($action);