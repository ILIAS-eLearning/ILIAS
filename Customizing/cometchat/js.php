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
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

if(phpversion()>='5'){
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'jsmin.php');
}

if(BAR_DISABLED==1 && empty($_REQUEST['admin'])){
	exit();
}

if(get_magic_quotes_runtime()){
	set_magic_quotes_runtime(false);
}

$mtime = explode(" ",microtime());
$starttime = $mtime[1]+$mtime[0];

$HTTP_USER_AGENT = '';
$useragent = (!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;

ob_start();

$type = 'core';
$name = 'default';
if(!empty($_REQUEST['type'])&&!empty($_REQUEST['name'])){
	$type = cleanInput($_REQUEST['type']);
	$name = cleanInput($_REQUEST['name']);
}

$subtype = '';
if(!empty($_REQUEST['subtype'])){
	$subtype = cleanInput($_REQUEST['subtype']);
}

$cbfn = '';
if(!empty($_REQUEST['callbackfn'])){
	$cbfn = cleanInput($_REQUEST['callbackfn']);
}

if(!empty($_REQUEST['admin'])){
	if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'admin.js')&&DEV_MODE!=1){
		if(!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'admin.js')){
			header("HTTP/1.1 304 Not Modified");
			exit();
		}
		readfile(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'admin.js');
		$js = ob_get_clean();
	}else{
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery.min.js");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."admin.js");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery-ui.min.js");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery.bgiframe-2.1.1.js");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery-ui-i18n.min.js");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."colorpicker.js");

		if(phpversion()>='5'){
			$js = JSMin::minify(ob_get_clean());
		}else{
			$js = ob_get_clean();
		}
		$fp = @fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'admin.js','w');
		@fwrite($fp,$js);
		@fclose($fp);
	}
	$lastModified = filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'admin.js');
}else{
	if(!empty($cbfn)){
		$_SESSION['noguestmode'] = '1';
	}

	if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'config.php')){
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'config.php');
	}else{
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'standard'.DIRECTORY_SEPARATOR.'config.php');
	}

	$cometchat = array();
	if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$theme.$cbfn.$color.$lang.$type.$name.'.js')&&DEV_MODE!=1){
		if(!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$theme.$cbfn.$color.$lang.$type.$name.'.js')){
			header("HTTP/1.1 304 Not Modified");
			exit();
		}

		readfile(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$theme.$cbfn.$color.$lang.$type.$name.'.js');
		$js = ob_get_clean();
	}else{
		if(($type!='core'||$name!='default')&&($type!='extension'||($type=='extension'&&$name=='jabber'))&&($type!='external')){
			if($type =='core' && $name=='embedcode'){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery.js");
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."libraries.js");

			}
			if($type=='core'){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$name.".js");
			}else{
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."libraries.js");
				if(empty($subtype)){
					$subtype = $name;
				}
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$type."s".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$subtype.".js")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.$type."s".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$subtype.".js");
				}
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$type."s".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.$type."s".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js");
				}
				if((($name=='avchat'||$name=='broadcast'||$name=='screenshare')&&$subtype=='fmsred5')||$name=='whiteboard'){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'swfobject.js');
				}
			}
		}else{

			if(USE_COMET==1){
				$minHeartbeat = REFRESH_BUDDYLIST.'000';
				$maxHeartbeat = REFRESH_BUDDYLIST.'000';
			}

			if(((defined('INCLUDE_JQUERY')&&INCLUDE_JQUERY==1)&&empty($cbfn))||($type=='extension'&&$name=='desktop')){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."jquery.js");
			}

			$settings = '';

			if(defined('DISPLAY_ALL_USERS')&&DISPLAY_ALL_USERS==1){
				$language[14] = $language[28];
			}elseif($hideOffline==1||MEMCACHE!=0){
				$language[14] = $language[29];
			}

			for($i = 0;$i<count($language);$i++){
				$cometchat['language'][$i] = $language[$i];
			}

			$settings .= "var language = ".json_encode($cometchat['language']).";";
			$cometchat['trayicon'] = array();

			for($i = 0;$i<count($trayicon);$i++){
				$id = $trayicon[$i];
				if(!empty($trayicon[$i][7])&&$trayicon[$i][7]==1){
					$trayicon[$i][2] = BASE_URL.$trayicon[$i][2];
				}

				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$trayicon[$i][0].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$trayicon[$i][0].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
					$traylanguage = $trayicon[$i][0].'_language';
					if(!empty(${$traylanguage}[100])){
						$trayicon[$i][1] = ${$traylanguage}[100];
					}
				}

				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$trayicon[$i][0].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$trayicon[$i][0].DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
					$traylanguage = $trayicon[$i][0].'_language';

					if(!empty(${$traylanguage}[100])){
						$trayicon[$i][1] = ${$traylanguage}[100];
					}
				}
				$cometchat['trayicon'][$id[0]] = $trayicon[$i];
			}

			$settings .= "var trayicon = ".json_encode($cometchat['trayicon']).";";
			if(!empty($cbfn)){
				$hideBar = 0;
			}

			$ccauth = array('enabled' => USE_CCAUTH, 'active' => $ccactiveauth);

			if($theme=='standard'){
				$cometchat['settings']['barWidth'] = intval($barWidth); // If set to fixed, enter the width of the bar in pixels
				$cometchat['settings']['barAlign'] = $barAlign; // If set to fixed, enter alignment of the bar
				$cometchat['settings']['autoLoadModules'] = intval($autoLoadModules); // If set to yes, modules open in previous page, will open in new page
				$cometchat['settings']['barType'] = $barType; // Bar layout
			}else{
				$cometchat['settings']['showSettingsTab'] = $showSettingsTab; // Show Settings tab
				$cometchat['settings']['showOnlineTab'] = $showOnlineTab; //Show Who's Online tab
				$cometchat['settings']['showModules'] = $showModules; //Show Modules in Who\'s Online tab
			}
			$cometchat['settings']['plugins'] = $plugins;
			$cometchat['settings']['extensions'] = $extensions;
			$cometchat['settings']['hideOffline'] = intval($hideOffline); // Hide offline users in Whos Online list?
			$cometchat['settings']['autoPopupChatbox'] = intval($autoPopupChatbox); // Auto-open chatbox when a new message arrives
			$cometchat['settings']['messageBeep'] = intval($messageBeep); // Beep on arrival of message from new user?
			$cometchat['settings']['beepOnAllMessages'] = intval($beepOnAllMessages); // Beep on arrival of all messages?
			$cometchat['settings']['barPadding'] = intval($barPadding); // Padding of bar from the end of the window
			$cometchat['settings']['minHeartbeat'] = intval($minHeartbeat); // Minimum poll-time in milliseconds (1 second = 1000 milliseconds)
			$cometchat['settings']['maxHeartbeat'] = intval($maxHeartbeat); // Maximum poll-time in milliseconds
			$cometchat['settings']['fullName'] = intval($fullName); // If set to yes, both first name and last name will be shown in chat conversations
			$cometchat['settings']['searchDisplayNumber'] = intval($searchDisplayNumber); // The number of users in Whos Online list after which search bar will be displayed
			$cometchat['settings']['thumbnailDisplayNumber'] = intval($thumbnailDisplayNumber); // The number of users in Whos Online list after which thumbnails will be hidden
			$cometchat['settings']['typingTimeout'] = intval($typingTimeout); // The number of milliseconds after which typing to will timeout
			$cometchat['settings']['idleTimeout'] = intval($idleTimeout); // The number of seconds after which user will be considered as idle
			$cometchat['settings']['displayOfflineNotification'] = intval($displayOfflineNotification); // If yes, user offline notification will be displayed
			$cometchat['settings']['displayOnlineNotification'] = intval($displayOnlineNotification); // If yes, user online notification will be displayed
			$cometchat['settings']['displayBusyNotification'] = intval($displayBusyNotification); // If yes, user busy notification will be displayed
			$cometchat['settings']['notificationTime'] = intval($notificationTime); // The number of milliseconds for which a notification will be displayed
			$cometchat['settings']['announcementTime'] = intval($announcementTime); // The number of milliseconds for which an announcement will be displayed
			$cometchat['settings']['scrollTime'] = intval($scrollTime); // Can be set to 800 for smooth scrolling when moving from one chatbox to another
			$cometchat['settings']['armyTime'] = intval($armyTime); // If set to yes, show time plugin will use 24-hour clock format
			$cometchat['settings']['disableForIE6'] = intval($disableForIE6); // If set to yes, CometChat will be hidden in IE6
			$cometchat['settings']['iPhoneView'] = intval($iPhoneView); // iPhone style messages in chatboxes? (not compatible with dark theme)
			$cometchat['settings']['hideBarCheck'] = intval($hideBar); // Hide bar for non-logged in users?
			$cometchat['settings']['startOffline'] = intval($startOffline); // Load bar in offline mode for all first time users?
			$cometchat['settings']['fixFlash'] = intval($fixFlash); // Set to yes, if Adobe Flash animations/ads are appearing on top of the bar (experimental)
			$cometchat['settings']['lightboxWindows'] = intval($lightboxWindows); // Set to yes, if you want to use the lightbox style popups
			$cometchat['settings']['sleekScroller'] = intval($sleekScroller);
			$cometchat['settings']['color'] = $color;
			$cometchat['settings']['cookiePrefix'] = $cookiePrefix;
			$cometchat['settings']['disableForMobileDevices'] = intval($disableForMobileDevices);
            $cometchat['settings']['desktopNotifications'] = intval($desktopNotifications);
			$cometchat['settings']['windowTitleNotify'] = intval($windowTitleNotify);
			$cometchat['settings']['floodControl'] = intval($floodControl);
			$cometchat['settings']['windowFavicon'] = intval($windowFavicon);
			$cometchat['settings']['theme'] = $theme;
			$cometchat['settings']['ccauth'] = $ccauth;
			$cometchat['settings']['prependLimit'] = !empty($prependLimit)?$prependLimit:'0';

			$settings .= "var settings = ".json_encode($cometchat['settings']).";";

			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."libraries.js");

			if($sleekScroller==1){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."scroll.js");
			}

			if(USE_COMET==1){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."transports".DIRECTORY_SEPARATOR.TRANSPORT.DIRECTORY_SEPARATOR.'config.php');
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."transports".DIRECTORY_SEPARATOR.TRANSPORT.DIRECTORY_SEPARATOR.'includes.php');
			}

			// Modifying this will void license
			if($p_<2){
				$jsfn = 'c5';
			}else{
				$jsfn = 'c6';
			}
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."cometchat.js");
			if($theme=='synergy' && !empty($cometchat['trayicon']['chatrooms'])){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."chatrooms.js");
			}
			if(empty($cbfn)){
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js");
				}else{
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."standard.js");
				}
				if($p_>2 && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.'config.php')){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.'config.php');
					if($enableMobileTab&&file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."mobile.js")){
						include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."mobile.js");
					}
				}
			}elseif($type=='external'){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$name.".js");
			}else{
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.$cbfn.DIRECTORY_SEPARATOR.$cbfn.".js");
				if($type=='extension'){
					if($name!=$cbfn){
						if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$name.".js")){
							include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$name.".js");
						}
					}
					if($name=='mobilewebapp'){
						include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.'chatrooms'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR."chatrooms.js");
						include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.'mobilewebapp'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."custom.modernizr.js");
						include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.'mobilewebapp'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR."jquery.nicescroll.js");
						$plugins = array_intersect($plugins,array('clearconversation','report','smilies'));
					}
				}
			}

			$include = 'init';
			$allplugins = array();

			if ($handle = opendir(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && is_dir(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'init.js') && $file != 'style') {
						$allplugins[] = $file;
					}
				}
				closedir($handle);
			}

			foreach($allplugins as $plugin){
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."init.js")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."init.js");
				}
			}

			if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."style".DIRECTORY_SEPARATOR."init.js")){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."style".DIRECTORY_SEPARATOR."init.js");
			}

			foreach($extensions as $extension){
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR."init.js")){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR."init.js");
				}
			}

			for($i = 0;$i<count($trayicon);$i++){
				$id = $trayicon[$i];
				if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$id[0].DIRECTORY_SEPARATOR."extra.js")&&empty($cbfn)){
					include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$id[0].DIRECTORY_SEPARATOR."extra.js");
				}
			}

			if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."extra.js")&&empty($cbfn)){
				include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."extra.js");
			}
		}

		if(phpversion()>='5'){
			$js = JSMin::minify(ob_get_clean());
		}else{
			$js = ob_get_clean();
		}

		$fp = @fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$theme.$cbfn.$color.$lang.$type.$name.'.js','w');
		@fwrite($fp,$js);
		@fclose($fp);
	}
	$lastModified = filemtime(dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$theme.$cbfn.$color.$lang.$type.$name.'.js');
}
if(phpversion()>='4.0.4pl1'&&(strstr($useragent,'compatible')||strstr($useragent,'Gecko'))){
	if(extension_loaded('zlib')&&GZIP_ENABLED==1){
		ob_start('ob_gzhandler');
	}else{
		ob_start();
	}
}else{
	ob_start();
}

header('Content-type: text/javascript;charset=utf-8');
header("Last-Modified: ".gmdate("D, d M Y H:i:s",$lastModified)." GMT");
header('Expires: '.gmdate("D, d M Y H:i:s",time()+3600*24*365).' GMT');

echo $js;

$mtime = explode(" ",microtime());
$endtime = $mtime[1]+$mtime[0];

echo "\n\n/* Execution time: ".($endtime-$starttime)." seconds */";
function cleanInput($input){
	return strtolower(preg_replace("/[^+A-Za-z0-9\_]/","",trim($input)));
}