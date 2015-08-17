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
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."cometchat_init.php");
    if (!empty($_REQUEST['channel'])) {
		sendCCResponse(1);
	}
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."emoji.php");

	$cookiefile = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'cookie.txt';
	$pushUsername = 'cometchat-admin';
	$pushPassword = 'cometchat-admin';

	if (empty($pushAPIKey)) {
		$pushAPIKey = 'MCr80tBuCel7ffIYNwOMSmOxkb0DZvui';
	}
	if (empty($notificationName)) {
		$notificationName = 'CometChat';
	}

	if (isset($_REQUEST['chatroommode']) && isset($_REQUEST['displayname'])) {
		pushMobileNotification($_REQUEST['message'], $_REQUEST['displayname'], $_REQUEST['channel'], $_REQUEST['channel']);
	} else {
		if ($userid > 0 && isset($_REQUEST['displayname'])) {
			pushMobileNotification($_REQUEST['message'], $_REQUEST['displayname'], $_REQUEST['channel'], $userid);
		}
	}

	function pushMobileNotification($msg, $displayname, $channel, $senderid) {
            global $pushAPIKey;
            global $notificationName;
            global $cookiefile;

            $msg = checkEmoji($msg);

            if(CROSS_DOMAIN == 1) {
                    $host = parse_url(BASE_URL);
                    $hostname = $host['host'] ? $host['host'] : array_shift(explode('/', $host['path'], 2));
            } else {
                    $hostname = $_SERVER['HTTP_HOST'];
            }
            $hostname = str_replace('www.', '', $hostname);
            /********************************** SETUP **********************************/

            $key        = $pushAPIKey;
            $channel    = $hostname.''.$channel;
            $message    = $displayname.': '.$msg;
            $title      = $notificationName;
            $vibrate    = true;
            $sound      = 'default';
            $icon       = 'push_appicon_29da38bf54';
            $to_ids     = "everyone";
            $json       = '{"badge":"1","alert":"'. $message .'","title":"'. $title .'","vibrate":'.$vibrate.',"sound":"'.$sound.'","icon": "'.$icon.'", "id":"'.$senderid.'" ,"name":"'.$displayname.'", "type":"chatbox"}';

            if (!isset($_SESSION['cometchat']['pushNotificationSessionid'])) {
                loginPushNotification();
            }

            /********************************** SEND PUSH **********************************/

            $url = "https://api.cloud.appcelerator.com/v1/push_notification/notify.json?key=".$key;
            $params = "channel=".$channel."&payload=".$json."&to_ids=$to_ids";
            $response  = checkcURL(0,$url,$params,1,$cookiefile);
	}

	function loginPushNotification() {
		global $pushAPIKey;
		global $cookiefile;
		global $pushUsername;
		global $pushPassword;

		/********************************** LOGIN FOR PUSH **********************************/

		$url = 'https://api.cloud.appcelerator.com/v1/users/login.json?key='.$pushAPIKey;
		$params = "login=".$pushUsername."&password=".$pushPassword;
		$response  = checkcURL(0,$url,$params,1,$cookiefile);

		$response = json_decode($response);
		if (!isset($response->meta->message) && isset($response->meta->session_id)) {
			$_SESSION['cometchat']['pushNotificationSessionid'] = $response->meta->session_id;
		} else {
			registerPushUser();
		}
	}

	function registerPushUser() {
		global $pushAPIKey;
		global $cookiefile;
		global $pushUsername;
		global $pushPassword;

		/********************************** REGISTER NEW USER FOR PUSH **********************************/

		$url = 'https://api.cloud.appcelerator.com/v1/users/create.json?key='.$pushAPIKey;
		$params = "username=".$pushUsername."&first_name=".$pushUsername."&last_name=".$pushUsername."&password=".$pushPassword."&password_confirmation=".$pushPassword;
		$response  = checkcURL(0,$url,$params,1,$cookiefile);
		loginPushNotification();
	}

	function checkEmoji($msg) {
		global $customsmileyUnicode;

		foreach($customsmileyUnicode as $type => $value) {
			foreach($value as $key => $item) {
				if (strstr($msg, $key)) {
					$msg = str_replace($key, $item, $msg);
				}
			}
		}
		return $msg;
	}