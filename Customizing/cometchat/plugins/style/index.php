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

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

$id = $_GET['id'];

$text = '';

$styles = explode(',',$styleOptions);

$embed = '';
$close = "setTimeout('window.close()',100);";
$before = 'window.opener';

if (!empty($_GET['embed'])) {
	$before = "$('#cometchat_trayicon_chatrooms_iframe,#cometchat_container_chatrooms .cometchat_iframe,.cometchat_embed_chatrooms',parent.document)[0].contentWindow";
	$embed = 'embed'; $close = "parent.closeCCPopup('style');";
}

foreach ($styles as $style) {
	$text .= '<span class="setStyle" pattern="'.$style.'" style="margin:2px;background-color:#'.$style.';width:20px;height:20px;display:block;float:left;"></span>';
}

$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}

echo <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>{$style_language[0]}</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script>
	$(document).ready(function(){
		$('.setStyle').live('click',function(){
			var pattern = $(this).attr('pattern');
			var controlparameters = {"type":"plugins", "name":"ccstyle", "method":"updatecolor", "params":{"pattern":pattern,"chatroommode":"1"}};
			controlparameters = JSON.stringify(controlparameters);
			if(typeof(parent) != 'undefined' && parent != null && parent != self){
				if(typeof(parent) != 'undefined' && parent != null && parent != self){
					parent.postMessage('CC^CONTROL_'+controlparameters,'*');
					var controlparameters = {'type':'plugins', 'name':'style', 'method':'closeCCPopup', 'params':{}};
                    controlparameters = JSON.stringify(controlparameters);
                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				} else {
					window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
					window.close();
				}
			} else {
				if(window.top == window.self){
					window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
					window.close();
				}else{
					var controlparameters = {"pattern":pattern};
		            $.ccstyle.updatecolor(controlparameters);
		            closeCCPopup('style');
				}
			}
		});
	});
</script>
<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=style&cc_theme={$cc_theme}" />
</head>
<body>
<div class="container">
<div class="container_title {$embed}">{$style_language[1]}</div>

<div class="container_body {$embed}">
$text
<div style="clear:both"></div>
</div>
</div>
</div>

</body>
</html>
EOD;

?>