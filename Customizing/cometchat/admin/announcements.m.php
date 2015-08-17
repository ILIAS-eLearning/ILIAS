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
	<a href="?module=announcements&amp;ts={$ts}">Announcements</a>
	<a href="?module=announcements&amp;action=newannouncement&amp;ts={$ts}">Add new announcement</a>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;

	$sql = ("select id,announcement,time,`to` from cometchat_announcements where `to` = 0 or `to` = '-1'  order by id desc");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$announcementlist = '';

	while ($announcement = mysqli_fetch_assoc($query)) {
		$time = $announcement['time'];

		$announcementlist .= '<li class="ui-state-default"><span style="font-size:11px;word-wrap:break-word;margin-top:3px;margin-left:5px;">'.htmlspecialchars  ($announcement['announcement']).' (<span class="chat_time" timestamp="'.$time.'"></span>)</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="?module=announcements&amp;action=deleteannouncement&amp;data='.$announcement['id'].'&amp;ts={$ts}"><img src="images/remove.png" title="Delete Announcement"></a></span><div style="clear:both"></div></li>';
	}
        $errormessage = '';
        if(!$announcementlist){
            $errormessage = '<div id="no_module" style="width: 480px;float: left;color: #333333;">You do not have any announcements at the moment. To create a new announcement, click on \'Add new Announcement\'.</div>';
        }


	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Announcements</h2>
		<h3>Announcements are displayed live to your users. You can also add HTML code to your announcements, push advertisements in real-time.</h3>

		<div>
			<ul id="modules_announcements">
                        {$errormessage}
                        {$announcementlist}
			</ul>
			<div id="rightnav" style="margin-top:5px">
				<h1>Tips</h1>
				<ul id="modules_announcementtips">
					<li>When you add an announcement, it will be displayed live to all logged in online users. If there are more than one new announcements, the last announcement will be displayed.</li>

				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#modules_livemodules").sortable({ connectWith: 'ul' });
			$("#modules_livemodules").disableSelection();
		});
	</script>

EOD;

	template();

}

function deleteannouncement() {
    global $ts;
	if (!empty($_GET['data'])) {
		$sql = ("delete from cometchat_announcements where id = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($_GET['data']))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		removeCache('latest_announcement');
	}

	header("Location:?module=announcements&ts={$ts}");
}

function newannouncement() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=announcements&action=newannouncementprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>New announcement</h2>
		<h3>HTML code is allowed for announcements</h3>

		<div>
			<div id="centernav">
				<div class="titlefull">Announcement:</div>
				<div style="clear:both;padding:5px;"></div>
				<div style="clear:both;padding:5px;"><textarea name="announcement" rows=20 style="width:400px" required="true"></textarea></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title" style="width:170px">Show only to logged-in users?</div><div class="element"><input type="radio" name="sli" value="1" checked>Yes <input type="radio" name="sli" value="0" >No</div>
				<div style="clear:both;padding:5px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Your message will be shown live to all online users. Double check before proceeding.</li>
			<li>Users who have not logged-in will not be able to see the announcement in real-time.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add Announcement" class="button">&nbsp;&nbsp;or <a href="?module=announcements&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function newannouncementprocess() {
    global $ts;
	$announcement = $_POST['announcement'];
	$zero = '0';
	if ($_POST['sli'] == 0) {
		$zero = '-1';
	}
	$message = mysqli_real_escape_string($GLOBALS['dbh'],$announcement);
	$sent = mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp());
	$zero = mysqli_real_escape_string($GLOBALS['dbh'],$zero);
	$sql = ("insert into cometchat_announcements (announcement,time,`to`) values ('".$message."', '".$sent."','".$zero."')");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$insertedid = mysqli_insert_id($GLOBALS['dbh']);
	parsePusherAnn($zero,$sent,$message,1,$insertedid);

	removeCache('latest_announcement');
	header( "Location: ?module=announcements&ts={$ts}" );
}
