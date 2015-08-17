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
		<a href="?module=extensions&amp;ts={$ts}">Live extensions</a>
	</div>
EOD;

function index() {
	global $body;
	global $extensions;
	global $navigation;
    global $ts;
    global $extensioninfo;


    $aextensions = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file) && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')) {
				$aextensions[] = $file;
			}
		}
		closedir($handle);
	}

	$extensionslist = '';
	$extensiondata = '';

	foreach ($aextensions as $extension) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR.'code.php');
        $titles[$extension] = $extensioninfo;
		$extensionhref = 'href="?module=extensions&amp;action=addextension&amp;data='.$extensioninfo[0].'&amp;ts='.$ts.'"';
		if (in_array($extension, $extensions)) {
			$extensionhref = 'href="javascript: void(0)" style="opacity: 0.5;cursor: default;"';
		}
		$extensiondata = '<span style="font-size:11px;float:right;margin-top:2px;margin-right:5px;"><a '.$extensionhref.' id="'.$extensioninfo[0].'">add</a></span>';
                if($extensioninfo[0] == 'mobileapp_title') {
                    $extensiondata = '';
                }

		$extensionslist .= '<li class="ui-state-default"><img src="../extensions/'.$extensioninfo[0].'/icon.png" style="margin:0;margin-right:5px;float:left;"></img><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;width:100px">'.$extensioninfo[1].'</span>'.$extensiondata.'<div style="clear:both"></div></li>';
	}

	$activeextensionsdata = '';
	$activeextensions = '';
	$no_extensions = '';
	$no = 0;

	foreach ($extensions as $ti) {

		$title = ucwords($ti);
                if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$ti.DIRECTORY_SEPARATOR.'settings.php')) {
			$activeextensionsdata = '<a href="javascript:void(0)" onclick="javascript:extensions_configextension(\''.$ti.'\')" style="margin-right:5px"><img src="images/config.png" title="Configure Extension"></a>';
		}
		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$ti.DIRECTORY_SEPARATOR.'code.php')) {
			$title = $titles[$ti][1];
		}

		++$no;
		if($title != 'Mobileapp'){
			$activeextensionsdata .= '<a href="javascript:void(0)" onclick="javascript:extensions_removeextension(\''.$no.'\')"><img src="images/remove.png" title="Remove Extension" rel="'.$extensioninfo[0].'"></a>';
        }

		$activeextensions .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'" rel="'.$ti.'"><img src="../extensions/'.$ti.'/icon.png" style="margin:0;margin-top:2px;margin-right:5px;float:left;"></img><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$activeextensionsdata.'</span><div style="clear:both"></div></li>';
	}

	if(!$activeextensions){
		$no_extensions .= '<div id="no_plugin" style="width: 480px;float: left;color: #333333;">You do not have any extensions activated at the moment. To activate a extension, please add the extension from the list of available extensions.</div>';
	}
	else{
		$activeextensions = '<ul id="modules_liveextensions">'.$activeextensions.'</ul>';
	}


	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Live Extensions</h2>
		<h3>Extensions add additional features to CometChat.</h3>

		<div>
			$no_extensions
			$activeextensions
			<div id="rightnav" style="margin-top:5px">
				<h1>Available extensions</h1>
				<ul id="modules_availableextensions">
				$extensionslist
				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function addextension() {
        global $ts;
	global $extensions;

	if (!empty($_GET['data'])) {

		$extensiondata = '$extensions = array(';

		foreach ($extensions as $extension) {
			$extensiondata .= "'$extension',";
		}

		$extensiondata .= "'{$_GET['data']}',";

		if($_GET['data'] === 'jabber'){
			$_SESSION['cometchat']['error'] = "You need to update the domain at www.cometchat.com/my otherwise Facebook/Gtalk won\'t work.";
		}

		$extensiondata = substr($extensiondata,0,-1).');';

		configeditor('EXTENSIONS',$extensiondata);
	}

	header("Location:?module=extensions&ts={$ts}");
}

function updateorder() {
	if (!empty($_POST['order'])) {

		$extensiondata = '$extensions = array(';

		$extensiondata .= $_POST['order'];

		$extensiondata = substr($extensiondata,0,-1).');';

		configeditor('EXTENSIONS',$extensiondata);
	} else {

		$extensiondata = '$extensions = array();';
		configeditor('EXTENSIONS',$extensiondata);

	}

	echo "1";

}
