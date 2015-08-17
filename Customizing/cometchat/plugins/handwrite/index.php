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

if (empty($_GET['id'])) { exit; }

$toId = intval($_GET['id']);

if (!empty($_GET['chatroommode'])) {
	$toId = "c".$toId;
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

$toId .= ';'.$_REQUEST['basedata'].';'.$embed.';';

echo <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>{$handwrite_language[0]}</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="../../js.php?type=plugin&name=handwrite"></script>
<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=handwrite&subtype=handwrite" />
<script type="text/javascript">
        var tid = '{$toId}';
	if( {$lightboxWindows} == 0 ){
		window.onresize = function() { window.resizeTo('350','320' ); }
		window.load = function() { window.resizeTo('350','320' ); }
	}
        function isIE () {
            var myNav = navigator.userAgent.toLowerCase();
            return (myNav.indexOf('msie') != -1) ? true : false;
        }
</script>

</head>
<body>
     <div id="content">
         <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="100%" height="250" align="middle" id="main">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="handwriting.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="tid={$toId}" />
    <param name="scale" value="exactFit" />
    <embed src="handwriting.swf"
           width="100%"
           height="250"
           autostart="false"
           quality="high"
           bgcolor="#ffffff"
           FlashVars="tid={$toId}"
           name="main"
           align="middle"
           allowScriptAccess="sameDomain"
           type="application/x-shockwave-flash"
           pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</div>
<div id="sketch">
        <canvas id="paint"></canvas>
        <div class="color-select">
            <div val="white" class="color-opt white"></div>
            <div val="maroon" class="color-opt maroon"></div>
            <div val="steelblue" class="color-opt steelblue"></div>
            <div val="green" class="color-opt green"></div>
            <div val="gold" class="color-opt gold"></div>
            <div val="black" class="color-opt black"></div>
            <div val="blueviolet" class="color-opt blueviolet"></div>
            <div val="deepskyblue" class="color-opt deepskyblue"></div>
            <div val="chartreuse" class="color-opt chartreuse"></div>
            <div val="red" class="color-opt red"></div>
        </div>
        <div id="footer">
            <div class="pencil-btn"><img src="images/pencil.png"></div>
            <div class="eraser-btn"><img src="images/eraser.png"></div>
            <div class="width-btn" >
                <span>WIDTH</span>
                <span class="width-select onepx selected" val="1"></span>
                <span class="width-select twopx" val="2" ></span>
                <span class="width-select threepx" val="3" ></span>
                <span class="width-select fourpx" val="5" ></span>
            </div>
            <div class="color-btn" ><img src="images/pencil.png"></div>
            <div class="send-btn" onclick="javascript:send()"><span>send</span></div>
        </div>
</div>
<input id="sendername" type="hidden" name="sendername" value="{$sendername}">
</body>
<script type="text/javascript">
if(isIE()){
    $('#sketch').remove();
} else {
   $('#content').remove();
}
</script>
</html>
EOD;
