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

if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

function getGuestID() {

	$_SESSION['guestMode'] = 1;

	global $cookiePrefix;

	$userid = 0;

	if (!empty($_COOKIE[$cookiePrefix.'guest'])) {
		$checkId = base64_decode($_COOKIE[$cookiePrefix.'guest']);

		$sql = ("select id from cometchat_guests where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$checkId)."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$result = mysqli_fetch_assoc($query);

		if (!empty($result['id'])) {
			$userid = $result['id'];
		}
	}

	if (empty($userid)) {
		$random = rand(10000,99999);
		$sql = ("insert into cometchat_guests (name,lastactivity) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$random)."','".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."')");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$userid = mysqli_insert_id($GLOBALS['dbh']);
		setcookie($cookiePrefix.'guest', base64_encode($userid), time()+3600*24*365, "/");
	}
	return $userid;
}

function getGuestsList($userid,$time,$originalsql) {

	global $guestsList;
	global $guestsUsersList;
	global $guestnamePrefix;

	$sql = ("select DISTINCT cometchat_guests.id userid, concat('".$guestnamePrefix."',cometchat_guests.name) username, NULL link, NULL avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message, 0 isdevice from cometchat_guests left join cometchat_status on cometchat_guests.id = cometchat_status.userid where ('".mysqli_real_escape_string($GLOBALS['dbh'],$time)."'- cometchat_status.lastactivity < '".((ONLINE_TIMEOUT)*2)."') and (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')");

	if (empty($_SESSION['guestMode'])) {
		if ($guestsUsersList == 2) {
			$sql = $originalsql;
		} else if ($guestsUsersList == 3) {
			$sql .= " UNION ".$originalsql;
		}
	} else {
		if ($guestsList == 2) {
			$sql = $originalsql;
		} else if ($guestsList == 3) {
			$sql .= " UNION ".$originalsql;
		}
	}
	return $sql;
}

function getChatroomGuests($chatroomid,$time,$originalsql) {

	global $guestnamePrefix;

	$sql = ("select DISTINCT cometchat_guests.id userid, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',cometchat_guests.name) username, '' avatar, cometchat_chatrooms_users.lastactivity lastactivity, cometchat_chatrooms_users.isbanned from cometchat_guests left join cometchat_status on cometchat_guests.id = cometchat_status.userid inner join cometchat_chatrooms_users on  cometchat_guests.id =  cometchat_chatrooms_users.userid where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' and ('".mysqli_real_escape_string($GLOBALS['dbh'],$time)."' - cometchat_chatrooms_users.lastactivity < ".ONLINE_TIMEOUT.") Union ".$originalsql);

	return $sql;
}

function getChatroomBannedGuests($chatroomid,$time,$originalsql) {

	global $guestnamePrefix;

   $sql = ("select DISTINCT cometchat_guests.id userid, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',cometchat_guests.name) username, '' link, '' avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message from cometchat_guests left join cometchat_status on cometchat_guests.id = cometchat_status.userid inner join cometchat_chatrooms_users on  cometchat_guests.id =  cometchat_chatrooms_users.userid where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' and ('".mysqli_real_escape_string($GLOBALS['dbh'],$time)."' - cometchat_chatrooms_users.lastactivity < ".ONLINE_TIMEOUT.") AND cometchat_chatrooms_users.isbanned = 1 Union ".$originalsql);

   return $sql;
}

function getGuestDetails($userid) {

	global $guestnamePrefix;

	$sql = ("select cometchat_guests.id userid, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',cometchat_guests.name) username,  '' link,  '' avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message from cometchat_guests left join cometchat_status on cometchat_guests.id = cometchat_status.userid where cometchat_guests.id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

	return $sql;
}