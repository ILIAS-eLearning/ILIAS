<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$chatroomTimeout = '604800';
$lastMessages = '10';
$allowUsers = '1';
$allowDelete = '1';
$displayFullName = '1';
$allowAvatar = '1';
$crguestsMode = '1';
$hideEnterExit = '0';
$minHeartbeat = '3000';
$maxHeartbeat = '12000';
$autoLogin = '0';
$messageBeep = '1';
$newMessageIndicator = '1';


/* SETTINGS END */

/* MODERATOR START */

$moderatorUserIDs = array();


/* MODERATOR END */



if (USE_COMET == 1 && COMET_CHATROOMS == 1) {
	$minHeartbeat = $maxHeartbeat = REFRESH_BUDDYLIST.'000';
	$hideEnterExit = 1;
}

/* ADDITIONAL SETTINGS */

$chatroomLongNameLength = 60;	// The chatroom length after which characters will be truncated
$chatroomShortNameLength = 30;	// The chatroom length after which characters will be truncated




////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////