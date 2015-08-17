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

$navigation = <<<EOD
	<div id="leftnav">
		<a href="?module=settings&amp;ts={$ts}">Settings</a>
EOD;

if (defined('SWITCH_ENABLED') && SWITCH_ENABLED == 1) {
	$navigation .= <<<EOD
		<a href="?module=settings&amp;action=whosonline&amp;ts={$ts}">Whos Online List</a>
EOD;
}

$navigation .= <<<EOD
		<a href="?module=settings&amp;action=comet&amp;ts={$ts}">Comet Service</a>
		<a href="?module=settings&amp;action=ccauth&amp;ts={$ts}">Authentication Mode</a>
		<a href="?module=settings&amp;action=caching&amp;ts={$ts}">Caching</a>
		<a href="?module=settings&amp;action=banuser&amp;ts={$ts}">Banned words &amp; users</a>
		<a href="?module=settings&amp;action=baseurl&amp;ts={$ts}">Change Base URL</a>
		<a href="?module=settings&amp;action=changeuserpass&amp;ts={$ts}">Change Admin User/Pass</a>
		<a href="?module=settings&amp;action=cron&amp;ts={$ts}">Cron</a>
		<a href="?module=settings&amp;action=clearcachefiles&amp;ts={$ts}">Clear Cache</a>
		<a href="?module=settings&amp;action=disablecometchat&amp;ts={$ts}">Disable CometChat</a>
	</div>
EOD;

$options = array(
    "hideOffline"                   => array('choice','Hide offline users in Who\'s Online list?'),
    "autoPopupChatbox"              => array('choice','Auto-open chatbox when a new message arrives'),
    "messageBeep"                   => array('choice','Beep on arrival of message from new user?'),
    "beepOnAllMessages"             => array('choice','Beep on arrival of all messages?'),
    "minHeartbeat"                  => array('textbox','Minimum poll-time in milliseconds (1 second = 1000 milliseconds)'),
    "maxHeartbeat"                  => array('textbox','Maximum poll-time in milliseconds'),
    "fullName"                      => array('choice','If set to yes, both first name and last name will be shown in chat conversations'),
    "searchDisplayNumber"           => array('textbox','The number of users in Whos Online list after which search bar will be displayed'),
    "thumbnailDisplayNumber"        => array('textbox','The number of users in Whos Online list after which thumbnails will be hidden'),
    "typingTimeout"                 => array('textbox','The number of milliseconds after which typing to will timeout'),
    "idleTimeout"                   => array('textbox','The number of seconds after which user will be considered as idle'),
    "displayOfflineNotification"    => array('choice','If yes, user offline notification will be displayed'),
    "displayOnlineNotification"     => array('choice','If yes, user online notification will be displayed'),
    "displayBusyNotification"       => array('choice','If yes, user busy notification will be displayed'),
    "notificationTime"              => array('textbox','The number of milliseconds for which a notification will be displayed'),
    "announcementTime"              => array('textbox','The number of milliseconds for which an announcement will be displayed'),
    "scrollTime"                    => array('textbox','Can be set to 800 for smooth scrolling when moving from one chatbox to another'),
    "armyTime"                      => array('choice','If set to yes, show time plugin will use 24-hour clock format'),
    "disableForIE6"                 => array('choice','If set to yes, CometChat will be hidden in IE6'),
    "hideBar"                       => array('choice','Hide bar for non-logged in users?'),
	"disableForMobileDevices"       => array('choice','If set to yes, CometChat bar will be hidden in mobile devices'),
    "startOffline"                  => array('choice','Load bar in offline mode for all first time users?'),
    "fixFlash"                      => array('choice','Set to yes, if Adobe Flash animations/ads are appearing on top of the bar (experimental)'),
    "lightboxWindows"               => array('choice','Set to yes, if you want to use the lightbox style popups'),
    "sleekScroller"                 => array('choice','Set to yes, if you want to use the new sleek scroller'),
    "desktopNotifications"          => array('choice','If yes, Google desktop notifications will be enabled for Google Chrome'),
    "windowTitleNotify"             => array('choice','If yes, notify new incoming messages by changing the browser title'),
    "floodControl"                  => array('textbox','Chat spam control in milliseconds (Disabled if set to 0)'),
    "windowFavicon"                 => array('choice','If yes, Update favicon with number of messages (Supported on Chrome, Firefox, Opera)'),
    "prependLimit"                 => array('textbox','Number of messages that are fetched when load earlier messages is clicked')
);

function index() {
	global $body;
	global $navigation;
	global $options;
    global $ts;

	$form = '';

	foreach ($options as $option => $result) {
		global ${$option};

		$form .= '<div class="titlelong" >'.$result[1].'</div><div class="element">';

		if ($result[0] == 'textbox') {
			$form .= '<input type="text" class="inputbox" name="'.$option.'" value="'.${$option}.'">';
		}

		if ($result[0] == 'choice') {
			if (${$option} == 1) {
				$form .= '<input type="radio" name="'.$option.'" value="1" checked>Yes <input type="radio" name="'.$option.'" value="0" >No';
			} else {
				$form .= '<input type="radio" name="'.$option.'" value="1" >Yes <input type="radio" name="'.$option.'" value="0" checked>No';
			}

		}

		if ($result[0] == 'dropdown') {

			$form .= '<select  name="'.$option.'">';

			foreach ($result[2] as $opt) {
				if ($opt == ${$option}) {
					$form .= '<option value="'.$opt.'" selected>'.ucwords($opt);
				} else {
					$form .= '<option value="'.$opt.'">'.ucwords($opt);
				}
			}

			$form .= '</select>';

		}

		$form .= '</div><div style="clear:both;padding:7px;"></div>';
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=updatesettings&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Settings</h2>
		<h3>If you are unsure about any value, please skip them</h3>

		<div>
			<div id="centernav" style="width:700px">
				$form
			</div>
		</div>
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	</form>
EOD;

	template();

}

function updatesettings() {
        global $ts;
	global $options;

	$data = '';

	foreach ($_POST as $option => $value) {
		$data .= '$'.$option.' = \''.$value.'\';'."\t\t\t// ".$options[$option][1]."\r\n";
	}

	if (!empty($data)) {
		configeditor('SETTINGS',$data,0);
	}

	$_SESSION['cometchat']['error'] = 'Setting details updated successfully';

	header("Location:?module=settings&ts={$ts}");
}

function caching() {
	global $ts;
	global $body;
	global $navigation;

	$nc = "";
	$mc = "";
	$fc = "";
	$mcr = "";
	$apc = "";
	$win = "";
	$sqlite = "";
	$memcached = "";
	$MC_SERVER = MC_SERVER;
	$MC_PORT = MC_PORT;
	$MC_USERNAME = MC_USERNAME;
	$MC_PASSWORD = MC_PASSWORD;
	$MC_NAME = MC_NAME;


	if($MC_NAME == 'files') {
		$fc = "selected = ''";
	} elseif ($MC_NAME == 'memcache') {
		$mc = "selected = ''";
	} elseif ($MC_NAME == 'memcachier') {
		$mcr = "selected = ''";
	}  elseif ($MC_NAME == 'wincache') {
		$win = "selected = ''";
	} elseif ($MC_NAME == 'sqlite') {
		$sqlite = "selected = ''";
	}  elseif ($MC_NAME == 'memcached') {
		$memcached = "selected = ''";
	} elseif ($MC_NAME == 'apc') {
		$apc = "selected = ''";
	} else {
		$nc = "selected = ''";
	}

	$body = <<<EOD
	{$navigation}
	<script>
		$(function(){

			if($("#MC_NAME option:selected").val() == 'memcache' || $("#MC_NAME option:selected").val() == 'memcached') {
				$('.memcache').css('display','block');
				$('.memcachier').hide();
			} else if($("#MC_NAME option:selected").val() == 'memcachier') {
				$('.memcache').css('display','block');
				$('.memcachier').show();
				$('#MC_USERNAME,#MC_PASSWORD').attr('required','true');
			}
		});


		$('select[id^=MC_NAME]').live('change', function() {
			$('#MC_USERNAME,#MC_PASSWORD').removeAttr('required');
			if($("#MC_NAME option:selected").index() == 1 || $("#MC_NAME option:selected").index() == 7) {
			   $('.memcache').css('display','block');
			   $('.memcachier').hide();
			} else if ($("#MC_NAME option:selected").index() == 3){
			   $('#MC_USERNAME,#MC_PASSWORD').attr('required','true');
			   $('.memcache').css('display','block');
			   $('.memcachier').show();
			} else {
			   $('.memcache').css('display','none');
			   $('.memcachier').hide();
			}
		});
		setTimeout(function () {
				var myform = document.getElementById('memcache');
				myform.addEventListener('submit', function(e) {
					e.preventDefault();
					if ($("#MC_NAME option:selected").index() == 1 && ($('#MC_SERVER').val() == null || $('#MC_SERVER').val() == '' || $('#MC_PORT').val() == null || $('#MC_PORT').val() == '')) {
						alert('Please enter memcache server name and port.');
						return false;
					} else if ($("#MC_NAME option:selected").index() == 3 && ($('#MC_SERVER').val() == null || $('#MC_SERVER').val() == '' || $('#MC_PORT').val() == null || $('#MC_PORT').val() == '' || $('#MC_USERNAME').val() == null || $('#MC_USERNAME').val() == '' || $('#MC_PASSWORD').val() == null || $('#MC_PASSWORD').val() == '' )) {
						alert('Please enter all the details for memcachier server.');
					} else if ($("#MC_NAME option:selected").index() == 7 && ($('#MC_SERVER').val() == null || $('#MC_SERVER').val() == '' || $('#MC_PORT').val() == null || $('#MC_PORT').val() == '')){
						alert('Please enter all the details for memcached server.');
					} else {
						myform.submit();
					}
				});
		}, 500);
	</script>
	<form id="memcache" action="?module=settings&action=updatecaching&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Caching</h2>
		<h3>You can set CometChat to use either Memcaching or File caching.</h3>
		<div>
			<div style="float:left;width:60%">
				<div id="centernav">
					<div style="width:200px" class="title">Select caching type:</div><div class="element"><select id="MC_NAME" name="MC_NAME">
							<option value="" {$nc}>No caching</option>
							<option value="memcache" {$mc}>Memcache</option>
							<option value="files" {$fc}>File caching</option>
							<option value="memcachier" {$mcr}>Memcachier</option>
							<option value="apc" {$apc}>APC</option>
							<option value="wincache" {$win}>Wincache</option>
							<option value="sqlite" {$sqlite}>SQLite</option>
							<option value="memcached" {$memcached}>Memcached</option>
						</select></div>
					<div style="clear:both;padding:5px;"></div>
				</div>
				<div id="centernav" class="memcache" style="display:none">
					<div style="width:200px" class="title">Memcache server name:</div><div class="element"><input type="text" id="MC_SERVER" name="MC_SERVER" value={$MC_SERVER}  required="true"/></div>
					<div style="clear:both;padding:5px;"></div>
				</div>
				<div id="centernav" class="memcache" style="display:none">
					<div style="width:200px" class="title">Memcache server port:</div><div class="element"><input type="text" id="MC_PORT" name="MC_PORT" value={$MC_PORT} required="true"/></div>
					<div style="clear:both;padding:5px;"></div>
				</div>
				<div id="centernav" class="memcachier" style="display:none">
					<div style="width:200px" class="title">Memcachier Username:</div><div class="element"><input type="text" id="MC_USERNAME"  name="MC_USERNAME" value="{$MC_USERNAME}" ></div>
					<div style="clear:both;padding:5px;"></div>
				</div>
				<div id="centernav" class="memcachier" style="display:none">
					<div style="width:200px" class="title">Memcachier Password:</div><div class="element"><input type="text" id="MC_PASSWORD" name="MC_PASSWORD" value="{$MC_PASSWORD}" ></div>
					<div style="clear:both;padding:5px;"></div>
				</div>

			</div>
			<div id="rightnav">
				<h1>Tips</h1>
				<ul id="modules_availablemodules">
					<li> Make sure your selected caching type is already enabled on your server. For Memcachier please make sure the port 11211 is open in your firewall.</li>
 				</ul>
			</div>
		</div>
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Listing" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	</form>
EOD;

	template();

}


function updatecaching(){
        global $ts;
	$conn = 1;
	$errorCode = 0;
	$memcacheAuth = 0;
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."cometchat_cache.php");
	if ($_POST['MC_NAME'] == 'memcachier') {
		$memcacheAuth = 1;
		$conn = 0;
		$memcache = new MemcacheSASL;
		$memcache->addServer($_POST['MC_SERVER'], $_POST['MC_PORT']);
		if($memcachierAuth = $memcache->setSaslAuthData($_POST['MC_USERNAME'], $_POST['MC_PASSWORD'])) {
			$memcache->set('auth', 'ok');
			if(!$conn = $memcache->get('auth')) {
				$errorCode = 3;
			}
			$memcache->delete('auth');
		} else {
			$errorCode = 3;
		}
	} elseif ($_POST['MC_NAME'] != '') {
			$conn = 0;
			$memcacheAuth = 1;
			phpFastCache::setup("storage",$_POST['MC_NAME']);
			$memcache = new phpFastCache();
			$driverPresent = (isset($memcache->driver->option['availability'])) ? 0 : 1;
			if ($driverPresent) {
				if ($_POST['MC_NAME'] == 'memcache'){
					$server = array(array($_POST['MC_SERVER'],$_POST['MC_PORT'],1));
					$memcache->option('server', $server);
				}
				$memcache->set('auth','ok',30);
				if (!$conn = $memcache->get('auth')){
					$errorCode = 1;
				}
				$memcache->delete('auth');
			}
	}
	if ($conn && !$errorCode) {
		$data = 'define(\'MEMCACHE\',\''.$memcacheAuth.'\');'."\r\n";
		$data .= 'define(\'MC_SERVER\',\''.$_POST['MC_SERVER'].'\');'."\t// Set name of your memcache  server\r\n";
		$data .= 'define(\'MC_PORT\',\''.$_POST['MC_PORT'].'\');'."\t\t\t// Set port of your memcache  server\r\n";
		$data .= 'define(\'MC_USERNAME\',\''.$_POST['MC_USERNAME'].'\');'."\t\t\t\t\t\t\t// Set username of memcachier  server\r\n";
		$data .= 'define(\'MC_PASSWORD\',\''.$_POST['MC_PASSWORD'].'\');'."\t\t\t// Set password your memcachier  server\r\n";
		$data .= 'define(\'MC_NAME\',\''.$_POST['MC_NAME'].'\');'."\t\t\t// Set name of caching method if 0 : '', 1 : memcache, 2 : files, 3 : memcachier, 4 : apc, 5 : wincache, 6 : sqlite & 7 : memcached";
		configeditor('MEMCACHE',$data,0);

		$_SESSION['cometchat']['error'] = 'Caching details updated successfully.';
	} else {
		if($_POST['MC_NAME']== 'memcachier') {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your Memchachier server details';
		} elseif ($_POST['MC_NAME'] == 'files') {
			$_SESSION['cometchat']['error'] = 'Please check file permission of your cache directory. Please try 755/777/644';
		} elseif ($_POST['MC_NAME'] == 'apc') {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your APC configuration.';
		} elseif ($_POST['MC_NAME'] == 'wincache') {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your Wincache configuration.';
		} elseif ($_POST['MC_NAME'] == 'sqlite') {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your SQLite configuration.';
		} elseif ($_POST['MC_NAME'] == 'memcached') {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your Memcached configuration.';
		} else {
			$_SESSION['cometchat']['error'] = 'Failed to update caching details. Please check your Memcache server configuration.';
		}
	}

	header("Location:?module=settings&action=caching&ts={$ts}");
}

function whosonline() {
	global $body;
	global $navigation;
        global $ts;

	$dy = "";
	$dn = "";

	if (defined('DISPLAY_ALL_USERS') && DISPLAY_ALL_USERS == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=updatewhosonline&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Who`s Online List</h2>
		<h3>You can set CometChat to show either all online users or all friends in the "Who's Online" list.</h3>

		<div>
			<div id="centernav">
				<div class="title" style="width:200px">Show all online users:</div><div class="element"><input type="radio" name="dou" value="1" $dy>Yes <input type="radio" $dn name="dou" value="0" >No</div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Tips</h1>
				<ul id="modules_availablemodules">
					<li>Displaying all online users is recommended for small sites only.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Listing" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	</form>
EOD;

	template();

}



function updatewhosonline() {
        global $ts;
	$data = 'define(\'DISPLAY_ALL_USERS\',\''.$_POST['dou'].'\');';
	configeditor('DISPLAYSETTINGS',$data,0);

	$_SESSION['cometchat']['error'] = 'Whos online listing updated successfully';

	header("Location:?module=settings&action=whosonline&ts={$ts}");

}

function clearcachefiles() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=clearcachefilesprocess&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Clear Cache</h2>
		<h3>Click Clear Cache to remove all cached and CSS/JS minified files</h3>
		<div>
			<div id="rightnav">
				<h1>Info</h1>
				<ul id="modules_availablemodules">
					<li>All the minified JS and CSS files will be removed once you click the Clear Cache button</li>
				</ul>
			</div>
		</div>
		<input type="submit" value="Clear Cache" class="button">
	</div>
	<div style="clear:both"></div>

EOD;

	template();
}

function clearcachefilesprocess() {
        global $ts;
	$_SESSION['cometchat']['error'] = 'Cache cleared successfully';

	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'jsmin.php');
	clearcachejscss(dirname(dirname(__FILE__)));

	header("Location:?module=settings&action=clearcachefiles&ts={$ts}");
}

function clearcachejscss($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
	$skipByExclude = false;
	$handle = opendir($directory);
        $ingorelistjs = array('jquery.min.js','jquery-ui.min.js','jquery-ui-i18n.min.js','favico-0.3.3.min.js','jquery-ui.min.js');
        $ingorelistcss = array('bootstrap.min.css','jquery-ui-1.8.21.custom.min.css','foundation.min.css');
	if ($handle) {
		while (false !== ($file = readdir($handle))) {
			preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
			if($exclude){
				preg_match($exclude, $file, $skipByExclude);
			}
			if (!$skip && !$skipByExclude) {
				if (is_dir($directory. DIRECTORY_SEPARATOR . $file) && !in_array($file,array('admin','images','swf','cache','lang','i','m','temp'))) {
					clearcachejscss($directory. DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude);
				} else {
					if($listFiles){
						if((strpos($file,'.js') >= -1) && (strpos($file,'.js.bak') <= -1) && (!in_array($file, $ingorelistjs))){
							$file = $directory . DIRECTORY_SEPARATOR . $file;
							$file = str_replace('.js','.min.js',$file);
                                                        if(file_exists($file)){
                                                            unlink($file);
                                                        }
						}else if((strpos($file,'.css') >= -1) && (strpos($file,'.css.bak') <= -1) && (!in_array($file, $ingorelistcss))) {
							$file = $directory . DIRECTORY_SEPARATOR . $file;
							$file = str_replace('.css','.min.css',$file);
                                                        if(file_exists($file)){
                                                            unlink($file);
                                                        }
						}
					}
				}
			}
		}
		closedir($handle);
	}

        if ($handle = opendir(dirname(dirname(__FILE__)).'/cache/')) {
		   while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "index.html") {
			 @unlink(dirname(dirname(__FILE__)).'/cache/'.$file);
		   }
	   }
	}
}

function disablecometchat() {
	global $body;
	global $navigation;
        global $ts;

	$dy = "";
	$dn = "";

	if (defined('BAR_DISABLED') && BAR_DISABLED == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=updatedisablecometchat&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Disable CometChat</h2>
		<h3>This feature will temporarily disable CometChat on your site.</h3>

		<div>
			<div id="centernav">
				<div class="title" style="width:200px">Disable CometChat:</div><div class="element"><input type="radio" name="dou" value="1" $dy>Yes <input type="radio" $dn name="dou" value="0" >No</div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>CometChat will stop appearing on your site if this option is set to yes.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	</form>
EOD;

	template();

}

function updatedisablecometchat() {
        global $ts;
	$data = 'define(\'BAR_DISABLED\',\''.$_POST['dou'].'\');';
	configeditor('DISABLEBAR',$data,0);

	$_SESSION['cometchat']['error'] = 'CometChat updated successfully';

	header("Location:?module=settings&action=disablecometchat&ts={$ts}");

}

function banuser() {
	global $body;
	global $navigation;
	global $bannedUserIDs;
	global $bannedUserIPs;
	global $bannedMessage;
	global $bannedWords;
        global $ts;

	$bannedids = '';
	$bannedips = '';

	foreach ($bannedUserIDs as $b) {
		$bannedids .= $b.',';
	}

	foreach ($bannedUserIPs as $b) {
		$bannedips .= $b.',';
	}

	$bannedw = '';

	foreach ($bannedWords as $b) {
		$bannedw .= "'".$b.'\',';
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=banuserprocess&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Banned words and users</h2>
		<h3>You can ban users and add words to the abusive list. If you do not know the user's ID, <a href="?module=settings&amp;action=finduser&amp;ts={$ts}">click here to find out</a></h3>

		<div>
			<div id="centernav">
				<div class="title">Banned Words:</div><div class="element"><input type="text" class="inputbox" name="bannedwords" value="$bannedw"></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Banned User IDs:</div><div class="element"><input type="text" class="inputbox" name="bannedids" value="$bannedids"> <a href="?module=settings&amp;action=finduser&amp;ts={$ts}">Don't know ID?</a></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Banned User IPs:</div><div class="element"><input type="text" class="inputbox" name="bannedips" value="$bannedips"> </div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Banned Message:</div><div class="element"><input type="text" class="inputbox" name="bannedmessage" value="$bannedMessage" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Please use comma to separate IDs and words</li>
					<li>Banned users will not be able to use IM and chatroom functionality of CometChat</li>
				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Modify" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}


function banuserprocess() {
        global $ts;
	if (!empty($_POST['bannedmessage'])) {

		$bannids = explode(",",$_POST['bannedids']);
		foreach ($bannids as $id) {
			if (!empty($id) && $id != "'" && $id != "," && $id != " ") {
				if(!preg_match("/[\d]+/", $id) || preg_match("/\./", $id) ) {
					header("Location:?module=settings&action=banuser&ts={$ts}");
					return;
				}
			}
		}
		$words = array();

		$inputWords = explode(",",$_POST['bannedwords']);

		foreach ($inputWords as $word) {
			$word = preg_replace("/\s+/"," ",str_replace("'","",$word));

			if (!empty($word) && $word != "'" && $word != "," && $word != " ") {
				array_push($words,$word);
			}
		}

		$words = "'".implode("','",$words)."'";

		if ($words == "''") { $words = ''; }

		$ips = array();

		$inputips = explode(",",$_POST['bannedips']);

		foreach ($inputips as $ip) {
			$ip = preg_replace("/\s+/"," ",str_replace("'","",$ip));
			if (!empty($ip) && $ip != "'" && $ip != "," && $ip != " ") {
				if (!filter_var ($ip, FILTER_VALIDATE_IP)) {
					header("Location:?module=settings&action=banuser&ts={$ts}");
					return;
				}
				array_push($ips,$ip);
			}
		}

		$ips = "'".implode("','",$ips)."'";

		if ($ips == "''") { $ips = ''; }

		$_SESSION['cometchat']['error'] = 'Banned words and users successfully modified.';
		$_POST['bannedmessage'] = str_replace("'", "", $_POST['bannedmessage']);
		$data = '$bannedWords = array( '.$words.' );'."\r\n".'$bannedUserIPs = array( '.$ips.' );'."\r\n".'$bannedUserIDs = array('.$_POST['bannedids'].');'."\r\n".'$bannedMessage = \''.$_POST['bannedmessage'].'\';';
		configeditor('BANNED',$data);
	}
	header("Location:?module=settings&action=banuser&ts={$ts}");
}

function changeuserpass() {
	global $body;
	global $navigation;
        global $ts;

	$nuser = ADMIN_USER;
	$npass = ADMIN_PASS;

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=changeuserpassprocess&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Change administration username and password</h2>
		<h3>If you are unable to login after changing your user/pass, simply edit config.php and find ADMIN_USER</h3>

		<div>
			<div id="centernav">
				<div class="title">New Username:</div><div class="element"><input type="text" class="inputbox" name="nuser" value="$nuser" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">New Password:</div><div class="element"><input type="text" class="inputbox" name="npass" value="$npass" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Do NOT use ` or \ in your username or password</li>
					<li>Proceed with caution.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Change user/pass" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}

function changeuserpassprocess() {
        global $ts;
	if (!empty($_POST['nuser']) && !empty($_POST['npass'])) {
		$_SESSION['cometchat']['error'] = 'User/pass successfully modified';
		$data = "define('ADMIN_USER','{$_POST['nuser']}');\r\ndefine('ADMIN_PASS','{$_POST['npass']}');";
		configeditor('ADMIN',$data);
	}
	header("Location:?module=dashboard&ts={$ts}");
}



function baseurl() {
	global $body;
	global $navigation;
        global $ts;

	$baseurl = BASE_URL;

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=updatebaseurl&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Update Base URL</h2>
		<h3>If CometChat is not working on your site, your Base URL might be incorrect.</h3>


		<div>
			<div id="centernav">
				<div class="titlelong" style="text-align:left;padding-left:40px;">Our detection algorithm suggests: <b><script>document.write(window.location.pathname.replace("admin/","").replace("admin",""));</script></b></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Base URL:</div><div class="element"><input type="text" class="inputbox" name="baseurl" value="$baseurl" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>If the Base URL is incorrect, CometChat will stop working on your site.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update settings" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}

function updatebaseurl() {
        global $ts;
	if (!empty($_POST['baseurl'])) {

		$baseurl = str_replace('\\','/',$_POST['baseurl']);

		if ($baseurl[0] != '/' && strpos($baseurl,'http://')===false && strpos($baseurl,'https://')===false) {
			$baseurl = '/'.$baseurl;
		}

		if ($baseurl[strlen($baseurl)-1] != '/') {
			$baseurl = $baseurl.'/';
		}

		$_SESSION['cometchat']['error'] = 'Base URL successfully modified';
		$data = "define('BASE_URL','{$baseurl}');";
		configeditor('BASE URL',$data);
	}
	header("Location:?module=settings&action=baseurl&ts={$ts}");
}



function comet() {
	global $body;
	global $navigation;
        global $ts;

	$dy = "";
	$dn = "";
	$dy2 = "";
	$dn2 = "";

	if (defined('USE_COMET') && USE_COMET == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	$keya = KEY_A;
	$keyb = KEY_B;
	$keyc = KEY_C;

	$overlay = '';

	if (!checkCurl()) {
		$overlay = <<<EOD
			<script>
			jQuery('#rightcontent').before('<div id="overlaymain" style="position:relative"></div>');
					var overlay = $('<div></div>')
						.attr('id','overlay')
						.css({
							'position':'absolute',
							'height':$('#rightcontent').innerHeight(),
							'width':$('#rightcontent').innerWidth(),
							'background-color':'#FFFFFF',
							'opacity':'0.9',
							'z-index':'99',
							'right': '0',
							'margin-left':'1px'
						})
						.appendTo('#overlaymain');
						$('<span>cURL extension is disabled on your server. Please contact your webhost to enable it.<br> cURL is required for CometService.</span>')
							.css({'z-index':' 9999',
							'color':'#000000',
							'font-size':'15px',
							'font-weight':'bold',
							'display':'block',
							'text-align':'center',
							'margin':'auto',
							'position':'absolute',
							'width':'100%',
							'top':'100px',
							'right':' -87px'
						}).appendTo('#overlaymain');
		</script>
EOD;
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=updatecomet&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Comet Service</h2>
		<h3>If you are using our hosted Comet service, please enter the details here</h3>

		<div>
			<div id="centernav">
				<div class="title" style="width:200px">Use Comet Service?</div><div class="element"><input type="radio" name="dou" value="1" $dy>Yes <input type="radio" $dn name="dou" value="0" >No</div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Key A:</div><div class="element"><input type="text" class="inputbox" name="keya" value="$keya" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Key B:</div><div class="element"><input type="text" class="inputbox" name="keyb" value="$keyb" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Key C:</div><div class="element"><input type="text" class="inputbox" name="keyc" value="$keyc" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>

			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Make sure that you have subscribed to our service before enabling this service.</li>
					<li>Remember to de-activate the chat history plugin.</li>
					<li>If you face load issues after activating the service, simply switch off Save Logs feature.</li>
					<li>After activation/de-activation be sure to clear your browser cache.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update settings" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	{$overlay}
EOD;

	template();
}

function updatecomet() {
        global $ts;
	$_SESSION['cometchat']['error'] = 'Comet service settings successfully updated';
	$data = "define('USE_COMET','".$_POST['dou']."');\r\ndefine('KEY_A','".$_POST['keya']."');\r\ndefine('KEY_B','".$_POST['keyb']."');\r\ndefine('KEY_C','".$_POST['keyc']."');";
	configeditor('COMET',$data);

	header("Location:?module=settings&action=comet&ts={$ts}");
}

function finduser() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=searchlogs&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Find User ID</h2>
		<h3>You can search by username.</h3>

		<div>
			<div id="centernav">
				<div class="title">Username:</div><div class="element"><input type="text" class="inputbox" name="susername" required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Search Database" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;action=banuser&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function searchlogs() {
        global $ts;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $navigation;
	global $body;
        global $bannedUserIDs;

	$username = $_REQUEST['susername'];

	if (empty($username)) {
		// Base 64 Encoded
		$username = 'Q293YXJkaWNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgc2FmZT8NCkV4cGVkaWVuY3kgYXNrcyB0aGUgcXVlc3Rpb24gLSBpcyBpdCBwb2xpdGljPw0KVmFuaXR5IGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcG9wdWxhcj8NCkJ1dCBjb25zY2llbmNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcmlnaHQ/DQpBbmQgdGhlcmUgY29tZXMgYSB0aW1lIHdoZW4gb25lIG11c3QgdGFrZSBhIHBvc2l0aW9uDQp0aGF0IGlzIG5laXRoZXIgc2FmZSwgbm9yIHBvbGl0aWMsIG5vciBwb3B1bGFyOw0KYnV0IG9uZSBtdXN0IHRha2UgaXQgYmVjYXVzZSBpdCBpcyByaWdodC4=';
	}

	$sql = ("select ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." id, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username from ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." where ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." LIKE '%".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($username))."%'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$userslist = '';

	while ($user = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$user['username'] = processName($user['username']);
		}
                $banuser = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=settings&amp;action=banusersprocess&amp;susername='.$username.'&amp;bannedids='.$user['id'].'&amp;ts='.$ts.'"><img style="width: 16px;" title="Ban User" src="images/ban.png"></a>';

                if(in_array($user['id'],$bannedUserIDs)) {
                    $banuser = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=settings&amp;action=unbanusersprocess&amp;susername='.$username.'&amp;bannedids='.$user['id'].'&amp;ts='.$ts.'"><img style="width: 16px;" title="Unban User" src="images/unban.png"></a>';
                }
		$userslist .= '<li class="ui-state-default cursor_default"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$user['username'].' - '.$user['id'].'</span>'.$banuser.'<div style="clear:both"></div></li>';
	}

	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Search results</h2>
		<h3>Please find the user id next to each username. <a href="?module=settings&amp;action=finduser&amp;ts={$ts}">Click here to search again</a></h3>

		<div>
			<ul id="modules_logs">
				$userslist
			</ul>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}

function banusersprocess() {
        global $ts;
        global $bannedUserIDs;
	global $bannedUserIPs;
	global $bannedMessage;
	global $bannedWords;

        $bannedids = '';

	foreach ($bannedUserIDs as $b) {
		$bannedids .= $b.',';
	}
        $bannedids .= $_REQUEST['bannedids'];
        $bannedips = '';

	foreach ($bannedUserIPs as $b) {
		$bannedips .= '\''.$b.'\',';
	}

        $bannedw = '';
        foreach ($bannedWords as $b) {
		$bannedw .= "'".$b.'\',';
	}

        $_SESSION['cometchat']['error'] = 'Ban ID list successfully modified.';
        $data = '$bannedWords = array( '.$bannedw.' );'."\r\n".'$bannedUserIPs = array( '.$bannedips.' );'."\r\n".'$bannedUserIDs = array('.$bannedids.');'."\r\n".'$bannedMessage = \''.$bannedMessage.'\';';
        configeditor('BANNED',$data);
        header("Location:?module=settings&action=searchlogs&susername={$_GET['susername']}&ts={$ts}");
}

function unbanusersprocess() {
        global $ts;
        global $bannedUserIDs;
	global $bannedUserIPs;
	global $bannedMessage;
	global $bannedWords;

        $bannedids = $bannedUserIDs;

        if(($key = array_search($_GET['bannedids'], $bannedids)) !== false) {
            unset($bannedids[$key]);
        }
        $bannedids =  implode(',',$bannedids);

        $bannedips = '';

	foreach ($bannedUserIPs as $b) {
		$bannedips .= '\''.$b.'\',';
	}

        $bannedw = '';
        foreach ($bannedWords as $b) {
		$bannedw .= "'".$b.'\',';
	}

        $_SESSION['cometchat']['error'] = 'Ban ID list successfully modified.';
        $data = '$bannedWords = array( '.$bannedw.' );'."\r\n".'$bannedUserIPs = array( '.$bannedips.' );'."\r\n".'$bannedUserIDs = array('.$bannedids.');'."\r\n".'$bannedMessage = \''.$bannedMessage.'\';';
        configeditor('BANNED',$data);
        header("Location:?module=settings&action=searchlogs&susername={$_GET['susername']}&ts={$ts}");
}

function cron() {
	global $body;
	global $navigation;
	global $trayicon;
	global $plugins;
        global $ts;

	$auth = md5(md5(ADMIN_USER).md5(ADMIN_PASS));
	$baseurl = BASE_URL;
	$datamodules = '';
	$dataplugins = '';

	foreach ($trayicon as $t) {
		if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$t[0].DIRECTORY_SEPARATOR.'cron.php')) {
			if($t[0] == "chatrooms") {
				$datamodules .= '<div style="clear:both;padding:2.5px;"></div><li class="titlecheck" ><input class="input_sub" type="checkbox" name="cron[inactiverooms]" value="1" onclick="javascript:cron_checkbox_check(\''.$t[0].'\',\'modules\')">Delete all user created inactive chatrooms<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link(\''.$baseurl.'\',\'inactiverooms\',\''.$auth.'\')"><img src="images/embed.png" style="float: right;margin-right: 17px;" title="Cron URL Code"></a></li><div style="clear:both;padding:2.5px;"></div><li class="titlecheck" ><input class="input_sub"  type="checkbox" name="cron[chatroommessages]" value="1" onclick="javascript:cron_checkbox_check(\''.$t[0].'\',\'modules\')">Delete all chatroom messages user created inactive chatrooms<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link(\''.$baseurl.'\',\'chatroommessages\',\''.$auth.'\')"><img src="images/embed.png" style="float: right;margin-right: 17px;" title="Cron URL Code"></a></li><div style="clear:both;padding:2.5px;"></div><li class="titlecheck" ><input class="input_sub"  type="checkbox" name="cron[inactiveusers]" value="1" onclick="javascript:cron_checkbox_check(\''.$t[0].'\',\'modules\')">Delete all user created inactive users from chatrooms<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link(\''.$baseurl.'\',\'inactiveusers\',\''.$auth.'\')"><img src="images/embed.png" style="float: right;margin-right: 17px;" title="Cron URL Code"></a></li>';
			} else {
				$datamodules .= '<div style="clear:both;padding:2.5px;"></div><li class="titlecheck" ><input class="input_sub"  type="checkbox" name="cron['.$t[0].']" value="1" onclick="javascript:cron_checkbox_check(\''.$t[0].'\',\'modules\')"> Run cron for '.$t[0].'<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link(\''.$baseurl.'\',\''.$t[0].'\',\''.$auth.'\')"><img src="images/embed.png" style="float: right;margin-right: 17px;" title="Cron URL Code"></a></li>';
			}
		}
	}

	foreach ($plugins as $p) {
		if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$p.DIRECTORY_SEPARATOR.'cron.php')) {
			$dataplugins .='<div style="clear:both;padding:2.5px;"></div>
			<li class="titlecheck" ><input  class="input_sub" type="checkbox" name="cron['.$p.']" value="1" onclick="javascript:cron_checkbox_check(\''.$p.'\',\'plugins\')">Delete all files from sent with '.$p.'<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link(\''.$baseurl.'\',\''.$p.'\',\''.$auth.'\')"><img src="images/embed.png" style="float: right;margin-right: 17px;" title="Cron URL Code"></a></li>';
		}
	}

	$body = <<<EOD
	$navigation
	<form action="?module=settings&action=processcron&ts={$ts}" method="post" onsubmit="return cron_submit()">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Cron</h2>
		<h3>This feature will remove old messages; old handwrite messages and old files of filetransfer.</h3>

		<div>
			<div id="centernav">
				<div id='error' style="display:none;color:red;font-size:13px">Please select atleast one the options</div>
				<h4><span><input id='individual' style="vertical-align: middle; margin-top: -2px;" type="radio" name="cron[type]" value="individual" onclick="javascript:$('#individualcat').slideDown('slow')" checked>Run individual crons</span></h4>

				<div id="individualcat" >
					<div class="titlecheck" ><input id="plugins" type="checkbox" name="cron[plugins]" value="1"  class="title_class" onclick="check_all('plugins','sub_plugins','{$auth}')">
						<div class="maintext" onclick="javascript:$('#sub_plugins').slideToggle('slow')" style="cursor: pointer;">Run all plugins cron<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','plugins','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></div>
					</div>
					<div id="sub_plugins">
						<ul style="margin-left: 60px;width:88%">
							{$dataplugins}
						</ul>
					</div>

					<div style="clear:both;padding:5.5px;"></div>
					<div class="titlecheck" ><input id="modules" type="checkbox" name="cron[modules]" value="1" class="title_class" onclick="check_all('modules','sub_modules','{$auth}')">
						<div class="maintext" onclick="javascript:$('#sub_modules').slideToggle('slow')" style="cursor: pointer;">Run all modules cron<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','modules','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></div>
					</div>
					<div id="sub_modules">
						<ul style="margin-left: 60px;width:88%">
							{$datamodules}
						</ul>
					</div>

					<div style="clear:both;padding:5.5px;"></div>
					<div class="titlecheck" ><input id="core" type="checkbox" name="cron[core]" value="1" class="title_class" onclick="check_all('core','sub_core','{$auth}')">
						<div class="maintext" onclick="javascript:$('#sub_core').slideToggle('slow')" style="cursor: pointer;">Run cron for core<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','core','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></div>
					</div>
					<div id="sub_core">
						<ul style="margin-left: 60px;width:88%">
							<div style="clear:both;padding:2.5px;"></div>
							<li class="titlecheck" ><input class="input_sub" type="checkbox" name="cron[messages]" value="1"onclick="javascript:cron_checkbox_check('messages','core')">Delete one-to-one messages execpt unread<a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','messages','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></li>
							<div style="clear:both;padding:2.5px;"></div>
							<li class="titlecheck" ><input class="input_sub" type="checkbox" name="cron[guest]" value="1" onclick="javascript:cron_checkbox_check('guest','core')"><span>Delete all guest`s entries</span><a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','guest','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></li>
							<div style="clear:both;padding:2.5px;"></div>
						</ul>
					</div>
				</div>
				<div style="clear:both"></div>
				<h4><span><input id='all' style="vertical-align: middle; margin-top: -2px;" type="radio" name="cron[type]" value="all" onclick="javascript:$('#individualcat').slideUp('slow')" >Run entire cron</span><a  href="javascript:void(0)" style="margin-left:5px;" onclick="javascript:cron_auth_link('{$baseurl}','all','{$auth}')"><img src="images/embed.png" style="float: right; margin-right: 17px;" title="Cron URL Code"></a></h4>

			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>'Run entire cron' will run for all the options under Run individual crons.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="hidden" value="{$auth}" name="auth">
		<input type="submit" value="Run" class="button">&nbsp;&nbsp;or <a href="?module=settings&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	</form>
EOD;

	template();

}

function processcron() {
    global $ts;
	$auth = md5(md5(ADMIN_USER).md5(ADMIN_PASS));
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'cron.php');
	$_SESSION['cometchat']['error'] = 'Cron executed successfully';
	header("Location:?module=settings&action=cron&ts={$ts}");
}

function ccauth() {
	global $body;
	global $navigation;
	global $ccactiveauth;
	global $guestsMode;
	global $guestsList;
	global $guestsUsersList;
	global $guestnamePrefix;
    global $ts;

	$ccauthoptions = array('Facebook','Google','Twitter');
	if(USE_CCAUTH == '1'){
		$ccauthshow = '';
		$siteauthshow = 'style="display:none"';
		$siteauthradio_checked = '';
		$ccauthradio_checked = 'checked';
	}else{
		$siteauthshow = '';
		$ccauthshow = 'style="display:none"';
		$ccauthradio_checked = '';
		$siteauthradio_checked = 'checked';
	}
	$authmode = USE_CCAUTH;
	$ccactiveauthlist = '';
	$ccauthlistoptions = '';
	$no = 0;
	$no_auth = '';
	foreach ($ccauthoptions as $ccauthoption) {
        ++$no;
        $ccauthhref = 'onclick="ccauth_addauthmode('.$no.',\''.$ccauthoption.'\');" style="cursor: pointer;"';
		if (in_array($ccauthoption, $ccactiveauth)) {
			$ccauthhref = 'style="opacity: 0.5;cursor: default;"';
		}
		$ccactiveauthdata = '<span style="font-size:11px;float:right;margin-top:2px;margin-right:5px;"><a '.$ccauthhref.' id="'.$ccauthoption.'">add</a></span>';

		$ccauthlistoptions .= '<li class="ui-state-default"><div class="cometchat_ccauthicon cometchat_'.$ccauthoption.'" style="margin:0;margin-right:5px;float:left;"></div><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$ccauthoption.'</span>'.$ccactiveauthdata.'<div style="clear:both"></div></li>';
	}
	$no = 0;
	foreach ($ccactiveauth as $ccauthoption) {
		++$no;
		$config = ' <a href="javascript:void(0)" onclick="javascript:auth_configauth(\''.$ccauthoption.'\')" style="margin-right:5px"><img src="images/config.png" title="Configure"></a>';
        $ccactiveauthlist .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ccauthoption.'" rel="'.$ccauthoption.'"><img height="16" width="16" src="images/'.$ccauthoption.'.png" style="margin:0;float:left;"></img><div class="cometchat_ccauthicon cometchat_'.$ccauthoption.'" style="margin:0;margin-right:5px;margin-top:2px;float:left;"></div><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ccauthoption.'_title">'.stripslashes($ccauthoption).'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$config.'<a href="javascript:void(0)" onclick="javascript:ccauth_removeauthmode(\''.$no.'\')"><img src="images/remove.png" title="Remove Authentication Mode"></a></span><div style="clear:both"></div></li>';
	}

	if(!$ccactiveauthlist){
		$no_auth .= '<div id="no_auth" style="width: 480px;float: left;color: #333333;">You have no Authentication Mode activated at the moment. To activate an Authentication Mode, please add them from the list of available Authentication Modes.</div>';
	}

	$dy = "";
	$dn = "";
	$gL1 = $gL2 = $gL3 = $gUL1 = $gUL2 = $gUL3 = '';

	if ($guestsMode == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	if ($guestsList == 1) {	$gL1 = "selected"; }
	if ($guestsList == 2) {	$gL2 = "selected"; }
	if ($guestsList == 3) {	$gL3 = "selected"; }

	if ($guestsUsersList == 1) { $gUL1 = "selected"; }
	if ($guestsUsersList == 2) { $gUL2 = "selected"; }
	if ($guestsUsersList == 3) { $gUL3 = "selected"; }

	$body = <<<EOD
	$navigation
	<form onsubmit="return ccauth_updateorder({$authmode});" action="?module=settings&action=updateauthmode&ts={$ts}" method="post">
	<input type="hidden" name="cc_auth_order" id="cc_auth_order"></input>
	<div id="rightcontent" style="float:left;width:725px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Authentication Mode</h2>
		<h3>You can choose to either integrate with your site's login system (if you have one) or to use our social login feature to enable your users to login using their social accounts.</h3>
		<div id="site_auth" class="auth_container">
			<div style="overflow: hidden;">
				<input type="radio" name="auth_mode" class="auth_select" id="site_auth_radio" value="site_auth" $siteauthradio_checked>
				<h2 class="auth_select_text">Site's Authentication</h2>
			</div>
			<div id="site_auth_options" {$siteauthshow}>
				<div style="float: left;width: 725px;">
					<div id="centernav">
						<div class="title" style="width:200px">Enable Guest Chat:</div><div class="element"><input type="radio" name="guestsMode" value="1" $dy>Yes <input type="radio" $dn name="guestsMode" value="0" >No</div>
						<div style="clear:both;padding:5px;"></div>
					</div>

					<div id="centernav">
						<div class="title" style="width:200px">Prefix for guest names:</div><div class="element"><input type="text" name="guestnamePrefix" value="$guestnamePrefix"></div>
						<div style="clear:both;padding:5px;"></div>
					</div>

					<div id="centernav">
						<div class="title" style="width:200px">In Who`s Online list, for guests:</div><div class="element"><select name="guestsList"><option value="1" $gL1>Show only guests</option><option value="2" $gL2>Show only logged in users</option><option value="3" $gL3>Show both</option></select></div>
						<div style="clear:both;padding:5px;"></div>
					</div>

					<div id="centernav">
						<div class="title" style="width:200px">And for logged in users:</div><div class="element"><select name="guestsUsersList"><option value="1" $gUL1>Show only guests</option><option value="2" $gUL2>Show only logged in users</option><option value="3" $gUL3>Show both</option></select></div>
						<div style="clear:both;padding:5px;"></div>
					</div>
				</div>
			</div>
		</div>
		<div id="cc_auth" class="auth_container">
			<div style="overflow: hidden;">
				<input type="radio" name="auth_mode" class="auth_select" id="cc_auth_radio" value="cc_auth" {$ccauthradio_checked}>
				<h2 class="auth_select_text">Social Login</h2>
			</div>
			<div id="cc_auth_options" {$ccauthshow}>
				<div style="overflow:hidden">
					<ul id="auth_livemodes" class="ui-sortable" unselectable="on">
						{$no_auth}
						{$ccactiveauthlist}
					</ul>
					<div id="rightnav" style="margin-top:5px">
						<h1>Available Modes</h1>
						<ul id="auth_availableauthmodes">
						$ccauthlistoptions
						</ul>
					</div>
				</div>
				<div>
				</div>
			</div>
		</div>
		<input type="submit" value="Update Authentication Mode" class="button">
	</div>

	<div style="clear:both"></div>
	</form>

	<script type="text/javascript">
		$(function() {
			$("#auth_livemodes").sortable({
				items: "li:not(.ui-state-unsort)",
				connectWith: 'ul'
			});
			$("#auth_livemodes").disableSelection();
		});
		$(function(){
			$('#site_auth_radio').live('click',function(){
				$('#site_auth_options').show('slow');
				$('#cc_auth_options').hide('slow');
			});
			$('#cc_auth_radio').live('click',function(){
				$('#cc_auth_options').show('slow');
				$('#site_auth_options').hide('slow');
			});
		});
	</script>
EOD;

	template();

}

function updateauthmode() {
	global $ts;
	global $ccactiveauth;

	$auth_mode = 0;

	if($_POST['auth_mode'] == 'cc_auth'){
		$auth_mode = 1;
	}

	if(USE_CCAUTH!=$auth_mode){
		$sql = ("truncate table `cometchat`;truncate table cometchat_block;truncate table cometchat_chatroommessages;truncate table cometchat_chatrooms;truncate table cometchat_chatrooms_users;truncate table cometchat_comethistory;truncate table cometchat_status;CREATE TABLE IF NOT EXISTS `cometchat_users` (`id` int(11) NOT NULL AUTO_INCREMENT,`username` varchar(100) NOT NULL,`displayname` varchar(100) NOT NULL,`avatar` varchar(200) NOT NULL,`link` varchar(200) NOT NULL,`grp` varchar(25) NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `username` (`username`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
		mysqli_multi_query($GLOBALS['dbh'],$sql);
	}

	$ccactiveauthdata = 'define(\'USE_CCAUTH\',\''.$auth_mode.'\');'."\n\n".'$ccactiveauth = array(';

	foreach ($_POST as $option => $value) {
		if($option!='auth_mode'){
			if($option == 'cc_auth_order'){
				$ccactiveauthdata .= substr($value,0,-1).');'."\r\n\n";
			}else{
				$ccactiveauthdata .= '$'.$option.' = \''.$value.'\';'."\n";
			}
		}
	}

	configeditor('CCAUTH',$ccactiveauthdata);

	$_SESSION['cometchat']['error'] = 'Authentication Mode details updated successfully';

	header("Location:?module=settings&action=ccauth&ts={$ts}");
}
