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

$domain = '';
if (!empty($_GET['basedomain'])) {
	$domain = $_GET['basedomain'];
}

$embed = '';
$embedcss = '';
$close = 'window.close();';
$before = 'window.opener';
$before2 = 'window.top';

if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$before = 'parent';
	$before2 = 'parent';
	$embedcss = 'embed';
	$close = "parent.closeCCPopup('jabber');";
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$before = 'parentSandboxBridge';
	$before2 = 'parentSandboxBridge';
	$embedcss = 'embed';
	$close = "parentSandboxBridge.closeCCPopup('jabber');";
}

if (!empty($_GET['session'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('session','sessiondata');
	</script>
EOD;
	exit;
}
if (!empty($_GET['sessiongtalk'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('sessiongtalk','sessiondatagtalk');
	</script>
EOD;
	exit;
}
if (!empty($_GET['error'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('error','Denied');
	</script>
EOD;
	exit;
}
if (!empty($_GET['Denied'])) {
	echo <<<EOD
	<script>
	{$close}
	</script>
EOD;
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>
			<?php echo $jabber_language[0];?><?php echo $jabber_language[16];?>
			<?php echo $jabber_language[15];?>
		</title>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=extension&name=jabber" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script src="../../js.php?type=extension&name=jabber"></script>
		<script>
			var before = "<?php echo $before;?>";
			var before2 = "<?php echo $before2;?>";
			var close = "<?php echo $close;?>";
			var domain = "<?php echo $domain;?>";
		</script>
	</head>
	<body>
		<form name="upload" onsubmit="return login();">
			<div class="container">
				<div class="container_title <?php echo $embedcss;?>"><?php echo $jabber_language[1];?></div>
				<div class="container_body <?php echo $embedcss;?>">
				<?php
					if(empty($_GET['sessiondata']) && empty($_GET['sessiondatagtalk']) ):
				?>
					<div style="margin: 0px auto;width: 149px;">
						<script>
							String.prototype.replaceAll=function(s1, s2) {return this.split(s1).join(s2)};
							var currenttime = new Date();
							currenttime = parseInt(currenttime.getTime());
							document.write('<iframe src="<?php echo $cometchatServer;?>gtalk.jsp?cometserver=<?php echo $cometchatServer; ?>&time='+currenttime+'&id=<?php echo $gtalkAppId;?>&r='+location.href.replaceAll('&','AND').replaceAll('?','QUESTION')+'" frameborder="0" border="0" width="149" height="22"></iframe>');
						</script>
					</div>
				<?php
					else:
						if(isset($_GET['sessiondatagtalk'])):
					?>
					<div class="container_body_1">
						<span><?php echo $jabber_language[7];?></span>
					</div>
					<script>
						$(function() {
							login_gtalk('<?php echo $_GET["sessiondatagtalk"];?>','<?php echo $_GET["username"];?>');
						});
					</script>
				<?php
						endif;
					endif;
				?>
					<div style="clear:both"></div>
				</div>
			</div>
		</form>
	</body>
</html>