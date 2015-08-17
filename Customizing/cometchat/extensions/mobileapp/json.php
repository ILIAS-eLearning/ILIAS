<?php

/*

CometChat
Copyright (c) 2013 Inscripts

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
$callbackfn = null;
if(!empty($_REQUEST['callbackfn'])){
	$callbackfn = $_REQUEST['callbackfn'];
}
if($callbackfn <> 'mobileapp'){
	echo "Nothing to look here";
	exit;
}
include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}else{
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
}
if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}else{
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
}
if($color == 'hangout'){
	$color = 'standard';
}
if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."colors".DIRECTORY_SEPARATOR.$color.'.php')){
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."colors".DIRECTORY_SEPARATOR.$color.'.php');
}else{
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.'standard.php');
}
$response_lang = Array();
$supported_plugins = array('clearconversation', 'report', 'avchat', 'filetransfer','block','audiochat');

foreach($supported_plugins as $key => $plugin){
	if(in_array($plugin,$plugins) || in_array($plugin,$crplugins)){
		if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}else{
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
		}
		$key = $plugin. '_language';
		${$key}['hash'] = md5(serialize(${$key}));
		$response_lang[$plugin] = ${$key};
	}
}
$response_lang['rtl'] = $rtl;
$language['hash'] = md5(serialize($language));
$mobileapp_language['hash'] = md5(serialize($mobileapp_language));
$chatrooms_language['hash'] = md5(serialize($chatrooms_language));
$response_lang['core'] = $language;
$response_lang['chatrooms'] = $chatrooms_language;
$response_lang['mobile'] = $mobileapp_language;
$response['pushNotifications'] = $pushNotifications;
$response['cookieprefix'] = $cookiePrefix;

if(!empty($pushAPIKey)){
	$response['pushAPIKey'] = $pushAPIKey;
}else{
	$response['pushAPIKey'] = 'MCr80tBuCel7ffIYNwOMSmOxkb0DZvui';
}
if(!empty($pushOauthSecret)){
	$response['pushOauthSecret'] = $pushOauthSecret;
}else{
	$response['pushOauthSecret'] = 'kS5G8pX1XY2Mjrow8z063X7Nd1bKqgA2';
}
if(!empty($pushOauthKey)){
	$response['pushOauthKey'] = $pushOauthKey;
}else{
	$response['pushOauthKey'] = 'uakSEPtycqz9baGjW844JbZR6hp9um4f';
}
if(!empty($notificationName)){
	$response['pushNotificationName'] = $notificationName;
}else{
	$response['pushNotificationName'] = 'CometChat';
}
foreach($response_lang as $key => $val){
	if(is_array($val)){
		foreach($val as $langkey => $langval){
			$response_lang[$key][$langkey] = strip_tags($langval);
		}
	}
}
if(empty($_REQUEST['langhash']) || $_REQUEST['langhash'] <> md5(serialize($response_lang))){
		$response['langhash'] = md5(serialize($response_lang));
		$response['lang'] = $response_lang;
}
if(!empty($headerColor)){
	$themeSettings['tab_title_background'] = $headerColor;
}
$response_css = $themeSettings;
if(empty($_REQUEST['csshash']) || $_REQUEST['csshash'] <> md5(serialize($response_css))){
	$response['csshash'] = md5(serialize($response_css));
	$response['css'] = $response_css;
}

$response['mobile_theme']['login_background'] = $login_background;
$response['mobile_theme']['login_foreground']= $login_foreground;
$response['mobile_theme']['login_placeholder']= $login_placeholder;
$response['mobile_theme']['login_button_pressed']= $login_button_pressed;
$response['mobile_theme']['login_foreground_text']= $login_foreground_text;

$response_config['homepage_URL'] = $homepage_URL;
$response_config['oneonone_enabled'] = $oneonone_enabled;
$response_config['announcement_enabled'] = $announcement_enabled;

$response_config['fullName'] = $fullName;
$response_config['DISPLAY_ALL_USERS'] = DISPLAY_ALL_USERS;
$response_config['REFRESH_BUDDYLIST'] = REFRESH_BUDDYLIST;
$response_config['USE_COMET'] = USE_COMET;
$response_config['minHeartbeat'] = $minHeartbeat;
$response_config['maxHeartbeat'] = $maxHeartbeat;

if(defined('USE_COMET') && USE_COMET == '1'){
	$response_config['KEY_A'] = KEY_A;
	$response_config['KEY_B'] = KEY_B;
	$response_config['KEY_C'] = KEY_C;
	$response_config['TRANSPORT'] = TRANSPORT;
	$response_config['COMET_CHATROOMS'] = COMET_CHATROOMS;
}
if(empty($_REQUEST['confighash']) || $_REQUEST['confighash'] <> md5(serialize($response_config))){
	$response['confighash'] = md5(serialize($response_config));
	$response['config'] = $response_config;
}
$response['avchat_enabled'] = '0';
if(in_array('avchat',$plugins) && file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."avchat".DIRECTORY_SEPARATOR."config.php")){
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."avchat".DIRECTORY_SEPARATOR."config.php");
	if($videoPluginType == '6'){
		$response['avchat_enabled'] = '1';
		$response['webRTCServer'] = $webRTCServer;
	}
}
$response['audiochat_enabled'] = '0';
if(in_array('audiochat',$plugins) && file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."audiochat".DIRECTORY_SEPARATOR."config.php")){
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."audiochat".DIRECTORY_SEPARATOR."config.php");
	if($audioPluginType == '1'){
		$response['audiochat_enabled'] = '1';
		$response['webRTCServer'] = $webRTCServer;
	}
}
$response['filetransfer_enabled'] = '0';
if(in_array('filetransfer',$plugins)){
	$response['filetransfer_enabled'] = '1';
}
$response['report_enabled'] = '0';
if(in_array('report',$plugins) && file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."config.php")){
	$response['report_enabled'] = '1';
}
$response['clearconversation_enabled'] = '0';
if(in_array('clearconversation',$plugins)){
	$response['clearconversation_enabled'] = '1';
}
$response['crclearconversation_enabled'] = '0';
if(in_array('clearconversation',$crplugins)){
	$response['crclearconversation_enabled'] = '1';
}
$response['crfiletransfer_enabled'] = '0';
if(in_array('filetransfer',$crplugins)){
	$response['crfiletransfer_enabled'] = '1';
}

$response['block_user_enabled']= '0';
if(in_array('block', $plugins)){
	$response['block_user_enabled']= '1';
}

$response['chatroomsmodule_enabled'] = '0';
$response['allowusers_createchatroom'] = '0';
$response['realtime_translation'] = '0';
for ($i=0; $i < sizeof($trayicon); $i++) {
	if($trayicon[$i][0] == 'chatrooms'){
		$response['chatroomsmodule_enabled'] = '1';
		include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."config.php");
		if($allowUsers == '1'){
			$response['allowusers_createchatroom'] = '1';
		}
	}
	if($trayicon[$i][0] == 'announcements'){
		if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}else{
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
		}
		$response['config']['announcement_enabled'] = "1";
		$response['lang']['announcements'] = $announcements_language;
		$response['lang']['announcements']['hash']=md5(serialize($announcements_language));
	}

	if($trayicon[$i][0] == 'realtimetranslate'){
		if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."realtimetranslate".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."realtimetranslate".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}else{
			include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."realtimetranslate".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
		}
		$response['realtime_translation'] = '1';
		$response['lang']['realtimetranslate'] = $realtimetranslate_language;
	}
}

$response['ad_unit_id'] = "";


$useragent = (!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : '';
if(phpversion()>='4.0.4pl1'&&(strstr($useragent,'compatible')||strstr($useragent,'Gecko'))){
	if(extension_loaded('zlib')&&GZIP_ENABLED==1){
		$response['ob_gzhandler']=1;
	}else{
		$response['ob_gzhandler']=2;
	}
}else{
	$response['ob_gzhandler']=3;
}

if(!empty($_REQUEST['device_type'])){
	$device = $_REQUEST['device_type'];
	$filesize = "";
	$filemodified = "";
	if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.png")){
		$response['header_image']['image_path'] = BASE_URL."extensions/mobileapp/images/drawable-".$device."/ic_launcher.png";
		$file = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.png";
		$filesize = getimagesize($file);
		$filemodified = filemtime($file);
	}else if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.jpg")){
		$response['header_image']['image_path'] = BASE_URL."extensions/mobileapp/images/drawable-".$device."/ic_launcher.jpg";
		$file = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.jpg";
		$filesize = getimagesize($file);
		$filemodified = filemtime($file);
	}else if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.jpeg")){
		$response['header_image']['image_path'] = BASE_URL."extensions/mobileapp/images/drawable-".$device."/ic_launcher.jpeg";
		$file = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."drawable-".$device.DIRECTORY_SEPARATOR."ic_launcher.jpeg";
		$filesize = getimagesize($file);
		$filemodified = filemtime($file);
	}
	if(!empty($filesize)&&!empty($filemodified)){
		$response['header_image']['image_width'] = $filesize[0];
		$response['header_image']['image_height'] = $filesize[1];
		$response['header_image']['image_modified_time'] = $filemodified;
	}
}

$useragent = (!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : '';
if(phpversion()>='4.0.4pl1'&&(strstr($useragent,'compatible')||strstr($useragent,'Gecko'))){
	if(extension_loaded('zlib')&&GZIP_ENABLED==1 && !in_array('ob_gzhandler', ob_list_handlers())){
		ob_start('ob_gzhandler');
	}else{
		ob_start();
	}
}else{
	ob_start();
}
if (!empty($_GET['callback'])) {
	echo $_GET['callback'].'('.json_encode($response).')';
} else {
	echo json_encode($response);
}