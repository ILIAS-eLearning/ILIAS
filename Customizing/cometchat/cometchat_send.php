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

include_once dirname(__FILE__).DIRECTORY_SEPARATOR."cometchat_init.php";

if (isset($_REQUEST['status'])) {

	if ($userid > 0) {

		$message = $_REQUEST['status'];

		$sql = ("insert into cometchat_status (userid,status) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($message))."') on duplicate key update status = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($message))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

		if ($message == 'offline') {
			$_SESSION['cometchat']['cometchat_sessionvars']['buddylist'] = 0;
		}

		if (function_exists('hooks_activityupdate')) {
			hooks_activityupdate($userid,$message);
		}
	}

	if (!empty($_GET['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'(1)';
	} else {
		echo "1";
	}
	exit(0);
}

if (isset($_REQUEST['guestname']) && $userid > 0) {
	$guestname = mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($_REQUEST['guestname']));

	$sql = ("UPDATE `cometchat_guests` SET name='".mysqli_real_escape_string($GLOBALS['dbh'],$guestname)."' where id='".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

	$_SESSION['cometchat']['username'] =  $guestnamePrefix.$guestname;

	if (!empty($_GET['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'(1)';
	} else {
		echo "1";
	}
	exit(0);
}

if (isset($_REQUEST['statusmessage'])) {
	$message = $_REQUEST['statusmessage'];

	if (empty($_SESSION['cometchat']['statusmessage']) || ($_SESSION['cometchat']['statusmessage'] != $message)) {

		$sql = ("insert into cometchat_status (userid,message) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($message))."') on duplicate key update message = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($message))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

		$_SESSION['cometchat']['statusmessage'] = $message;

		if (function_exists('hooks_statusupdate')) {
			hooks_statusupdate($userid,$message);
		}
	}

	if (!empty($_GET['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'(1)';
	} else {
		echo "1";
	}

	exit(0);
}


if ( (!empty($_REQUEST['to']) && isset($_REQUEST['message']) && $_REQUEST['message']!='')||(!empty($_REQUEST['broadcast']))) {

	if(!empty($_REQUEST['broadcast'])){
		$broadcasttemp = $_REQUEST['broadcast'];
		$broadcast = array();
		$broadcast_toids = array();
		$bsize =sizeof($broadcasttemp);
		for ($i=0; $i < $bsize ; $i++) {
			array_push($broadcast_toids, $broadcasttemp[$i]);
			$tempa = array('to' => $broadcasttemp[$i],'message' => $broadcasttemp[++$i], 'dir' => 0 );
			array_push($broadcast, $tempa);
		}
	}else{
		$to = $_REQUEST['to'];
		$message = $_REQUEST['message'];
	}
	if ($userid > 0) {

		if (!in_array($userid,$bannedUserIDs) && !in_array($_SERVER['REMOTE_ADDR'],$bannedUserIPs)) {

			if(empty($_REQUEST['broadcast'])){
				$response = sendMessage($to,$message,0);
			}else{
				$response = broadcastMessage($broadcast);
			}

			if (!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp' && empty($_REQUEST['v2'])) {
				$response = $response['id'];
			}
			if (!empty($_GET['callback'])) {
				header('content-type: application/json; charset=utf-8');
				sendCCResponse($_GET['callback'].'('.json_encode($response).')');
			} else {
				sendCCResponse(json_encode($response));
			}

			if(empty($_REQUEST['broadcast'])){
				parsePusher($to,$response['id'],$_SESSION['cometchat']['user']['n'].": ".$response['m']);
			}else{
				if(USE_COMET == '1'){
					publishCometMessages($broadcast,$response[0]['id']);
				}
				foreach ($response as $rkey => $rvalue) {
					parsePusher($rvalue['from'],$rvalue['id'],$_SESSION['cometchat']['user']['n'].": ".$rvalue['m']);
				}
			}
		} else if(empty($_REQUEST['broadcast'])){
			$sql = ("insert into cometchat (cometchat.from,cometchat.to,cometchat.message,cometchat.sent,cometchat.read,cometchat.direction) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."', '".mysqli_real_escape_string($GLOBALS['dbh'],$to)."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize($bannedMessage))."','".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."',0,2)");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }


			if (!empty($_GET['callback'])) {
				header('content-type: application/json; charset=utf-8');
				echo $_GET['callback'].'()';
			}
		}
		if (function_exists('hooks_message')) {
			hooks_message($userid,$to,$message);
		}
	}

	exit(0);
}