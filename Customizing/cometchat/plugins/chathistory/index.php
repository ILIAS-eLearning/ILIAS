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
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."chathistory.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

if (defined('ERROR_LOGGING') && ERROR_LOGGING == '1') {
    error_reporting(E_ALL);
    ini_set('error_log', 'error.log');
    ini_set('log_errors', 'On');
}


if (empty($_GET['id']) && empty($_GET['history'])) { exit; }

$body = '';
	if(!empty($_REQUEST['chatroommode'])) {
            $body = <<<EOD
                <script type="text/javascript">getChatLog({$_REQUEST['history']}, {$_REQUEST['chatroommode']}, '{$_REQUEST['basedata']}');</script>
EOD;
    template();
	} else {
            $body = <<<EOD
                <script type="text/javascript">getChatLog({$_REQUEST['history']}, '', '{$_REQUEST['basedata']}');</script>
EOD;
	template();
	}

function template() {

    global $body;
    global $chathistory_language;
    $embed = '';
    $embedcss = '';

    if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
        $embed = 'web';
        $embedcss = 'embed';
    } elseif (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
        $embed = 'desktop';
        $embedcss = 'embed';
    }

    echo <<<EOD
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <title>{$chathistory_language[6]}</title>
            <link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=chathistory" />
            <script src="../../js.php?type=core&name=jquery" type="text/javascript"></script>
            <script src="../../js.php?type=core&name=scroll" type="text/javascript"></script>
            <script src="../../js.php?type=plugin&name=chathistory" type="text/javascript"></script>
            <script type="text/javascript"> var norecords = '{$chathistory_language[9]}';</script>
        </head>
        <body>
            <div class="container">
                <div class="container_title {$embedcss}" >{$chathistory_language[6]}</div>
                    <div class="container_body {$embedcss}">
                        <div class="container_body_chat">
                        {$body}
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
EOD;

exit;

}