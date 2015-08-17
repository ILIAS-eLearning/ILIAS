<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DATABASE */

define('TABLE_PREFIX',				""											);
define('DB_USERTABLE',				"cometchat_users"							);
define('DB_USERTABLE_USERID',		"id"										);
define('DB_USERTABLE_USERNAME',		"username"									);
define('DB_USERTABLE_NAME',			"displayname"								);
define('DB_AVATARTABLE',		    " "											);
define('DB_AVATARFIELD',		    " ".DB_USERTABLE.".avatar "					);
define('DB_LINKFIELD',		    	" link "									);
define('DB_GROUPFIELD',		    	" grp "										);

/* FUNCTIONS */

function getUserID() {
	$userid = 0;


	if (!empty($_SESSION['basedata']) && $_SESSION['basedata'] != 'null') {
		   $_REQUEST['basedata'] = $_SESSION['basedata'];
	   }

	if (!empty($_REQUEST['basedata'])) {
	   $userid = $_REQUEST['basedata'];
	}

	if (!empty($_SESSION['cometchat']['userid']) && !empty($_SESSION['cometchat']['ccauth'])){
		$userid = $_SESSION['cometchat']['userid'];
	}

	return $userid;
}

function chatLogin($userName,$userPass) {

	$userid = 0;

	$sql ="SELECT * FROM ".DB_USERTABLE." WHERE username='".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'";
	$result=mysqli_query($GLOBALS['dbh'], $sql);
	if($row = mysqli_fetch_array($result)){
		$userid = $row['id'];
	}

	return $userid;
}

function getFriendsList($userid,$time) {

	$sql = ("select DISTINCT ".DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_USERTABLE.".".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, ".DB_USERTABLE.".".DB_GROUPFIELD." grp, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".DB_USERTABLE." left join cometchat_status on ".DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline') order by username asc");

	return $sql;
}

function getUserDetails($userid) {
	$sql = ("select ".DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE.".".DB_USERTABLE_NAME." username,  ".DB_USERTABLE.".".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".DB_USERTABLE." left join cometchat_status on ".DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".DB_USERTABLE.".".DB_USERTABLE_USERID." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	return $sql;
}

function updateLastActivity($userid) {
	$sql = ("insert into cometchat_status (userid,lastactivity) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."') on duplicate key update lastactivity = '".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."'");

	return $sql;
}

function getUserStatus($userid) {
	$sql = ("select cometchat_status.userid, cometchat_status.message, cometchat_status.status from cometchat_status where userid = '".mysql_real_escape_string($userid)."'");
	return $sql;
}

function fetchLink($link) {
   return $link;
}

function getAvatar($data) {
	return $data;
}

function getTimeStamp() {
	return time();
}

function processTime($time) {
	return $time;
}

if (!function_exists('getLink')) {
 	function getLink($userid) { return fetchLink($userid); }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* HOOKS */

function hooks_statusupdate($userid,$statusmessage) {

}

function hooks_forcefriends() {

}

function hooks_activityupdate($userid,$status) {

}

function hooks_message($userid,$to,$unsanitizedmessage) {

}

