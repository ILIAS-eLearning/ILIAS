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

$toId = $_GET['id'];
$baseData = $_REQUEST['basedata'];

$chatroommode = 0;

if (!empty($_GET['chatroommode'])) {
	$chatroommode = 1;
}

$sendername = $_REQUEST['sendername'];
$embed = '';
$embedcss = '';

if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$embedcss = 'embed';
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$embedcss = 'embed';
}

$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}

echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{$filetransfer_language[0]}</title>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=filetransfer&cc_theme={$cc_theme}" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<script type="text/javascript" src="../../js.php?type=plugin&name=filetransfer"></script>
		<script type="text/javascript">
		function disableButton() {
			document.getElementById('uploadfile').setAttribute('disabled','true');
		}
		</script>
	</head>
	<body>
		<form name="upload" action="upload.php?embed={$embed}" method="post" enctype="multipart/form-data">
			<div class="container">
				<div class="container_title {$embedcss}">{$filetransfer_language[1]}</div>
				<div class="container_body {$embedcss}">
					<div class="container_body_1">{$filetransfer_language[2]}</div>
					<div id="select-0" class="container_body_2">
						<label class="cabinet">
							<input id='uploadfile' type="file" class="file" name="Filedata" onchange="javascript:document.upload.submit();disableButton();" />
						</label>
					</div>
					<div class="container_body_3 {$embedcss}">{$filetransfer_language[4]}</div>
					<div style="clear:both"></div>
					<div class="container_body_4">{$filetransfer_language[3]}</div>
					<input type="hidden" name="to" value="{$toId}">
					<input type="hidden" name="basedata" value="{$baseData}">
					<input type="hidden" name="chatroommode" value="{$chatroommode}">
					<input type="hidden" name="sendername" value="{$sendername}">
				</div>
			</div>
			<script>SI.Files.stylizeAll();</script>
			<script type='text/javascript'>
				if(typeof $ != 'undefined')
				$(document).ready(function(){
					var width = ($("form").outerWidth(false)+window.outerWidth-$("form").outerWidth(false));
					var height = ($('form').outerHeight(false)+window.outerHeight-window.innerHeight)+10;//margin-top+margin-bottom
					window.resizeTo(width,height);

					if(typeof(parent) != 'undefined'){
						var controlparameters = {'type':'plugin', 'name':'filetransfer', 'method':'resizeCCPopup', 'params':{"id":"loadChatroomPro", "height":height, "width":width}};
	                	controlparameters = JSON.stringify(controlparameters);
	                	if(typeof(window.opener) == null){
	                		window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
	                	}else{
	                		parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	                	}
					}
					//Height 80 = container_body.height(50) + embed.padding(10*2) + container.margin(5*2)

				});
			</script>
		</form>
	</body>
	</html>
EOD;
