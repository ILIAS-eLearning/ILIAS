<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADVANCED */

define('SET_SESSION_NAME','');			// Session name
define('SWITCH_ENABLED','0');
define('INCLUDE_JQUERY','1');
define('FORCE_MAGIC_QUOTES','0');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DATABASE */
if(!file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."Services".DIRECTORY_SEPARATOR."Init".DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."class.ilIniFile.php")) {
	echo ("Please check if cometchat is installed in the correct directory <br /> Generally cometchat should be installed in <ILIAS_HOME_DIRECTORY>/cometchat");
	exit;
}

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."Services".DIRECTORY_SEPARATOR."Init".DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."class.ilIniFile.php");

$ini = new ilIniFile(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."ilias.ini.php");
$ini->read();

define("ILIAS_WEB_DIR",$ini->readVariable("clients","path"));
define("ILIAS_ABSOLUTE_PATH",$ini->readVariable('server','absolute_path'));
define("ILIAS_CLIENT_ID",$ini->readVariable('clients','default'));
define("ILIAS_CLIENT_INI",$ini->readVariable('clients','inifile'));

$search_path = ILIAS_ABSOLUTE_PATH.DIRECTORY_SEPARATOR.ILIAS_WEB_DIR.DIRECTORY_SEPARATOR.ILIAS_CLIENT_ID.DIRECTORY_SEPARATOR."lm_data";

$client_ini = ILIAS_ABSOLUTE_PATH.DIRECTORY_SEPARATOR.ILIAS_WEB_DIR.DIRECTORY_SEPARATOR.ILIAS_CLIENT_ID.DIRECTORY_SEPARATOR.ILIAS_CLIENT_INI;

$cini = new ilIniFile($client_ini);
$cini->read();

// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW

define('DB_SERVER',			$cini->readVariable("db","host")		);
define('DB_PORT',			"3306"									);
define('DB_USERNAME',			$cini->readVariable("db","user")		);
define('DB_PASSWORD',			$cini->readVariable("db","pass")		);
define('DB_NAME',			$cini->readVariable("db","name")		);
if(defined('USE_CCAUTH') && USE_CCAUTH == '0'){
define('TABLE_PREFIX',			""										);
define('DB_USERTABLE',			"usr_data"							    );
define('DB_USERTABLE_USERID',		"usr_id"								);
define('DB_USERTABLE_NAME',		"login"									);
define('DB_AVATARTABLE',		" "                                     );
define('DB_AVATARFIELD',		"usr_id"								);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* FUNCTIONS */


	function getUserID() {
		$userid = 0;
		if (!empty($_SESSION['basedata']) && $_SESSION['basedata'] != 'null') {
			$_REQUEST['basedata'] = $_SESSION['basedata'];
		}

		if (!empty($_REQUEST['basedata']) && isset($_REQUEST['callbackfn']) && in_array($_REQUEST['callbackfn'],array('desktop','mobileapp'))) {
			if (function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
				$key = "";
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}
				$uid = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(rawurldecode($_REQUEST['basedata'])), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
				if (intval($uid) > 0) {
					$userid = $uid;
				}
			} else {
				$userid = $_REQUEST['basedata'];
			}
		}

		if(isset($_COOKIE['PHPSESSID'])) {
			$sql = "SELECT user_id FROM usr_session WHERE session_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_COOKIE['PHPSESSID'])."'";
			$result = mysqli_fetch_assoc(mysqli_query($GLOBALS['dbh'],$sql));
			$userid = $result['user_id'];
		}

		return $userid;
	}

	function chatLogin($userName,$userPass) {
		$userid = 0;
		global $guestsMode;
		if(!empty($_REQUEST['guest_login']) && $userPass == "CC^CONTROL_GUEST" && $guestsMode == 1) {
			if(!empty($_REQUEST['basedata']) && $_REQUEST['basedata'] != 'null') {
				$userid = getUserID();
				$sql = ("UPDATE `cometchat_guests` SET `name` = '".mysqli_real_escape_string($GLOBALS['dbh'], $userName)."', `lastactivity` = '".getTimeStamp()."' WHERE `id` = ".mysqli_real_escape_string($GLOBALS['dbh'], $userid));
				$query = mysqli_query($GLOBALS['dbh'], $sql);
			} else {
				$sql = ("INSERT INTO `cometchat_guests` (`name`, `lastactivity`) VALUES('".mysqli_real_escape_string($GLOBALS['dbh'], $userName)."','".getTimeStamp()."')");
				$query = mysqli_query($GLOBALS['dbh'], $sql);
				$userid = mysqli_insert_id($GLOBALS['dbh']);
			}
			if (isset($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp') {
                $sql = ("insert into cometchat_status (userid,isdevice) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','1') on duplicate key update isdevice = '1'");
                mysqli_query($GLOBALS['dbh'], $sql);
            }
		} else {
			if (filter_var($userName, FILTER_VALIDATE_EMAIL)) {
				$sql = ("SELECT * FROM ".TABLE_PREFIX.DB_USERTABLE." WHERE user_emailadr = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
			} else {
				$sql = ("SELECT * FROM ".TABLE_PREFIX.DB_USERTABLE." WHERE username = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
			}
			$result = mysqli_query($GLOBALS['dbh'],$sql);
			$row = mysqli_fetch_assoc($result);
			if($row['password'] == md5($userPass)) {
				$userid = $row['id'];
                if (isset($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp') {
                    $sql = ("insert into cometchat_status (userid,isdevice) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','1') on duplicate key update isdevice = '1'");
                    mysqli_query($GLOBALS['dbh'], $sql);
                }
			}
		}
		if($userid && function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
			$key = "";
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}
			$userid = rawurlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $userid, MCRYPT_MODE_CBC, md5(md5($key)))));
		}

		return $userid;
	}

	function getFriendsList($userid,$time) {
		global $hideOffline;
		$offlinecondition = '';

		if ($hideOffline) {
			$offlinecondition = "where ((cometchat_status.lastactivity > (".mysqli_real_escape_string($GLOBALS['dbh'],$time)."-".((ONLINE_TIMEOUT)*2).")) OR cometchat_status.isdevice = 1) and (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')";
		}
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link, " .DB_AVATARFIELD. " avatar, cometchat_status.lastactivity lastactivity,  cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." ".$offlinecondition." order by username asc");

		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, CONCAT(".DB_USERTABLE.".firstname,' ',".DB_USERTABLE.".lastname) username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link, " .DB_AVATARFIELD. " avatar, cometchat_status.lastactivity lastactivity,  cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." ".$offlinecondition."
		
		AND userid IN (SELECT usr_id FROM rbac_ua WHERE rol_id IN (SELECT rol_id FROM `rbac_ua` WHERE usr_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' AND rol_id NOT IN (2,5,109))) 
		
		order by username asc");
		
		return $sql;
	}

	function getUserDetails($userid) {
		$sql = ("select ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link,  " .DB_AVATARFIELD. " avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		return $sql;
	}

	function updateLastActivity($userid) {
		$sql = ("insert into cometchat_status (userid,lastactivity) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".getTimeStamp()."') on duplicate key update lastactivity = '".getTimeStamp()."'");

		return $sql;
	}

	function getUserStatus($userid) {
		 $sql = ("select cometchat_status.message, cometchat_status.status from cometchat_status where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		 return $sql;
	}

	function fetchLink($link) {
	        return BASE_URL.'../../goto.php?target=usr_'.$link;
	}

	function getAvatar($image) {
		/*
		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'usr_images'.DIRECTORY_SEPARATOR.'usr_'.$image.'.jpg')) {
			return BASE_URL.'../data/administrator/usr_images/usr_'.$image.'.jpg';
		} else {
			return BASE_URL.'../templates/default/images/no_photo_xxsmall.jpg';
		}
		*/
		$client='hslu';
		if (is_file(dirname(dirname(dirname(__FILE__))).'/data/'.$client.'/usr_images/usr_'.$image.'_xsmall.jpg')) {
			return '/ilias/data/'.$client.'/usr_images/usr_'.$image.'_xsmall.jpg';
		} else {
			return '/ilias/Customizing/global/skin/'.$client.'/images/no_photo_xsmall.jpg';
		}
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

	function hooks_updateLastActivity($userid) {

	}

	function hooks_statusupdate($userid,$statusmessage) {

	}

	function hooks_forcefriends() {

	}

	function hooks_activityupdate($userid,$status) {

	}

	function hooks_message($fromid,$toid,$unsanitizedmessage) {

	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* LICENSE */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'license.php');
$x = "\x62a\x73\x656\x34\x5fd\x65c\157\144\x65";
eval($x('JHI9ZXhwbG9kZSgnLScsJGxpY2Vuc2VrZXkpOyRwXz0wO2lmKCFlbXB0eSgkclsyXSkpJHBfPWludHZhbChwcmVnX3JlcGxhY2UoIi9bXjAtOV0vIiwnJywkclsyXSkpOw'));

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
