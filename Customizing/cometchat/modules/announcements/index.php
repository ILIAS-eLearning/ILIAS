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
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

$extra = "";

if (!empty($userid)) {
	$extra = "or `to` = '0' or `to` = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'";
}

$limit = " limit ".$noOfAnnouncements;
if(!empty($_REQUEST['v3']))
{
	$limit = '';
}
$sql = ("select id,announcement,time,`to` from cometchat_announcements where `to` = '-1' ".$extra." order by id desc ".$limit);
$query = mysqli_query($GLOBALS['dbh'],$sql);

if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

$announcementdata = '';
$announcementJson = array();
while ($announcement = mysqli_fetch_assoc($query)) {
	$time = $announcement['time'];

	$class = 'highlight';

	if ($announcement['to'] == 0 || $announcement['to'] == -1) {
		$class = '';
	}
	$ann = array();

	$ann['id'] =  $announcement['id'];
	$ann['m'] =  $announcement['announcement'];
	$ann['t'] =  $announcement['time'];

	$announcementJson["_".$announcement['id']] = $ann;
	$announcementdata .= <<<EOD
		<li class="announcement"><span class="{$class}">{$announcement['announcement']}</span><br/><small class="chattime" timestamp="{$time}"></small></li>
EOD;
}

if (empty($announcementdata)) {
	$announcementdata = '<li class="announcement">'.$announcements_language[0].'</li>';
}

$extrajs = "";
if ($sleekScroller == 1) {
	$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
}

if(empty($_REQUEST['v3']))
{
echo <<<EOD
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="cache-control" content="no-cache">
			<meta http-equiv="pragma" content="no-cache">
			<meta http-equiv="expires" content="-1">
			<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
			<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=announcements" />
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
			<script src="../../js.php?type=module&name=announcements"></script>
			{$extrajs}
		</head>
		<body>
			<div style="width:100%;margin:0 auto;margin-top: 0px;height: 100%;overflow-y: auto;">
				<div class="container">
					<div class="announcements" style="width: 100%; height: 300px;overflow:auto">
						<ul>
							<ul>{$announcementdata}</ul>
						</ul>
					</div>
					<div style="clear:both">&nbsp;</div>
				</div>
			</div>
		</body>
	</html>
EOD;
} else{
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($announcementJson);
}
?>
