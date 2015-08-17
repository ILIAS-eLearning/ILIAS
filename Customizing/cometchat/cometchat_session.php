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

session_set_save_handler("cometchatSessionOpen", "cometchatSessionClose", "cometchatSessionRead", "cometchatSessionWrite", "cometchatSessionDestroy", "cometchatSessionGarbageCollector");

function cometchatSessionOpen($path, $name) {
    global $dbh;
    cometchatDBConnect();
    $sql = ("INSERT INTO cometchat_session(`session_id`,`session_data`) values('" . session_id() . "','') ON DUPLICATE KEY UPDATE session_lastaccesstime = NOW()");
    $query = mysqli_query($GLOBALS['dbh'],$sql);
}

function cometchatSessionClose() {
    $sessionId = session_id();
    //perform some action here
}

function cometchatSessionRead($sessionId) {
    global $dbh;
    cometchatDBConnect();
    $data = "";
    $sql = ("SELECT session_data FROM cometchat_session where session_id = '" . session_id() . "'");
    $query = mysqli_query($GLOBALS['dbh'],$sql);
    if($session = mysqli_fetch_assoc($query)){
        $data = $session['session_data'];
    }
    return $data;
}

function cometchatSessionWrite($sessionId, $data) {
    global $dbh;
    cometchatDBConnect();
    $sql = ("INSERT INTO cometchat_session SET session_id = '" . session_id() . "', session_data = '" . mysqli_real_escape_string($GLOBALS['dbh'],$data) . "' ON DUPLICATE KEY UPDATE session_data = '" . mysqli_real_escape_string($GLOBALS['dbh'],$data) . "'");
    $query = mysqli_query($GLOBALS['dbh'],$sql);
}

function cometchatSessionDestroy($sessionId) {
    global $dbh;
    cometchatDBConnect();
    $sql = ("DELETE FROM cometchat_session WHERE session_id = '" . session_id() . "'");
    $query = mysqli_query($GLOBALS['dbh'],$sql);
    setcookie(session_name(), "", time() - 3600);
}

function cometchatSessionGarbageCollector($lifetime) {
    global $dbh;
    cometchatDBConnect();
    $sql = ("DELETE FROM cometchat_session WHERE session_lastaccesstime < DATE_SUB(NOW(), INTERVAL " . mysqli_real_escape_string($GLOBALS['dbh'],$lifetime) . " SECOND)");
    $query = mysqli_query($GLOBALS['dbh'],$sql);
}

?>