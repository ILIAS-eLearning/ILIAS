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

$data = explode(';',$_REQUEST['tid']);
$_REQUEST['basedata'] = $data[1];

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."config.php");
/*Uncomment to enable push notifications for CometChat Legacy Apps*/
/*include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."sendnotification.php");*/
/*Uncomment to enable push notifications for CometChat Legacy Apps*/

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

$data = explode(';',$_REQUEST['tid']);
$_REQUEST['tid'] = $data[0];
$_REQUEST['embed'] = $data[2];

$randomImage = md5(rand(0,9999999999).time());

$file = fopen(dirname(__FILE__).DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$randomImage.".png","w");

if (!empty($_REQUEST['image'])) {
    $image = explode('data:image/png;base64,',$_REQUEST['image']);
    $png = base64_decode($image[1]);
    fwrite($file,$png);
    fclose($file);
} else {
    $inputSocket = fopen('php://input','rb');
    $png = stream_get_contents($inputSocket);
    fclose($inputSocket);
    fwrite($file,$png);
    fclose($file);
}

if(file_exists(dirname(dirname(dirname(__FILE__)))."/plugins/handwrite/uploads/".$randomImage.".png")){
    $linkToImage = BASE_URL."plugins/handwrite/uploads/".$randomImage.".png";
    $text = '<a href="'.$linkToImage.'" target="_blank" style="display:inline-block;margin-bottom:3px;margin-top:3px;max-width:100%;"><img class="cc_handwrite_image" src="'.$linkToImage.'" border="0" style="padding:0px;display: inline-block;border:1px solid #666;" height="90" width="134"></a>';
    if (substr($_REQUEST['tid'],0,1) == 'c') {
        $_REQUEST['tid'] = substr($_REQUEST['tid'],1);
        sendChatroomMessage($_REQUEST['tid'],$handwrite_language[3]."<br/>$text",0);
    } else {
        $response = sendMessage($_REQUEST['tid'],$handwrite_language[1]."<br/>$text",1);
        sendMessage($_REQUEST['tid'],$handwrite_language[2]."<br/>$text",2);
        /*Uncomment to enable push notifications for CometChat Legacy Apps*/
        /*if (isset($_REQUEST['sendername']) && $pushNotifications == 1) {
                pushMobileNotification($handwrite_language[2], $_REQUEST['sendername'], $_REQUEST['tid'], $_REQUEST['tid']);
        }*/
        /*Uncomment to enable push notifications for CometChat Legacy Apps*/
    }
}

$embed = '';
$embedcss = '';
$close = "setTimeout('window.close()',2000);";

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
    $embed = 'web';
    $embedcss = 'embed';
    $close = "
        var controlparameters = {'type':'plugins', 'name':'handwrite', 'method':'closeCCPopup', 'params':{}};
        controlparameters = JSON.stringify(controlparameters);
        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        } else {
            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        }";
}

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
    $embed = 'desktop';
    $embedcss = 'embed';
    $close = "parentSandboxBridge.closeCCPopup('handwrite');";
}
if(!empty($_REQUEST['other']) && $_REQUEST['other'] == 1){
    echo $close;
} else {
    echo <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>{$handwrite_language[0]} (closing)</title>
<script type="text/javascript">
    function closePopup(){
        var controlparameters = {'type':'plugins', 'name':'handwrite', 'method':'closeCCPopup', 'params':{}};
        controlparameters = JSON.stringify(controlparameters);
        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        } else {
            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        }
    }
</script>
</head>
<body onload="closePopup();">
</body>
</html>
EOD;
}
?>