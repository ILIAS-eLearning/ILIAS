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

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

if ($p_<2) exit;

$toId = $_GET['id'];

if (!empty($_COOKIE[$cookiePrefix."language"])) {
	$_GET['action'] = 'cached';
	$_GET['lang'] = $_COOKIE[$cookiePrefix."language"];
}

$chatroommode = 0;

if (!empty($_GET['chatroommode'])) {
	$chatroommode = 1;
}

$embed = '';
$embedcss = '';
$before = 'window.opener';

if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$embedcss = 'embed';
	$before = 'parent';

	if ($chatroommode == 1) {
		$before = "$('#cometchat_trayicon_chatrooms_iframe',parent.document)[0].contentWindow";
	}
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$embedcss = 'embed';
	$before = 'parentSandboxBridge';
}

$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}

if (empty($_GET['action'])) {

	$toId = $_GET['id'];
	$baseData = $_REQUEST['basedata'];
	echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
		<title>{$transliterate_language[0]}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=transliterate&cc_theme=$cc_theme" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<script type="text/javascript" src="//www.google.com/jsapi"></script>
		<script type="text/javascript">

		google.load("elements", "1", {
			packages: "transliteration"
		});

		function formatlang(str) {
			return str[0].toUpperCase()+str.substr(1).toLowerCase();
		}

		function onLoad() {
			var languages = google.elements.transliteration.getDestinationLanguages('en');
			html = '';
			for (x in languages) {
				if (languages[x] != 'ne') {
					html += '<li id="'+languages[x]+'">'+formatlang(x)+'</li>';
				}
			}
			$("#languages").html(html);

			$("li").click(function() {
				var info = $(this).attr('id');
				setCookie('{$cookiePrefix}language',info);
				location.href = 'index.php?cc_theme={$cc_theme}&action=transliterate&basedata={$baseData}&embed={$embed}&chatroommode={$chatroommode}&id={$toId}&lang='+info;
			});
		}

		function setCookie(cookie_name, cookie_value, cookie_life) {
			var today = new Date()
			var expiry = new Date(today.getTime() + cookie_life * 24*60*60*1000)
			if (cookie_value != null && cookie_value != ""){
				var cookie_string =cookie_name + "=" + escape(cookie_value)
				if(cookie_life){ cookie_string += "; expires=" + expiry.toGMTString()}
				cookie_string += "; path=/"
				document.cookie = cookie_string
			}
		}

		google.setOnLoadCallback(onLoad);
		</script>
	</head>
	<body>
		<div class="container">
			<div class="container_title {$embedcss}">{$transliterate_language[1]}</div>

			<div class="container_body {$embedcss}">

				<ul id="languages">Loading...</ul>
				<div style="clear:both"></div>
			</div>
		</div>
	</div>

	</body>
	<script>
	var controlparameters = {"type":"plugins", "name":"cctransliterate", "method":"setTitle", "params":{"lang":"{$transliterate_language[0]}"}};
	controlparameters = JSON.stringify(controlparameters);
	parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	</script>
	</html>
EOD;
} else {
	$toId = $_GET['id'];
	$lang = $_GET['lang'];
	$baseData = $_REQUEST['basedata'];

	$extra = '';
	if (!empty($_GET['chatroommode'])) {
		$decide = '#currentroom';
		$chatroommode = $_GET['chatroommode'];
	} else {
		$decide = '#cometchat_user_'.$toId.'_popup';
		$chatroommode = 0;
	}
	echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
		<title>{$transliterate_language[0]}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=transliterate&cc_theme=$cc_theme" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<script type="text/javascript" src="//www.google.com/jsapi"></script>
		<script type="text/javascript">

		google.load("elements", "1", {
			packages: "transliteration"
		});

		function formatlang(str) {
			return str[0].toUpperCase()+str.substr(1).toLowerCase();
		}

		function onLoad() {
			var options = {
				sourceLanguage: 'en',
				destinationLanguage: ['{$lang}'],
				shortcutKey: 'ctrl+g',
				transliterationEnabled: true
			};
			var control =
			new google.elements.transliteration.TransliterationControl(options);
			var ids = ["transliteratebox" ];
			control.makeTransliteratable(ids);

			$("#transliteratebox").keyup(function(event) {
				return chatboxKeydown(event);
			});

		}

		function pushcontents() {
			var data = document.getElementById('transliteratebox').value;
			document.getElementById('transliteratebox').value = '';
			var controlparameters = {"type":"plugins", "name":"cctransliterate", "method":"appendMessage", "params":{"to":"{$toId}", "data":data, "chatroommode": "{$chatroommode}"}};
			controlparameters = JSON.stringify(controlparameters);
			if(typeof(window.opener) == 'undefined' || window.opener == null){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			}else{
				window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
			}
			setTimeout('document.getElementById(\'transliteratebox\').focus()',100);
			setTimeout('document.getElementById(\'transliteratebox\').focus()',1000);
		}

		function changeLanguage() {
			setCookie('{$cookiePrefix}language','',0);
			location.href = 'index.php?cc_theme={$cc_theme}&id={$toId}&embed={$embed}&basedata={$baseData}&chatroommode={$chatroommode}';
		}

		function setCookie(cookie_name, cookie_value, cookie_life) {
			var today = new Date()
			var expiry = new Date(today.getTime() + cookie_life * 24*60*60*1000)
			var cookie_string =cookie_name + "=" + escape(cookie_value)
			if(cookie_life){ cookie_string += "; expires=" + expiry.toGMTString()}
			cookie_string += "; path=/"
			document.cookie = cookie_string
		}

		function chatboxKeydown(event) {
			if(event.keyCode == 13 && event.shiftKey == 0)  {
				pushcontents();

			}
		}

		google.setOnLoadCallback(onLoad);
		</script>
	</head>
	<body>
		<div class="container">
			<div class="container_title {$embedcss}">{$transliterate_language[2]}</div>

			<div class="container_body {$embedcss}">
				<textarea id="transliteratebox"></textarea><div style="clear:both"></div>
				<div>
					<div id="send">
						<input type="button" value="{$transliterate_language[3]}" onclick="javascript:pushcontents()" class="button">
					</div>
					<div id="change">
						<a href="javascript:void(0);" onclick="changeLanguage()">{$transliterate_language[4]}</a>
					</div>
					<div style="clear:both"></div>
				</div>
			</div>
		</div>
	</div>

	</body>
	<script>
	var languages = google.elements.transliteration.getDestinationLanguages('en');
	$.each(languages, function(key,value) {
		if(value == '{$lang}'){
			var formatLang = formatlang(key);
			var controlparameters = {"type":"plugins", "name":"cctransliterate", "method":"setTitle", "params":{"lang":"{$transliterate_language[0]}", "formatLang":formatLang}};
			controlparameters = JSON.stringify(controlparameters);
			parent.postMessage('CC^CONTROL_'+controlparameters,'*');
		}
	});
	</script>
	</html>
EOD;
}