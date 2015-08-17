<?php

/* TIMEZONE SPECIFIC INFORMATION (DO NOT TOUCH) */

date_default_timezone_set('UTC');

$currentversion = '5.6.0';

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SOFTWARE SPECIFIC INFORMATION (DO NOT TOUCH) */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* CCAUTH START */

define('USE_CCAUTH','0');

$ccactiveauth = array('Facebook','Google','Twitter');

$guestsMode = '0';
$guestnamePrefix = 'Guest';
$guestsList = '3';
$guestsUsersList = '3';


/* CCAUTH END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'integration.php');

if(USE_CCAUTH == '1'){
  include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'ccauth.php');
  $guestsMode = '0';
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* BASE URL START */

define('BASE_URL','/ilias/Customizing/cometchat/');

/* BASE URL END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* COOKIE */

$cookiePrefix = 'cc_';        // Modify only if you have multiple CometChat instances on the same site

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* LANGUAGE START */

$lang = 'en';

/* LANGUAGE END */

if (!empty($_COOKIE[$cookiePrefix."lang"])) {
  $lang = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix . "lang"]);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$trayicon = array();

/* ICONS START */

$trayicon[] = array('home','Home','/','','','','','','');
$trayicon[] = array('chatrooms','Chatrooms','modules/chatrooms/index.php','_popup','600','300','','1','1');
$trayicon[] = array('scrolltotop','Scroll To Top','javascript:jqcc.cometchat.scrollToTop();','','','','','','');

/* ICONS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* PLUGINS START */

$plugins = array('smilies','clearconversation','chattime');

/* PLUGINS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* EXTENSIONS START */

$extensions = array('mobileapp');

/* EXTENSIONS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* CHATROOMPLUGINS START */

$crplugins = array('chattime','style','filetransfer','smilies');

/* CHATROOMPLUGINS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'smilies'.DIRECTORY_SEPARATOR.'config.php');

/* SMILEYS START */

$smileys_default = array (

);

/* SMILEYS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* EMOJI START */

$smileys = array_merge($smileys_default,$emojis);

/* EMOJI END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* BANNED START */

$bannedWords = array();
$bannedUserIDs = array();
$bannedUserIPs = array();
$bannedMessage = 'Sorry, you have been banned from using this service. Your messages will not be delivered.';

/* BANNED END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADMIN START */

define('ADMIN_USER','cometchat');
define('ADMIN_PASS','cometchat');

/* ADMIN END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$hideOffline = '1';     // Hide offline users in Whos Online list?
$autoPopupChatbox = '1';      // Auto-open chatbox when a new message arrives
$messageBeep = '1';     // Beep on arrival of message from new user?
$beepOnAllMessages = '1';     // Beep on arrival of all messages?
$minHeartbeat = '3000';     // Minimum poll-time in milliseconds (1 second = 1000 milliseconds)
$maxHeartbeat = '12000';      // Maximum poll-time in milliseconds
$fullName = '0';      // If set to yes, both first name and last name will be shown in chat conversations
$searchDisplayNumber = '10';      // The number of users in Whos Online list after which search bar will be displayed
$thumbnailDisplayNumber = '40';     // The number of users in Whos Online list after which thumbnails will be hidden
$typingTimeout = '10000';     // The number of milliseconds after which typing to will timeout
$idleTimeout = '300';     // The number of seconds after which user will be considered as idle
$displayOfflineNotification = '1';      // If yes, user offline notification will be displayed
$displayOnlineNotification = '1';     // If yes, user online notification will be displayed
$displayBusyNotification = '1';     // If yes, user busy notification will be displayed
$notificationTime = '5000';     // The number of milliseconds for which a notification will be displayed
$announcementTime = '15000';      // The number of milliseconds for which an announcement will be displayed
$scrollTime = '1';      // Can be set to 800 for smooth scrolling when moving from one chatbox to another
$armyTime = '0';      // If set to yes, show time plugin will use 24-hour clock format
$disableForIE6 = '0';     // If set to yes, CometChat will be hidden in IE6
$hideBar = '0';     // Hide bar for non-logged in users?
$disableForMobileDevices = '1';     // If set to yes, CometChat will be hidden in mobile devices
$startOffline = '0';      // Load bar in offline mode for all first time users?
$fixFlash = '0';      // Set to yes, if Adobe Flash animations/ads are appearing on top of the bar (experimental)
$lightboxWindows = '1';     // Set to yes, if you want to use the lightbox style popups
$sleekScroller = '1';     // Set to yes, if you want to use the new sleek scroller
$desktopNotifications = '1';      // If yes, desktop notifications will be enabled for Google Chrome
$windowTitleNotify = '1';               //If yes, notify new incoming messages by changing the browser title
$floodControl = '0';                    //If set to 0, Users will be allowed to spam in chat (milliseconds)
$windowFavicon = '0';     // If yes, Update favicon with number of messages (Works on Chrome, Firefox, Opera)
$prependLimit = '10';     // Number of messages that are fetched when load earlier messages is clicked

/* SETTINGS END */

$notificationsFeature = 1;      // Set to yes, only if you are using notifications

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/* MEMCACHE START */

define('MEMCACHE','0');       // Set to 0 if you want to disable caching and 1 to enable it.
define('MC_SERVER','localhost');  // Set name of your memcache  server
define('MC_PORT','11211');      // Set port of your memcache  server
define('MC_USERNAME','');           // Set username of memcachier  server
define('MC_PASSWORD','');           // Set password your memcachier  server
define('MC_NAME','files');      // Set name of caching method if 0 : '', 1 : memcache, 2 : files, 3 : memcachier, 4 : apc, 5 : wincache, 6 : sqlite & 7 : memcached

/* MEMCACHE END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* COLOR START */

$color = 'hangout';

/* COLOR END */

if (!empty($_COOKIE[$cookiePrefix."color"])) {
  $color = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix."color"]);
}

if (!empty($_REQUEST["cc_theme"]) && ($_REQUEST["cc_theme"] == 'synergy')) {
  $color = preg_replace("/[^A-Za-z0-9\-]/", '', $_REQUEST["cc_theme"]);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* THEME START */

$theme = 'hangout';

/* THEME END */

if (!empty($_COOKIE[$cookiePrefix."theme"])) {
  $theme = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix."theme"]);
}

if (!empty($_REQUEST["cc_theme"])) {
  $theme = preg_replace("/[^A-Za-z0-9\-]/", '', $_REQUEST["cc_theme"]);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DISPLAYSETTINGS START */

define('DISPLAY_ALL_USERS','1');

/* DISPLAYSETTINGS END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DISABLEBAR START */

define('BAR_DISABLED','0');

/* DISABLEBAR END */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* COMET START */

define('USE_COMET','0');        // Set to 0 if you want to disable transport service and 1 to enable it.
define('KEY_A','');
define('KEY_B','');
define('KEY_C','');

/* COMET END */

define('TRANSPORT','cometservice');
define('COMET_CHATROOMS','1');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADVANCED */

define('REFRESH_BUDDYLIST','60');   // Time in seconds after which the user's "Who's Online" list is refreshed
define('DISABLE_SMILEYS','0');      // Set to 1 if you want to disable smileys
define('DISABLE_LINKING','0');      // Set to 1 if you want to disable auto linking
define('DISABLE_YOUTUBE','1');      // Set to 1 if you want to disable YouTube thumbnail
define('CACHING_ENABLED','0');      // Set to 1 if you would like to cache CometChat
define('GZIP_ENABLED','0');       // Set to 1 if you would like to compress output of JS and CSS
define('DEV_MODE','0');         // Set to 1 only during development
define('ERROR_LOGGING','0');      // Set to 1 to log all errors (error.log file)
define('ONLINE_TIMEOUT',USE_COMET?REFRESH_BUDDYLIST*2:($maxHeartbeat/1000*2.5));
                    // Time in seconds after which a user is considered offline
define('DISABLE_ANNOUNCEMENTS','0');  // Reduce server stress by disabling announcements
define('DISABLE_ISTYPING','1');     // Reduce server stress by disabling X is typing feature
define('CROSS_DOMAIN','0');       // Do not activate without consulting the CometChat Team
if (CROSS_DOMAIN == 0){
  define('ENCRYPT_USERID', '1');      //Set to 1 to encrypt userid
}else{
  define('ENCRYPT_USERID', '0');
}
define('DB_SESSION','0');      // If set to 1, sessions will be stored in the database.

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Pulls the language file if found

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php');
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
  include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
}

if (!defined('DB_AVATARFIELD')) {
  define('DB_AVATARTABLE','');
  define('DB_AVATARFIELD',"''");
}

$channelprefix = (preg_match('/www\./', $_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:'www.'.$_SERVER['HTTP_HOST'];
$channelprefix = md5($channelprefix.BASE_URL);