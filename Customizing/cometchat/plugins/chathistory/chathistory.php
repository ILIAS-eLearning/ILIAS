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

if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

$history = intval($_REQUEST['history']);
function logs() {
    $usertable = TABLE_PREFIX.DB_USERTABLE;
    $usertable_username = DB_USERTABLE_NAME;
    $usertable_userid = DB_USERTABLE_USERID;
    global $history;
    global $userid;
    global $chathistory_language;
    global $guestsMode;
    global $guestnamePrefix;
    global $response;

    if (!empty($_REQUEST['history'])) {
        $currentroom = $_REQUEST['history'];
    }
    $guestpart = "";
    if (!empty($_REQUEST['chatroommode'])) {
        if ($guestsMode == '1') {
            $guestpart = " union (select m1.*, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',f.name) fromu, from_unixtime(m1.sent,'%y,%m,%d') from cometchat_chatroommessages m1, cometchat_guests f where f.id = m1.userid and m1.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."' and m1.message NOT LIKE 'CC^CONTROL_deletemessage_%' group by date_format(from_unixtime(sent),'%y,%m,%d') desc) order by id";
            }
            $sql = ("select * from ((select m1.*, f.".$usertable_username." fromu, from_unixtime(m1.sent,'%y,%m,%d') from cometchat_chatroommessages m1, ".$usertable." f where f.".$usertable_userid." = m1.userid and m1.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."' and m1.message not like '%banned%' and m1.message not like '%kicked%' and m1.message not like '%deletemessage%' group by date_format(from_unixtime(sent),'%y,%m,%d') desc) ".$guestpart.") as t group by date_format(from_unixtime(sent),'%y,%m,%d') desc");
            } else {
                if ($guestsMode == '1') {
                    $guestpart = " union (select * from ((select m1.*, concat('".$guestnamePrefix."',f.name) fromu, concat('".$guestnamePrefix."',t.name) tou, from_unixtime(m1.sent,'%y,%m,%d')from cometchat m1, cometchat_guests f, cometchat_guests t where  f.id = m1.from and t.id = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')))
			union (select m1.*, concat('".$guestnamePrefix."',f.name) fromu, t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." tou, from_unixtime(m1.sent,'%y,%m,%d') from cometchat m1, cometchat_guests f, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." t where  f.id = m1.from and t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')))
			union (select m1.*, f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." fromu, concat('".$guestnamePrefix."',t.name) tou, from_unixtime(m1.sent,'%y,%m,%d') from cometchat m1, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f, cometchat_guests t where  f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.from and t.id = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."'))) order by id) as t group by date_format(from_unixtime(sent),'%y,%m,%d') desc)";
		}
                $sql = ("(select m1.*,  f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." fromu, t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." tou, from_unixtime(m1.sent,'%y,%m,%d') from `cometchat` m1, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." t where f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.from and t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')) group by date_format(from_unixtime(sent),'%y,%m,%d') desc) ".$guestpart." ");
            }
            $query = mysqli_query($GLOBALS['dbh'],$sql);
            $previd = 1000000;
            if (mysqli_num_rows($query)>0) {
		 while ($chat = mysqli_fetch_assoc($query)) {
                     if (function_exists('processName')) {
                         $chat['fromu'] = processName($chat['fromu']);
                         if (empty($_REQUEST['chatroommode'])) {
                             $chat['tou'] = processName($chat['tou']);
                             }
                    }
                    if (empty($_REQUEST['chatroommode'])) {

                        if ($chat['from'] == $userid) {
                            $chat['fromu'] = $chathistory_language[1];
                        }
                        } else {
                            if ($chat['userid'] == $userid) {
                                $chat['fromu'] = $chathistory_language[1];
                            }
                        }
                        if((strpos($chat['message'],'CC^CONTROL_')) !== false){
                            $controlparameters = str_replace('CC^CONTROL_', '', $chat['message']);
                            if((strpos($controlparameters,'deletemessage_')) <= -1){
                                $chatmsg = $chat['message'];
    			             }
                        }else{
                            $chatmsg = $chat['message'];
                        }
			if ($chat['id'] == $previd) {
                            $previd = 1000000;
			}
            $read = 0;
            if(empty($chat['read'])){
                $read = 1;
            } else {
                $read = $chat['read'];
            }
			$response[] = array('id' => $chat['id'], 'previd' => $previd, 'from' => $chat['fromu'], 'message' => $chatmsg, 'sent' =>  $chat['sent']*1000, 'old' => $read);
                        $previd = $chat['id'];
                }
                echo json_encode($response); exit;
        } else {
            echo '0'; exit;
        }
}

function logview() {
    $usertable = TABLE_PREFIX.DB_USERTABLE;
    $usertable_username = DB_USERTABLE_NAME;
    $usertable_userid = DB_USERTABLE_USERID;
    global $history;
    global $userid;
    global $chathistory_language;
    global $guestsMode;
    global $guestnamePrefix;
    global $limit;
    global $response;
    $requester = '';
    $limit = 13;
    $preuserid = 0;

	if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

    if (!empty($_REQUEST['range'])) {
        $range = $_REQUEST['range'];
    }

    if (!empty($_REQUEST['history'])) {
        $history = $_REQUEST['history'];
    }

    $range = intval($range);

    if (!empty($_REQUEST['lastidfrom'])) {
        $lastidfrom = $_REQUEST['lastidfrom'];
    }
    $guestpart= "";
    if (!empty($_REQUEST['chatroommode'])) {
        if ($guestsMode == '1') {
            $guestpart = "union (select m1.*, m2.name chatroom, concat('".$guestnamePrefix."',f.name) fromu from cometchat_chatroommessages m1, cometchat_chatrooms m2, cometchat_guests f where  f.id = m1.userid and m1.chatroomid=m2.id and m1.chatroomid=".mysqli_real_escape_string($GLOBALS['dbh'],$history)." m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.message not like 'CC^CONTROL_deletemessage_%')";
        }
        $sql = ("(select m1.*, m2.name chatroom, f.".$usertable_username." fromu from cometchat_chatroommessages m1, cometchat_chatrooms m2, ".$usertable." f where  f.".$usertable_userid." = m1.userid and m1.chatroomid=m2.id and m1.chatroomid='".$history."' and m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.message not like '%banned%' and m1.message not like '%kicked%' and m1.message not like '%deletemessage%') ".$guestpart." order by id limit ".$limit."");

    } else {
        if ($guestsMode == '1') {
            $guestpart = "union (select m1.*, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',f.name) fromu, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',t.name) tou from cometchat m1, cometchat_guests f, cometchat_guests t where f.id = m1.from and t.id = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')) and m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.direction <> 2) union (select m1.*, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',f.name) fromu, t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." tou from cometchat m1, cometchat_guests f, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." t where f.id = m1.from and t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')) and m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.direction <> 2) union (select m1.*, f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." fromu, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',t.name) tou from cometchat m1, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f, cometchat_guests t where f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.from and t.id = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')) and m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.direction <> 2)";
        }
        $sql = ("(select m1.*, f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." fromu, t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." tou from cometchat m1, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." t  where  f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.from and t.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.to and ((m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."') or (m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$history)."')) and m1.id >= ".mysqli_real_escape_string($GLOBALS['dbh'],$range)." and m1.direction <> 2) ".$guestpart." order by id limit ".mysqli_real_escape_string($GLOBALS['dbh'],$limit)."");
    }
    $query = mysqli_query($GLOBALS['dbh'],$sql);
    $previd = '';
    $lines = 0;
    $s = 0;
	if (mysqli_num_rows($query)>0) {
		while ($chat = mysqli_fetch_assoc($query)) {
			if (function_exists('processName')) {
				$chat['fromu'] = processName($chat['fromu']);
				if (empty($_REQUEST['chatroommode'])) {
					$chat['tou'] = processName($chat['tou']);
				}
			}
			if ($s == 0) {
                            $s = $chat['sent'];
			}
			$requester = $chat['fromu'];
                        if (!empty($_REQUEST['chatroommode'])) {
                            $chathistory_language[2]=$chathistory_language[7];
                            $requester=$chat['chatroom'];
                            if ($chat['userid']==$userid) {
                                $chat['fromu'] = $chathistory_language[1];
                            }
                            if($chat['userid'] == $preuserid) {
                                $chat['fromu']= '';
                            }
                            $preuserid = $chat['userid'];
			} else {
                            if ($chat['from'] == $userid) {
                                    $chat['fromu'] = $chathistory_language[1];
                            }
			}
            if((strpos($chat['message'],'CC^CONTROL_')) !== false){
                $controlparameters = str_replace('CC^CONTROL_', '', $chat['message']);
                if((strpos($controlparameters,'deletemessage_')) <= -1){
                    $chatmes = $chat['message'];
                 }
            }else{
                $chatmes = $chat['message'];
            }
                        if (!empty($_REQUEST['chatroommode'])) {
                            if (!empty($_REQUEST['lastidfrom']) && $lastidfrom == $chat['userid']) {
                                $chat['fromu'] = '';
                            }
			} else	{
                            if (!empty($_REQUEST['lastidfrom']) && $lastidfrom == $chat['from']) {
                                $chat['fromu'] = '';
                            }
			}
			$lines++;
                        $previd = 1000000;
			if (!empty($chat['userid'])) {
                            $lastidfrom = $chat['userid'];
			} else if(!empty($chat['from'])) {
                            $lastidfrom = $chat['from'];
			}
            $read = 0;
            if(empty($chat['read'])){
                $read = 1;
            } else {
                $read = $chat['read'];
            }
		$response['_'.$chat['id']] = array('id' => $chat['id'], 'previd' => $previd, 'from' => $chat['fromu'], 'requester' => $requester, 'message' => $chatmes, 'sent' =>  $chat['sent']*1000, 'userid' => $lastidfrom, 'old' => $read);
	}
        echo json_encode($response);
        exit;
        } else {
            echo '0'; exit;

        }
}
$allowedActions = array('logs','logview');

if (!empty($_GET['action']) && in_array($_GET['action'],$allowedActions)) {
    call_user_func($_GET['action']);
}
?>