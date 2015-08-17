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

define('CC_CRON', '1');

if (!empty($_REQUEST['url'])) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."modules.php");
}

$auth = md5(md5(ADMIN_USER).md5(ADMIN_PASS));

if (!empty($_REQUEST['auth']) && !empty($auth) && $_REQUEST['auth'] == $auth ) {
	if ((!empty($_REQUEST['cron']['type']) && $_REQUEST['cron']['type'] == "all") || !empty($_REQUEST['cron']['core'])) {
		clearMessageEntries();
		clearGuestEntries();
	} else {
		if (!empty($_REQUEST['cron']['messages'])) {
			clearMessageEntries();
		}
		if (!empty($_REQUEST['cron']['guest'])) {
			clearGuestEntries();
		}
	}
	clearModulesData();
	clearPluginsData();
} else {
	echo 'Sorry you don`t have permissions to execute cron.';
}

function clearModulesData() {
	global $trayicon;
	global $chatroomTimeout;
	foreach ($trayicon as $t) {
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$t[0].DIRECTORY_SEPARATOR.'cron.php')) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$t[0].DIRECTORY_SEPARATOR.'cron.php');
		}
	}
}

function clearPluginsData() {
	global $plugins;
	foreach ($plugins as $p) {
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$p.DIRECTORY_SEPARATOR.'cron.php') && (!empty($_REQUEST['cron'][$p]) || (!empty($_REQUEST['cron']['plugins'])) || (!empty($_REQUEST['cron']['type']) && $_REQUEST['cron']['type'] == "all"))) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$p.DIRECTORY_SEPARATOR.'cron.php');
		}
	}
}

function clearMessageEntries() {
	$sql = ("delete from cometchat where (cometchat.read = 1 and (".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-cometchat.sent)>10800) OR ((".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-cometchat.sent)>604800)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$sql = ("delete from cometchat_comethistory where ((".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-cometchat_comethistory.sent)>10800)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
}

function clearGuestEntries() {
	$sql = ("delete from cometchat_guests where id in (select userid from cometchat_status where (".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."-cometchat_status.lastactivity)>10800)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
}