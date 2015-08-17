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

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");

$colorslist = '';
if ($handle = opendir(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'colors')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != "index.html" && $file != "synergy.php" && $file != "synergy.bak" && file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'cometchat.css') && $file != $color.'.php') {
            $listedcolor = stristr($file,".",true);
            $colorname = ucfirst($listedcolor);
            $colorslist .=  <<<EOD
                    <a href="javascript:void(0);" onclick="javascript:changeTheme('{$listedcolor}')">{$colorname}</a><br/>
EOD;
        }
    }
    closedir($handle);
}

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

$currenttheme = ucfirst($color);

$themesoptions = '';
if(!empty($colorslist)) {
	$themesoptions = "<b>{$themechanger_language[1]}</b><br/><br/>{$colorslist}";
} else {
	$themesoptions = "<b>{$themechanger_language[2]}</b>";
}

$extrajs = "";
if ($sleekScroller == 1) {
	$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
}
echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=themechanger" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
                 {$extrajs}
		<script>
                        $(function() {

				if (jQuery().slimScroll) {
					$('.container').slimScroll({height: '100px',allowPageScroll: false});
					$(".container").css("height","90px");
				}
			});
			function changeTheme(name) {
				set_cookie('color',name);
				if (typeof(parent)!= 'undefined') {
					var controlparameters = {"type":"modules", "name":"themechanger", "method":"closeModule", "params":{}};
					controlparameters = JSON.stringify(controlparameters);
					parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				}else if(typeof(window.opener)!= 'undefined') {
					window.opener.location.reload();
					window.close();
				}
			}

			function set_cookie(name,value) {
				var today = new Date();
				today.setTime( today.getTime() );
				expires = 1000 * 60 * 60 * 24;
				var expires_date = new Date( today.getTime() + (expires) );
				document.cookie = "{$cookiePrefix}" + name + "=" +escape( value ) + ";path=/" + ";expires=" + expires_date.toGMTString();
			}

		</script>

	</head>
	<body>
		<div class="container">
			{$themechanger_language[0]} <b>$currenttheme</b><br/><br/>

			{$themesoptions}
		</div>
	</body>
</html>
EOD;
?>