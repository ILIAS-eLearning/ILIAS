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

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

if ($p_<1) exit;

if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'block') {

	$id = $_REQUEST['to'];

	$sql = "insert into cometchat_block (fromid, toid) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],$id)."')";
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	removeCache('blocked_id_of_'.$userid);
	removeCache('blocked_id_of_'.$id);

	$response = array();
	$response['id'] = $id;
	$error = mysqli_error($GLOBALS['dbh']);
	if (!empty($error)) {
		$response['result'] = "0";
		header('content-type: application/json; charset=utf-8');
		$response['error'] = mysqli_error($GLOBALS['dbh']);
		echo $_REQUEST['callback'].'('.json_encode($response).')';
		exit;
	}

	$response['result'] = "1";

	if (!empty($_REQUEST['callback']) || !empty($_REQUEST['callbackfn'])) {
		header('content-type: application/json; charset=utf-8');
		if(empty($_REQUEST['v3'])){
			echo $_REQUEST['callback'].'('.json_encode($response).')';
		} else {
			echo json_encode($response);
		}
	}

} elseif (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'unblock') {

	if(empty($_REQUEST['id'])){
		$id = intval($_REQUEST['to']);
	} else {
		$id = intval($_REQUEST['id']);
	}
	$embed = '';
	$embedcss = '';

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
	}

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
		$embed = 'desktop';
		$embedcss = 'embed';
	}

	$sql = "delete from cometchat_block where toid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'";
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$affectedRows = mysqli_affected_rows($GLOBALS['dbh']);
	removeCache('blocked_id_of_'.$userid);
	removeCache('blocked_id_of_'.$id);

	$response = array();
	$response['id'] = $id;
	$error = mysqli_error($GLOBALS['dbh']);

	if(empty($_REQUEST['v3'])){
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		$ts = time();
		header("Location: index.php?basedata={$_REQUEST['basedata']}&embed={$embed}&ts={$ts}\r\n");
		exit;
	} else {
		header('content-type: application/json; charset=utf-8');
		if (!empty($error)) {
			$response['result'] = "0";
			$response['error'] = mysqli_error($GLOBALS['dbh']);
		}else if($affectedRows == 0){
			$response['result'] = "0";
			$response['error'] = 'NOT_A_BLOCKED_USER';
		} else {
			$response['result'] = "1";
		}
		echo json_encode($response);
		exit;
	}

} else {

	$embed = '';
	$embedcss = '';

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
	}

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
		$embed = 'desktop';
		$embedcss = 'embed';
	}

	$usertable = TABLE_PREFIX.DB_USERTABLE;
	$usertable_username = DB_USERTABLE_NAME;
	$usertable_userid = DB_USERTABLE_USERID;
	$body = '';
	$number = 0;

	$sql = ("select distinct(m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid).") `id`, m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." `name` from cometchat_block, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." m where m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = toid and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

	$query = mysqli_query($GLOBALS['dbh'],$sql);


	if(empty($_REQUEST['v3'])){
		while ($chat = mysqli_fetch_assoc($query)) {
			if (function_exists('processName')) {
				$chat['name'] = processName($chat['name']);
			}

			++$number;

		$body = <<<EOD
 $body
<div class="chat">
			<div class="chatrequest"><b>{$number}</b></div>
			<div class="chatmessage">{$chat['name']}</div>
			<div class="chattime"><a href="?action=unblock&amp;id={$chat['id']}&amp;basedata={$_REQUEST['basedata']}&amp;embed={$embed}">{$block_language[4]}</a></div>
			<div style="clear:both"></div>
</div>

EOD;
		}

	if ($number == 0) {
		$body = <<<EOD
 $body
<div class="chat">
			<div class="chatrequest">&nbsp;</div>
			<div class="chatmessage">{$block_language[6]}</div>
			<div class="chattime">&nbsp;</div>
			<div style="clear:both"></div>
</div>

EOD;
	}



echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>{$block_language[3]}</title>
	<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=block" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>

	</head>
	<body>
	<div class="container">
	<div class="container_title {$embedcss}" >{$block_language[3]}</div>

	<div class="container_body {$embedcss}">

	$body

	</div>
	</div>
	</div>
	</body>
	</html>
EOD;
	} else {
	$response = array();
	while ($chat = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$blockedName = processName($chat['name']);
		} else {
			$blockedName = $chat['name'];
		}
		$blockedID = $chat['id'];
		$response[$blockedID] = array('id'=>$blockedID,'name'=>$blockedName);
	}
	if(empty($response)){
		$response = json_decode('{}');
	}
	echo json_encode($response);
	}
}