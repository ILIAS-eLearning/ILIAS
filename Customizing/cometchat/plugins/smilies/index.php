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

$id = $_GET['id'];

$text = '';
$people_text = '';
$nature_text = '';
$objects_text = '';
$places_text = '';
$symbols_text = '';

$used = array();

$chatroommode = 0;
$broadcastmode = 0;
if (!empty($_GET['chatroommode'])) {
	$chatroommode = 1;
}
if (!empty($_GET['broadcastmode'])) {
	$broadcastmode = 1;
}
$embed = '';
$embedcss = '';
$close = "setTimeout('window.close()',2000);";
$before = 'window.opener';

if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$embedcss = 'embed';
	$close = "";
	$before = 'parent';

	if ($chatroommode == 1) {
		$before = "$('#cometchat_trayicon_chatrooms_iframe,#cometchat_container_chatrooms .cometchat_iframe,.cometchat_embed_chatrooms',parent.document)[0].contentWindow";
	}
	if ($broadcastmode == 1) {
		$before = "$('#cometchat_trayicon_chatrooms_iframe,#cometchat_container_chatrooms .cometchat_iframe,.cometchat_embed_chatrooms',parent.document)[0].contentWindow";
	}
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$embedcss = 'embed';
	$close = "";
	$before = 'parentSandboxBridge';
}

foreach ($smileys as $pattern => $result) {

	if (!empty($used[$result])) {
	} else {
		$pattern_class = str_replace("'","\\'",$pattern);
		$title = str_replace("-"," ",ucwords(preg_replace("/\.(.*)/","",$result)));
		$class = str_replace("-"," ",preg_replace("/\.(.*)/","",$result));
		if (in_array($result, $people)) {
			$people_text .= '<span class="cometchat_smiley '.$class.' people" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px;"></span>';
		} elseif (in_array($result, $nature)) {
			$nature_text .= '<span class="cometchat_smiley '.$class.' nature" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px;"></span>';
		} elseif (in_array($result, $objects)) {
			$objects_text .= '<span class="cometchat_smiley '.$class.' objects" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px;"></span>';
		} elseif (in_array($result, $places)) {
			$places_text .= '<span class="cometchat_smiley '.$class.' places" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px;"></span>';
		} elseif (in_array($result, $symbols)) {
			$symbols_text .= '<span class="cometchat_smiley '.$class.' symbols" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px;"></span>';
		} else {
			$text .= '<img class="cometchat_smiley" width="20" height="20" src="'.BASE_URL.'images/smileys/'.$result.'" title="'.$pattern.' ('.$title.')" to="'.$id.'" pattern="'.$pattern_class.'" chatroommode="'.$chatroommode.'" style="padding:2px">';
		}

		$used[$result] = 1;
	}
}
$hideadditional = '';
$tablength = "tab_length6";
$showadditional = '<div id="additional" class="tab tab_length6 "><h2>'.$smilies_language[7].'</h2></div>';
if(empty($text)){
	$tablength = "tab_length5";
	$showadditional = '';
}

$extrajs = "";
$scrollcss = "overflow-y:scroll;overflow-x:hidden;position:absolute;top:26px;";
if ($sleekScroller == 1) {
	$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
	$scrollcss = "";
}

$container_body_height = $smlHeight - 55;
$container_body_height_embed = $smlHeight - 35;
$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}
echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$smilies_language[0]}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=smilies&subtype=smilies&cc_theme={$cc_theme}" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		{$extrajs}
		<style>
			.container_body {
				height: {$container_body_height}px !important;
				{$scrollcss};
			}
			.container_body.embed {
				height: {$container_body_height_embed}px !important;
				{$scrollcss};
			}
		</style>
		<script type="text/javascript">
	    	$(function(){
	    		$('.tab').click(function(){
	    			$('.tab').removeClass('selected');
	    			$('.emojis').removeClass('emoji_selected');
	    			$(this).addClass('selected');
	    			$('.'+$(this).attr('id')).addClass('emoji_selected');
	    		});
				$('.cometchat_smiley').click(function(){
					var to = $(this).attr('to');
					var pattern = $(this).attr('pattern');
					var chatroommode = $(this).attr('chatroommode');
					var controlparameters = {"type":"plugins", "name":"ccsmilies", "method":"addtext", "params":{"to":to, "pattern":pattern, "chatroommode":chatroommode}};
					controlparameters = JSON.stringify(controlparameters);
					if(typeof(parent) != 'undefined' && parent != null && parent != self){
						parent.postMessage('CC^CONTROL_'+controlparameters,'*');
					} else {
						window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
					}
				});
				if (jQuery().slimScroll) {
					$(".container_body").slimScroll({ width: '100%'});
				}
			});
	    </script>
	</head>
	<body>
		<div class="container">
			<div class="container_title {$embedcss}">{$smilies_language[1]}</div>
			<div id="tabs">
			    <div id="people" class="tab {$tablength} selected"><h2>{$smilies_language[2]}</h2></div>
			    <div id="nature" class="tab {$tablength}"><h2>{$smilies_language[3]}</h2></div>
			    <div id="objects" class="tab {$tablength}"><h2>{$smilies_language[4]}</h2></div>
			    <div id="places" class="tab {$tablength}"><h2>{$smilies_language[5]}</h2></div>
			    <div id="symbols" class="tab {$tablength}"><h2>{$smilies_language[6]}</h2></div>
			    {$showadditional}
		    </div>
			<div class="container_body {$embedcss}">
				<div class="people emojis emoji_selected" id="emoji-people">{$people_text}</div>
				<div class="nature emojis" id="emoji-nature">{$nature_text}</div>
				<div class="objects emojis" id="emoji-objects">{$objects_text}</div>
				<div class="places emojis" id="emoji-places">{$places_text}</div>
				<div class="symbols emojis" id="emoji-symbols">{$symbols_text}</div>
				<div class="additional emojis" id="emoji-additional">{$text}</div>
			</div>
		</div>
	</body>
</html>
EOD;
