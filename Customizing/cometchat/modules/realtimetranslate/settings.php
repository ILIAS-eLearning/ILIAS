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

if (empty($_GET['process'])) {
	global $getstylesheet;
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

	$dy = '';
	$dn = '';

	$errorMsg = '';
	$innercontent = '"';

	if ($useGoogle == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	if(!checkcURL()) {
		$errorMsg = "<h2 id='errormsg' style='font-size: 11px; color: rgb(255, 0, 0);'>cURL extension is disabled on your server. Please contact your webhost to enable it. cURL is required for Translate Conversations.</h2>";
		$innercontent = ';display:none;"';
	}

echo <<<EOD
<!DOCTYPE html>

$getstylesheet
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=module&name=realtimetranslate&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>By default, we use Microsoft's Translate API to translate text in real-time. You can also use Google's Translate API. Please refer to our online documentation for information on how to setup this service.</h3>
		<div>
			{$errorMsg}
			<div id="centernav" style="width:380px {$innercontent}">
				<div class="title">Bing Client ID:</div><div class="element"><input type="text" class="inputbox" name="bingClientID" value="$bingClientID"></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Bing Client Secret:</div><div class="element"><input type="text" class="inputbox" name="bingClientSecret" value="$bingClientSecret"></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Use Google Translate API:</div><div class="element"><input type="radio" name="useGoogle" value="1" $dy>Yes <input type="radio" $dn name="useGoogle" value="0" >No</div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<h3 style="border-top: 1px solid #CCCCCC;margin-top:17px;padding-top:10px;">If you have selected Google Translate API, please add the Google Key below:</h3>
		<div>
			{$errorMsg}
			<div id="centernav" style="width:380px {$innercontent}">
				<div class="title">Google Key:</div><div class="element"><input type="text" class="inputbox" name="googleKey" value="$googleKey"></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
</div>
</form>
<script type="text/javascript" src="../js.php?admin=1"></script>
            <script type="text/javascript" language="javascript">
                $(function() {
					setTimeout(function(){
							resizeWindow();
						},200);
				});
				function resizeWindow() {
                    window.resizeTo(($("form").outerWidth(false)+window.outerWidth-$("form").outerWidth(false)), ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
                }
            </script>
EOD;
} else {

	$data = '';
	foreach ($_POST as $field => $value) {
		$data .= '$'.$field.' = \''.$value.'\';'."\r\n";
	}

	configeditor('SETTINGS',$data,0,dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
	header("Location:?module=dashboard&action=loadexternal&type=module&name=realtimetranslate");
}