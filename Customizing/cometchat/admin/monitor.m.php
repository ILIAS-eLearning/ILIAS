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

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$online = onlineusers();

$navigation = <<<EOD
	<div id="leftnav">
		<h1 id="online" style="font-size:70px;font-weight:bold">$online</h1>
		<span style="font-size:10px">USERS ONLINE</span>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;
	$overlay = '';

	$body = <<<EOD
	$navigation
        <link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Monitor</h2>
		<h3>See what users are typing in real-time on your site</h3>

		<div>
			<div id="centernav" style="width:700px">
				<script>
					jQuery(function () {
						jQuery.cometchatmonitor();
					});
				</script>
				<div id="data"></div>
			</div>

		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
	{$overlay}
EOD;

	template();

}

function data() {

	global $guestsMode;
	global $guestnamePrefix;

	if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

	$usertable = TABLE_PREFIX.DB_USERTABLE;
	$usertable_username = DB_USERTABLE_NAME;
	$usertable_userid = DB_USERTABLE_USERID;
	$guestpart = "";

	$criteria = "cometchat.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['timestamp'])."' and ";
	$criteria2 = 'desc';

	if($guestsMode) {
		$guestpart = "UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read,CONCAT('$guestnamePrefix',f.name) fromu, CONCAT('$guestnamePrefix',t.name) tou from cometchat, cometchat_guests f, cometchat_guests t where $criteria f.id = cometchat.from and t.id = cometchat.to) UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, f.".$usertable_username." fromu, CONCAT('$guestnamePrefix',t.name) tou from cometchat, ".$usertable." f, cometchat_guests t where $criteria f.".$usertable_userid." = cometchat.from and t.id = cometchat.to) UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, CONCAT('$guestnamePrefix',f.name) fromu, t.".$usertable_username." tou from cometchat, cometchat_guests f, ".$usertable." t where $criteria f.id = cometchat.from and t.".$usertable_userid." = cometchat.to) ";
	}

	$response = array();
	$messages = array();

	if (empty($_POST['timestamp'])) {
		$criteria = '';
		$criteria2 = 'desc limit 20';

	}

	$sql = ("(select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, f.$usertable_username fromu, t.$usertable_username tou from cometchat, $usertable f, $usertable t where $criteria f.$usertable_userid = cometchat.from and t.$usertable_userid = cometchat.to ) ".$guestpart." order by id $criteria2");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$timestamp = $_POST['timestamp'];

	while ($chat = mysqli_fetch_assoc($query)) {

		if (function_exists('processName')) {
			$chat['fromu'] = processName($chat['fromu']);
			$chat['tou'] = processName($chat['tou']);
		}

		$time=$chat['sent']*1000;

		if(strpos($chat['message'], 'CC^CONTROL_') === false)
		array_unshift($messages,  array('id' => $chat['id'], 'from' => $chat['from'], 'to' => $chat['to'], 'fromu' => $chat['fromu'], 'tou' => $chat['tou'], 'message' => $chat['message'], 'time' => $time));

		if ($chat['id'] > $timestamp) {
			$timestamp = $chat['id'];
		}
	}

	$response['timestamp'] = $timestamp;
	$response['online'] = onlineusers();

	if (!empty($messages)) {
		$response['messages'] = $messages;
	}

	header('Content-type: application/json; charset=utf-8');
	echo json_encode($response);
exit;
}