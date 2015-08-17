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
		<a href="?module=language&amp;ts={$ts}">Languages</a>
		<a href="?module=language&amp;action=additionallanguages&amp;ts={$ts}">Additional languages</a>
		<a href="?module=language&amp;action=createlanguage&amp;ts={$ts}">Create new language</a>
		<a href="?module=language&amp;action=uploadlanguage&amp;ts={$ts}">Upload language</a>
	</div>
EOD;

function index() {
	global $body;
	global $languages;
	global $navigation;
	global $lang;
        global $ts;

	$alanguages = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$file) && strtolower(extension($file)) == 'php') {
				$alanguages[] = substr($file,0,-4);
			}
		}
		closedir($handle);
	}

	$languages = '';
	$no = 0;
	$activelanguages = '';

	foreach ($alanguages as $ti) {

		$default = '';
		$opacity = '';
		$titlemakedefault = 'title="Make language default"';
		$setdefault = 'onclick="javascript:language_makedefault(\''.$ti.'\')"';

		if (strtolower($lang) == strtolower($ti)) {
			$default = ' (Default)';
			$opacity = '0.5;cursor:default;';
			$titlemakedefault = '';
			$setdefault = '';
		}

		++$no;

		$activelanguages .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.$ti.$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="javascript:void(0)" '.$setdefault.' style="margin-right:5px;"><img src="images/default.png" '.$titlemakedefault.' style="opacity:'.$opacity.';"></a><a href="?module=language&amp;action=editlanguage&amp;data='.$ti.'&amp;ts='.$ts.'" style="margin-right:5px"><img src="images/config.png" title="Edit Language"></a><a href="?module=language&amp;action=exportlanguage&amp;data='.$ti.'&amp;ts='.$ts.'" target="_blank" style="margin-right:5px;"><img src="images/export.png" title="Download Language"></a><a href="javascript:void(0)" onclick="javascript:language_removelanguage(\''.$ti.'\')"><img src="images/remove.png" title="Remove Language"></a></span><div style="clear:both"></div></li>';
	}

	$body = <<<EOD
	$navigation
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Languages</h2>
		<h3>To set the language, click on the star button next to the language.</h3>

		<div>
			<div id="centernav">
					<div>
						<ul id="modules_livelanguage">
							$activelanguages
						</ul>
					</div>
				</div>
			</div>
		</div>


	<div style="clear:both"></div>
EOD;

	template();

}

function makedefault() {

	if (!empty($_POST['lang'])) {
		$data = '$lang = \''.$_POST['lang'].'\';';

		configeditor('LANGUAGE',$data,0);
	}

	$_SESSION['cometchat']['error'] = 'Language details updated successfully';

	echo "1";

}

function removelanguageprocess() {
        global $ts;
	$lang = $_GET['data'];

	if ($lang != 'en') {
		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file) && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
					unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
				}
			}
			closedir($handle);
		}

		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file) && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
					unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
				}
			}
			closedir($handle);
		}

		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file) && (is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')||$file =='mobileapp'||$file == 'desktop') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
					unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
				}
			}
			closedir($handle);
		}

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
			unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
		}

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'i'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
			unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'i'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
		}

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'m'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
			unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'m'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
		}

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'desktop'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
			unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'desktop'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
		}

		$_SESSION['cometchat']['error'] = 'Language deleted successfully';
	} else {
		$_SESSION['cometchat']['error'] = 'Sorry, this language cannot be deleted.';
	}

	header("Location:?module=language&ts={$ts}");


}

function editlanguage() {
	global $body;
	global $navigation;
        global $rtl;
	global $language;

	$lang = $_GET['data'];

	$filestoedit = array ( "" => "", "i" => "i", "m" => "m", "desktop" => "desktop" );

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file) && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit["modules/".$file] = $file;
			}
		}
		closedir($handle);
	}

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file) && is_file(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit["plugins/".$file] = $file;
			}
		}
		closedir($handle);
	}


	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file) &&  file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit["extensions/".$file] = $file;
			}
		}
		closedir($handle);
	}

	$data = '';

	foreach ($filestoedit as $name => $file) {

		if (empty($name)) {
			$namews = $name;
		} else {
			$namews = $name.'/';
		}

		if (file_exists((dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
			if ($name == '') {
				$data .= '<h4 onclick="javascript:$(\'#'.md5($name).'\').slideToggle(\'slow\')">core</h4>';
			} else {
				$data .= '<div style="clear:both"></div><h4 onclick="javascript:$(\'#'.md5($name).'\').slideToggle(\'slow\')">'.$name.'</h4>';
			}

			$data .= '<div id="'.md5($name).'" style="display:none"><form>';

			include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.'en.php');

			if (file_exists((dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {
				include(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.$lang.'.php');
			}

			if (!empty($file)) {
				$file .= '_';
			}

			$array = $file.'language';

			$x = 0;

			if ($name == '') {

				$rtly = "";
				$rtln = "";

				if ($rtl == 1) {
					$rtly = "checked";
				} else {
					$rtln = "checked";
				}

				$data .= '<div class="title">Right to left text:</div><div class="element"><input type="radio" id="rtl" name="rtl" value="1" '.$rtly.'>Yes <input id="rtl" type="radio" '.$rtln.' name="rtl" value="0" >No</div><div style="clear:both;padding:7.5px;"></div>';
			}

			foreach (${$array} as $i => $l) {
				$x++;
				$data .= '<div style="clear:both"></div><div class="title">'.$x.':</div><div class="element"><textarea name="lang_'.$i.'" class="inputbox inputboxlong">'.(stripslashes($l)).'</textarea></div>';
			}

			$data .= '<div style="clear:both;padding:7.5px;"></div><div style="float:right;margin-right:20px;"><input type="button" value="Update language" onclick="language_updatelanguage(\''.md5($name).'\',\''.$name.'\',\''.$file.'\',\''.$lang.'\')" class="button">&nbsp;&nbsp;or <a onclick="language_restorelanguage(\''.md5($name).'\',\''.$name.'\',\''.$file.'\',\''.$lang.'\')" href="#">restore</a></div><div style="clear:both;padding:7.5px;"></div></form></div>';

		}
	}

	$body = <<<EOD
	$navigation
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Edit language - {$lang}</h2>
		<h3>Please select the section you would like to edit.</h3>
		<div>
			<div id="centernav" class="centernavextend">
				$data
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function editlanguageprocess() {

	$lang = $_POST['lang'];

	$data = '<?php'."\r\n"."\r\n".'/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////'."\r\n"."\r\n".'/* LANGUAGE */'."\r\n"."\r\n";

	if (isset($_POST['rtl']) && empty($_POST['id'])) {
		$data .= "\$rtl = '".$_POST['rtl']."';\r\n";
	}

	foreach ($_POST['language'] as $i => $l) {
		$data .= '$'.$_POST['file'].'language['.str_replace('lang_','',$i).'] = \''.(str_replace("'", "\'",$l)).'\';'."\r\n";
	}

	$data .= "\r\n".'/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////';

	if (!empty($_POST['id'])) {
		$_POST['id'] .= '/';
	}

	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_POST['id'].'lang/'.strtolower($lang).".php";
	$fh = fopen($file, 'w');
	if (fwrite($fh, $data) === FALSE) {
			echo "Cannot write to file ($file)";
			exit;
	}
	fclose($fh);
	chmod($file, 0777);
	if ($handle = opendir(dirname(dirname(__FILE__)).'/cache/')) {
		while (false !== ($file = readdir($handle))) {

			if ($file != "." && $file != ".." && $file != "index.html") {
				unlink(dirname(dirname(__FILE__)).'/cache/'.$file);
			}
		}
	}

	echo "1";
	exit;
}

function restorelanguageprocess() {

	$lang = $_POST['lang'];

	if (!empty($_POST['id'])) {
		$_POST['id'] .= '/';
	}

	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_POST['id'].'lang'.DIRECTORY_SEPARATOR.'en.bak';
	$fh = fopen($file, 'r');
	$restoredata = fread($fh, filesize($file));
	fclose($fh);

	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_POST['id'].'lang'.DIRECTORY_SEPARATOR.strtolower($lang).".php";
	$fh = fopen($file, 'w');
	if (fwrite($fh, $restoredata) === FALSE) {
			echo "Cannot write to file ($file)";
			exit;
	}
	fclose($fh);
	chmod($file, 0777);

	$_SESSION['cometchat']['error'] = 'Language has been restored successfully.';

	echo "1";
	exit;

}

function createlanguage() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=language&action=createlanguageprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Create new language</h2>
		<h3>Enter the first two letters of your new language.</h3>
		<div>
			<div id="centernav">
				<div class="title">Language:</div><div class="element"><input type="text" class="inputbox" name="lang" maxlength=2 required="true"/></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add language" class="button">&nbsp;&nbsp;or <a href="?module=language&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function createlanguageprocess() {
        global $ts;

	if (!file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.strtolower($_POST['lang']).".php")) {
		$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.strtolower($_POST['lang']).".php";
		$fh = fopen($file, 'w');
		fclose($fh);
		chmod($file, 0777);
		$_SESSION['cometchat']['error'] = 'New language added successfully';
	} else {
		$_SESSION['cometchat']['error'] = 'Language already exists. Please remove it and then try again.';
	}

	header("Location:?module=language&ts={$ts}");
}

function getlanguage($lang) {

	$filestoedit = array ( "" => "", "i" => "i", "m" => "m", "desktop" => "desktop" );

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit['modules'.DIRECTORY_SEPARATOR.$file] = $file;
			}
		}
		closedir($handle);
	}

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit['plugins'.DIRECTORY_SEPARATOR.$file] = $file;
			}
		}
		closedir($handle);
	}

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file) && (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')||$file =='mobileapp'||$file == 'desktop') && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php')) {
				$filestoedit['extensions'.DIRECTORY_SEPARATOR.$file] = $file;
			}
		}
		closedir($handle);
	}

	$data = '<?php '."\r\n".'// CometChat Language File - '.$lang."\r\n"."\r\n";

	foreach ($filestoedit as $name => $file) {

		if (empty($name)) {
			$namews = $name;
		} else {
			$namews = $name.'/';
		}

		if (file_exists((dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.'en.php')) {

			if (file_exists((dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.$lang.'.php')) {

				if (!empty($file)) {
					$file .= '_';
				}

				$array = $file.'language';

				$file = (dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$namews.'lang'.DIRECTORY_SEPARATOR.$lang.'.php';
				$fh = fopen($file, 'r');
				$readdata = @fread($fh, filesize($file));
				fclose($fh);

				$data .= "\$file['".$name."']='".base64_encode($readdata)."';\r\n\r\n";

			}
		}
	}

	$data .= ' ?>';

	return $data;
}

function additionallanguages() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=language&action=updatelanguage&ts={$ts}" method="post">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Additional Languages</h2>
		<h3>Official languages available from CometChat. If your language is not in the list below, you can create your own.</h3>

		<div>
			<div id="centernav">
				<div style="clear:both;">
					<ul id="modules_livelanguage">

					</ul>
				</div>
			</div>



			</div>
		</div>


	<div style="clear:both"></div>
	</form>
	<script>
		$(function() { language_getlanguages(); });
	</script>
EOD;

	template();

}

function previewlanguage() {

	if (!empty($_POST['data'])) {
		eval(str_replace('"','\'',str_replace('?>','',str_replace('<?php','',$_POST['data']['data']))));

		foreach ($file as $f => $d) {
			if ($f == '') { $f = 'core'; }
			echo "\n-- ";
			echo $f;
			echo " ----------------------\r\n";
			$d = str_replace('>','&gt;',str_replace('<','&lt;',str_replace('\';','\'',preg_replace('/(.*?) = /','', preg_replace('/(\r?\n){1,}/', "\n",preg_replace('/#.*/','',preg_replace('#//.*#','',preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',(str_replace('?>','',str_replace('<?php','',base64_decode($d))))))))))));
			echo $d;
		}
	}
}

function importlanguage() {

	if (!empty($_POST['data'])) {
		$lang = $_POST['data']['name'];
		$data = $_POST['data']['data'];
		$data = str_replace('"','\'',str_replace('<?php','',str_replace('?>','',$data)));
		eval($data);

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.strtolower($lang).".php")) {
			$_SESSION['cometchat']['error'] = 'Language already exists. Please remove it and then import the language.';
		} else {

			foreach ($file as $f => $d) {

				if (!empty($f)) {
					$f .= DIRECTORY_SEPARATOR;
					$f = str_replace("\\", DIRECTORY_SEPARATOR, $f);
				}

				if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$f.'lang') && !file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$f.'lang'.DIRECTORY_SEPARATOR.strtolower($lang).".php")) {
					$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$f.'lang'.DIRECTORY_SEPARATOR.strtolower($lang).".php";
					$fh = fopen($file, 'w');
					if (fwrite($fh, base64_decode($d)) === FALSE) {
							echo "Cannot write to file ($file)";
							exit;
					}
					fclose($fh);
					chmod($file, 0777);
				}
			}
		}
	}

	echo "1";
}

function uploadlanguage() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=language&action=uploadlanguageprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Upload new language</h2>
		<h3>Have you downloaded a new CometChat language? Upload only the .lng file e.g. "en.lng".</h3>

		<div>
			<div id="centernav">
				<div class="title">Language:</div><div class="element"><input type="file" class="inputbox" name="file"></div>
				<div style="clear:both;padding:5px;"></div>
			</div>

		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add language" class="button">&nbsp;&nbsp;or <a href="?module=language&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function uploadlanguageprocess() {
        global $ts;

	$error = '';

	if (!empty($_FILES["file"]["size"])) {
		if ($_FILES["file"]["error"] > 0) {
			$error = "Language corrupted. Please try again.";
		} else {
			if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $_FILES["file"]["name"])) {
				unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp/" .DIRECTORY_SEPARATOR. $_FILES["file"]["name"]);
			}

			if (!move_uploaded_file($_FILES["file"]["tmp_name"], dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $_FILES["file"]["name"])) {
				$error = "Unable to copy to temp folder. Please CHMOD temp folder to 777.";
			}
		}
	} else {
		$error = "Language not found. Please try again.";
	}

	if (!empty($error)) {
		$_SESSION['cometchat']['error'] = $error;
		header("Location: ?module=language&action=uploadlanguage&ts={$ts}");
		exit;
	}

	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $_FILES["file"]["name"]);

	$lang = basename(strtolower($_FILES["file"]["name"]), ".lng");

	foreach ($file as $f => $d) {

		if (!empty($f)) { $f .= '/'; }

		if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$f.'lang')) {

			$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$f.'lang'.DIRECTORY_SEPARATOR.strtolower($lang).".php";
			$fh = fopen($file, 'w');
			if (fwrite($fh, base64_decode($d)) === FALSE) {
					echo "Cannot write to file ($file)";
					exit;
			}
			fclose($fh);
			chmod($file, 0777);

		}

	}

	if (!empty($error)) {
		$_SESSION['cometchat']['error'] = $error;
		header("Location: ?module=language&action=uploadlanguage&ts={$ts}");
		exit;
	}

	unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $_FILES["file"]["name"]);

	$_SESSION['cometchat']['error'] = 'Language added successfully';
	header("Location: ?module=language&ts={$ts}");
	exit;

}

function exportlanguage() {

	$lang = $_GET['data'];

	$data = getlanguage($lang);

	header('Content-Description: File Transfer');
	header('Content-Type: application/force-download');
	header('Content-Disposition: attachment; filename='.$lang.'.lng');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_clean();
	flush();
	echo ($data);

}