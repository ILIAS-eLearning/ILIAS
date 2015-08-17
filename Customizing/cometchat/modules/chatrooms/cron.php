<?php

if (!defined('CC_CRON')) { echo "NO DICE"; exit; }

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

if ((!empty($_REQUEST['cron']['type']) && $_REQUEST['cron']['type'] == "all") || !empty($_REQUEST['cron']['modules'])) {
	chatrooms();
	chatroommessages();
	chatroomsusers();
} else {
	if(!empty($_REQUEST['cron']['inactiverooms'])){chatrooms();}
	if(!empty($_REQUEST['cron']['chatroommessages'])){chatroommessages();}
	if(!empty($_REQUEST['cron']['inactiveusers'])){chatroomsusers();}
}

function chatrooms() {
	$sql = ("delete from cometchat_chatrooms where createdby <> 0 and lastactivity < (".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."- ".$GLOBALS['chatroomTimeout']." )");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
}

function chatroommessages() {
	$sql = ("delete from cometchat_chatroommessages where sent < (".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-10800)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
}

function chatroomsusers() {
	$sql = ("delete from cometchat_chatrooms_users where lastactivity < (".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-3600)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
}