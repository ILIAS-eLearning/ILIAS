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
		<a href="?module=themes&amp;ts={$ts}">Themes</a>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;
	global $color;
        global $theme;

        $athemes = array();
        $recommendedcolor = array('Facebook' => 'Facebook', 'Hangout' => 'Hangout', 'Lite' => 'Standard', 'Mobile' => '', 'Standard' => 'Standard','Synergy' => 'Synergy');

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "base" && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'config.php')) {
				$athemes[] = $file;
			}
		}
		closedir($handle);
	}

	$activethemes = '';
	$no = 0;

	foreach ($athemes as $ti) {

		$title = ucwords($ti);

		++$no;

		$default = '';
		$opacity = '0.5';
		$titlemakedefault = 'title="Activate this theme"';
		$setdefault = '';
		$star = '<a href="javascript:void(0)" onclick="javascript:themestype_makedefault(\''.$ti.'\')" style="margin-right:5px;"><img src="images/default.png" '.$titlemakedefault.' style="opacity:'.$opacity.';"></a>';
		if (strtolower($theme) == strtolower($ti)) {
			$default = ' (Recommended color: '.$recommendedcolor[$title].') (Active)';
			$opacity = '1;cursor:default';
			$titlemakedefault = '';
			$setdefault = '';
                        $star = '<a href="javascript:void(0)" style="margin-right:5px;"><img src="images/default.png" '.$titlemakedefault.' style="opacity:'.$opacity.';"></a>';
                } else {
                        $default = ' (Recommended color: '.$recommendedcolor[$title].')';
                }

                if (strtolower($ti) == 'mobile' || strtolower($ti) == 'synergy') {
					$default = ' (Default)';
					$opacity = '1;cursor:default';
					$titlemakedefault = '';
					$setdefault = '';
		            $star ='';
				}

				if(strtolower($ti) == 'synergy'){
					$default = '';
				}

				if (strtolower($ti) == 'synergy'){
					$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$star.'<a href="../cometchat_popout.php" target="_blank" style="margin-right:5px;"><img src="images/link.png" title="Direct link to Synergy"></a><a href="javascript:void(0)" onclick="javascript:themetype_configmodule(\''.$ti.'\')" style="margin-right:5px;"><img src="images/embed.png" title="Generate Embed Code" ></a></span><div style="clear:both"></div></li>';
				} else{
					$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$star.'<a href="javascript:void(0)" onclick="javascript:themetype_configmodule(\''.$ti.'\')" style="margin-right:5px;"><img src="images/config.png" title="Edit '.$title.'"></a></span><div style="clear:both"></div></li>';
				}
	}

	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
                 <div>
		<h2>Themes</h2>
		<h3>To set the theme type, click on the star button next to the theme.</h3>

		<div>
			<ul id="modules_livethemes">
				$activethemes
			</ul>
		</div>
                 </div>


EOD;

        $athemes = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "index.html" && $file != "synergy.bak"  && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'cometchat.css')) {
                            $athemes[] = current(explode('.',pathinfo($file, PATHINFO_BASENAME)));
			}
		}
		closedir($handle);
	}

	$activethemes = '';
	$no = 0;

	foreach ($athemes as $ti) {

		$title = ucwords($ti);

		++$no;

		$default = '';
		$opacity = '0.5';
		$titlemakedefault = 'title="Activate this color-scheme"';
		$setdefault = 'onclick="javascript:themes_makedefault(\''.$ti.'\')"';

		if (strtolower($color) == strtolower($ti)) {
			$default = ' (Active)';
			$opacity = '1;cursor:default';
			$titlemakedefault = '';
			$setdefault = '';
		}

                $isdefault = '<a href= "javascript:void(0)" onclick="javascript:themes_editcolor(\''.$ti.'\')" style="margin-right:5px;"><img src="images/config.png" title="Edit Color"></a><a href="?module=themes&amp;action=clonecolor&amp;theme='.$ti.'&amp;ts={$ts}"><img src="images/clone.png" title="Clone Color" style="margin-right:5px;"></a><a href="javascript:void(0)" onclick="javascript:themes_removecolor(\''.$ti.'\')"><img src="images/remove.png" title="Remove Color"></a>';
		if($ti == 'hangout' or $ti == 'standard' or $ti == 'facebook'){
                        $isdefault = '<a href="?module=themes&amp;action=clonecolor&amp;theme='.$ti.'&amp;ts={$ts}"><img src="images/clone.png" title="Clone Color" style="margin-right:5px;"></a>';
		} elseif($ti == 'synergy'){
				$isdefault = '<a href= "javascript:void(0)" onclick="javascript:themes_editcolor(\''.$ti.'\')" style="margin-right:5px;"><img src="images/config.png" title="Edit Color"></a>';
		}
		if($ti == 'synergy'){
			$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$isdefault.'</span><div style="clear:both"></div></li>';
		}else{
			$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="javascript:void(0)" '.$setdefault.' style="margin-right:5px;"><img src="images/default.png" '.$titlemakedefault.' style="opacity:'.$opacity.';"></a>'.$isdefault.'</span><div style="clear:both"></div></li>';
		}
	}


	$body .= <<<EOD
                <div class="margin-top">
		<h2>Colors</h2>
		<h3>To set the Color, click on the star button next to the Color.</h3>

		<div>
			<ul id="modules_livethemes">
				$activethemes
			</ul>
		</div>
                </div>
                <div style="clear:both"></div>
        <input type="button" value="Create new Color" class="button margin-top" onclick="javascript:create_new_colorscheme()">
	</div>

	<div style="clear:both"></div>



EOD;

	template();

}

function makedefault() {

	if (!empty($_POST['theme'])) {

		$themedata = '$color = \'';

		$themedata .= $_POST['theme'];
		$themedata .= '\';';
		if ($_POST['theme'] != 'lite') {
			configeditor('COLOR',$themedata);
			$_SESSION['cometchat']['error'] = 'Successfully updated color. Please clear your browser cache and try.';
		} else {
			$_SESSION['cometchat']['error'] = 'Sorry, you cannot set the lite theme as default.';
		}
	}

	echo "1";

}

function themestypemakedefault() {

	if (!empty($_POST['theme'])) {

		$themedata = '$theme = \'';

		$themedata .= $_POST['theme'];
		$themedata .= '\';';
                configeditor('THEME',$themedata);
                $_SESSION['cometchat']['error'] = 'Successfully updated theme. Please clear your browser cache and try.';
        }

	echo "1";

}

function checkcolor($color) {

	if (substr($color,0,1) == '#') {
		$color = strtoupper($color);

		if (strlen($color) == 4) {
			$color = $color[0].$color[1].$color[1].$color[2].$color[2].$color[3].$color[3];
		}

	}

	return $color;

}

function restorecolorprocess() {

	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.'synergy.bak';

	$fh = fopen($file, 'r');
	$restoredata = fread($fh, filesize($file));
	fclose($fh);

	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR."synergy.php";
	$fh = fopen($file, 'w');
	if (fwrite($fh, $restoredata) === FALSE) {
			echo "Cannot write to file ($file)";
			exit;
	}
	fclose($fh);
	chmod($file, 0777);

	$_SESSION['cometchat']['error'] = 'Colors have been restored successfully.';

	echo "1";
	exit;

}

function editcolor() {
	global $body;
	global $navigation;
        global $ts;
        global $themeSettings;

	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$_GET['data'].'.php')) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$_GET['data'].'.php');
	}

	$restore = '<a href="?module=themes&amp;ts={$ts}">cancel</a>';
	if($_GET['data'] == 'synergy'){
		$restore = '<a onclick="javascript:themes_restorecolors()" href="">restore</a>' ;
	}

	$form = '';
	$inputs = '';
	$js = '';
	$uniqueColor = array();

	foreach ($themeSettings as $field => $input) {
		$input = checkcolor($input);

		$form .= '<div class="titlesmall" style="padding-top:14px;" >'.$field.'</div><div class="element">';

		if (substr($input,0,1) == '#') {

			if (empty($uniqueColor[$input])) {
				$inputs .= '<div class="themeBox colors" oldcolor="'.$input.'" newcolor="'.$input.'" style="background:'.$input.';"></div>';
			}

			$uniqueColor[$input] = 1;

			$form .= '<input type="text" class="inputbox themevariables" id=\''.$field.'_field\' name=\''.$field.'\' value=\''.$input.'\' style="width: 100px;height:28px" required="true"/>';
			$form .= '<div class="colorSelector themeSettings" field="'.$field.'" id="'.$field.'" oldcolor="'.$input.'" newcolor="'.$input.'" ><div style="background:'.$input.'" style="float:right;margin-left:10px"></div></div>';

			$input = substr($input,1);
			$js .= <<<EOD
$('#$field').ColorPicker({
	color: '#$input',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(500);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(500);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('#$field div').css('backgroundColor', '#' + hex);
		$('#$field').attr('newcolor','#'+hex);
		$('#{$field}_field').val('#'+hex);
	}
});

EOD;

		} else {
			$form .= '<input type="text" class="inputbox themevariables" name=\''.$field.'\' value=\''.$input.'\' style="height:28px;width:250px;" required="true"/>';
		}

		$form .= '</div><div style="clear:both;padding:7px;"></div>';

	}

	$js .= <<<EOD

$(function() {
		$( "#slider" ).slider({
			value:0,
			min: 0,
			max: 1,
			step: 0.0001,
			slide: function( event, ui ) {
				shift(ui.value);
			}
		});
});

EOD;

	$body = <<<EOD

	<script>

	$(function() { $js });

	</script>
	$navigation
	<form>
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Theme Editor</h2>
		<h3>Edit your theme using two simple tools. If you need advanced modifications, then manually edit the CSS files in the <b>cometchat</b> folder on your server.</h3>

		<div>
			<div id="centernav">
				<h2>Color changer</h2>
				<h3>Use the slider to change the base colors.</h3>
				$inputs
				<div style="clear:both;padding:7.5px;"></div>
				<div id="slider"></div>
				<div style="clear:both;padding:7.5px;"></div>
				<input type="button" value="Update colors" class="button" onclick="javascript:themes_updatecolors('{$_GET['data']}')">&nbsp;&nbsp;or {$restore}
				<div style="clear:both;padding:20px;"></div>

				<h2>Theme Variables</h2>
				<h3>Update colors, font family and font sizes of your theme.</h3>

				<div>
					<div id="centernav" style="width:700px">
						$form
					</div>
				</div>

				<div style="clear:both;padding:7.5px;"></div>
				<input type="button" value="Update variables" class="button" onclick="javascript:themes_updatevariables('{$_GET['data']}')">&nbsp;&nbsp;or {$restore}
			</div>
			<div id="rightnav">
				<h1>Tips</h1>
				<ul id="modules_availablethemes">
					<li>For more advanced modifications, please edit themes/{$_GET['data']}/cometchat.css file.</li>
 				</ul>
			</div>
		</div>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}

function updatecolorsprocess() {
        global $themeSettings;
	$colors = $_POST['colors'];
	$_GET['data'] = $_POST['theme'];

	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$_GET['data'].'.php');

	foreach ($themeSettings as $field => $input) {
		$input = checkcolor($input);

		if (!empty($colors[strtoupper($input)])) {
			$themeSettings[$field] = strtoupper($colors[$input]);
		}
	}

	$data = '$themeSettings = array('."\r\n";

	foreach ($themeSettings as $field => $input) {
		$data .= "'".$field."' => '".$input."',"."\r\n";
	}

	$data .= ");";


	$_SESSION['cometchat']['error'] = 'Color scheme updated successfully';

	configeditor('SETTINGS',$data,0,dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$_GET['data'].'.php');

	echo 1;

}

function updatevariablesprocess() {

	$colors = $_POST['colors'];
	$_GET['data'] = $_POST['theme'];

	$data = '$themeSettings = array('."\r\n";

	foreach ($colors as $field => $input) {
		$data .= "'".$field."' => '".$input."',"."\r\n";
	}

	$data .= ");";

	$_SESSION['cometchat']['error'] = 'Color scheme updated successfully';

	configeditor('SETTINGS',$data,0,dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$_GET['data'].'.php');

	echo 1;

}

function clonecolor() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=themes&action=clonecolorprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Create color scheme</h2>
		<h3>Please enter the name of your new color cheme. Do not include special characters in your theme name.</h3>
		<div>
			<div id="centernav">
				<div class="title">Color scheme name:</div><div class="element"><input type="text" class="inputbox" name="theme" required="true"/><input type="hidden" name="clone" value="{$_GET['theme']}"></div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add Color scheme" class="button">&nbsp;&nbsp;or <a href="?module=themes&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function clonecolorprocess() {
        global $ts;
	$color = createslug($_POST['theme']);
	$clone = $_POST['clone'];

        if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$color.'.php')) {
            $_SESSION['cometchat']['error'] = ucfirst($color).' color scheme alredy exists.';
            header("Location:?module=themes&action=clonecolor&theme={$clone}&ts={$ts}");
            exit;
        }
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$clone.'.php')) {
		copy(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$clone.'.php',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$color.'.php');
	}

	$_SESSION['cometchat']['error'] = 'New color scheme added successfully';
	header("Location:?module=themes&ts={$ts}");
}

function removecolorprocess() {
        global $ts;
	$color = $_GET['data'];

	if ($color != 'standard' && !empty($color)) {

		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')) {
					if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color)) {
						deletedirectory((dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color));
					}
				}
			}
			closedir($handle);
		}

		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')) {
					if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color)) {
						deletedirectory((dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color));
					}
				}
			}
			closedir($handle);
		}


		if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'code.php')) {
					if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color)) {
						deletedirectory((dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$color));
					}
				}
			}
			closedir($handle);
		}

		if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$color.'.php')) {
			 unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'colors'.DIRECTORY_SEPARATOR.$color.'.php');
		}

		$_SESSION['cometchat']['error'] = 'Color scheme deleted successfully';

	} else {
		$_SESSION['cometchat']['error'] = 'Sorry, this color scheme cannot be deleted. Please manually remove the theme from the "themes/color" folder.';
	}


	header("Location:?module=themes&ts={$ts}");
}