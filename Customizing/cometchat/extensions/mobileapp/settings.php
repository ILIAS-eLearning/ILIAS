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

global $getstylesheet;
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

$invalidfile ='';
if(!empty($_REQUEST['invalidfile'])){
	if($_REQUEST['invalidfile'] == 'fileformat'){
    	$invalidfile = '<div>Invalid file or file format. Please try again.</div><div style="clear:both;padding:5px;"></div>';
	}
	if($_REQUEST['invalidfile'] == 'filedimensions'){
    	$invalidfile = '<div>Invalid file dimensions. Please upload files with appropriate dimensions.</div><div style="clear:both;padding:5px;"></div>';
	}
}

if(!empty($_GET['uploadimageprocess'])){
	$allowedExts = array("png","jpg", "jpeg");
	$folderarray = array("ldpi", "mdpi", "hdpi", "xhdpi", "xxhdpi", "xxxhdpi","iOS","iOS@2x","iOS@3x");
	$size = array(array(108,36),array(144,48),array(216,72),array(288,96),array(432,144),array(576,192),array(125,25),array(250,50),array(250,50));
	$flag = 1;
    for ($i = 0; $i < 9; $i++) {
    	if(!empty($_FILES["file$i"]["name"])){
	    	$filename = $_FILES["file$i"]["name"];
	    	$filesize = getimagesize($_FILES["file$i"]["tmp_name"]);
	    	if(($filesize[0] == $size[$i][0] || $filesize[0] == $size[$i][1]) && $filesize[1] == $size[$i][1]){
	    		if($i < 6 || $filesize[0] != $size[$i][1]){
				    $temp = explode(".", $filename);
				    $extension = end($temp);
				    if (!in_array($extension, $allowedExts)) {
				        header("Location:?module=dashboard&action=loadexternal&type=extension&uploadimages=true&name=mobileapp&invalidfile=fileformat");
				        exit;
				    }
				}else{
					header("Location:?module=dashboard&action=loadexternal&type=extension&uploadimages=true&name=mobileapp&invalidfile=filedimensions");
				    exit;
				}
			}else{
				header("Location:?module=dashboard&action=loadexternal&type=extension&uploadimages=true&name=mobileapp&invalidfile=filedimensions");
			    exit;
			}
		}
	}
	for ($i = 0; $i < 9; $i++) {
		$foldername = $folderarray[$i];
		if(!empty($_FILES["file$i"]["name"])){
		    if ($_FILES["file$i"]["error"] > 0) {
		    } else {
		        if (file_exists(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.png")) {
		        	unlink(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.png");
				}
				if (file_exists(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.jpg")) {
		        	unlink(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.jpg");
				}
				if (file_exists(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.jpeg")) {
		        	unlink(dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.jpeg");
				}
		        if(move_uploaded_file($_FILES["file$i"]["tmp_name"],dirname(__FILE__)."/images/drawable-$foldername/ic_launcher.$extension")){
		        	$_SESSION['cometchat']['error'] = 'File uploaded successfully';
		        	echo '<script type="text/javascript">window.opener.location.reload();window.close();</script>';
		    	}
		    }
		}
	}
    exit;
}

if(!empty($_GET['uploadimages'])){
	$android = "0";
	$iOS = "0";
	if (file_exists(dirname(__FILE__)."/images/drawable-ldpi/ic_launcher.png") || file_exists(dirname(__FILE__)."/images/drawable-ldpi/ic_launcher.jpg") || file_exists(dirname(__FILE__)."/images/drawable-ldpi/ic_launcher.jpeg")) {
    	$android = "1";
	}
	if (file_exists(dirname(__FILE__)."/images/drawable-iOS/ic_launcher.png") || file_exists(dirname(__FILE__)."/images/drawable-iOS/ic_launcher.jpg") || file_exists(dirname(__FILE__)."/images/drawable-iOs/ic_launcher.jpeg")) {
    	$iOS = "1";
	}
	echo <<<EOD
	<!DOCTYPE html>
	$getstylesheet
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
	<style type="text/css" rel="stylesheet">
	.red{
		color:#F00;
	}
	.title.device_type{
		padding-top:6px;
	}
	</style>
	<script src="../js.php?admin=1"></script>
	<script type="text/javascript" language="javascript">
	    function resizeWindow() {
	        window.resizeTo((650), (($('form').outerHeight(false)+window.outerHeight-window.innerHeight)));
	    }
	    var iOS = "{$iOS}";
	    var android = "{$android}";
	    function validateForm(){
	    	if(
	    		(
	    			(
	    				(
	    					($('#ic_iOS').val() == null || $('#ic_iOS').val() == "") ||
	    					($('#ic_iOS_2x').val() == null || $('#ic_iOS_2x').val() == "")
	    				)
					 &&
	    				(iOS == "0")
	    			)
	    		)
	    		 &&
	    		(
	    			(
	    		 		(
	    		 			($('#ic_36').val() == null || $('#ic_36').val() == "") ||
		    		 		($('#ic_48').val() == null || $('#ic_48').val() == "") ||
							($('#ic_72').val() == null || $('#ic_72').val() == "") ||
							($('#ic_96').val() == null || $('#ic_96').val() == "") ||
							($('#ic_144').val() == null || $('#ic_144').val() == "")
						) &&
	    				(android == "0")
	    			)
				)
			){
	    		alert('Fields marked with * are compulsory');
	    		return false;
	    	}
	    }
	    $(function() {
			setTimeout(function(){
				resizeWindow();
			},200);
		});
	</script>
	<form style="height:100%" action="?module=dashboard&action=loadexternal&type=extension&name=mobileapp&uploadimageprocess=true" onsubmit="return validateForm()" method="post" enctype="multipart/form-data">
		<div id="content" style="width:auto">
			<h2>Only for white-labelled mobileapp</h2>
			<br>
			<h3>If you would like to use your own images and colors for the mobile app, you can make necessary changes here.</h3>
			<label style="color:#F00; font-size:18px">{$invalidfile}</label>
		    <label>Choose Header Image Icon for your App (Only .png & .jpeg files supported):</label>
		    <div style="font-weight:bold" class="titlefull">For Android (dimensions: width x height) :</div>
            <div style="clear:both;padding:5px;"></div>
            <span class="title device_type">LDPI : </span>
            <input type="file" name="file0" id="ic_36">
		    <label><span class="red">* </span>36px x 36px / 108px x 36px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">MDPI : </span>
            <input type="file" name="file1" id="ic_48">
		    <label><span class="red">* </span>48px x 48px / 144px x 48px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">HDPI : </span>
            <input type="file" name="file2" id="ic_72">
		    <label><span class="red">* </span>72px x 72px / 216px x 72px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">XHDPI : </span>
            <input type="file" name="file3" id="ic_96">
		    <label><span class="red">* </span>96px x 96px / 288px x 96px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">XXHDPI : </span>
            <input type="file" name="file4" id="ic_144">
		    <label><span class="red">* </span>144px x 144px / 432px x 144px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">XXXHDPI : </span>
            <input type="file" name="file5" id="ic_192">
		    <label>192px x 192px / 576px x 192px(optional)</label><br>

		    <div style="clear:both;padding:10px;"></div>
            <div style="font-weight:bold" class="titlefull">For iOS (dimensions: width x height) :</div>
            <div style="clear:both;padding:5px;"></div>
            <span class="title device_type">iOS : </span>
            <input type="file" name="file6" id="ic_iOS">
		    <label><span class="red">* </span>125px x 25px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type">iOS@2x : </span>
            <input type="file" name="file7" id="ic_iOS_2x">
		    <label><span class="red">* </span>250px x 50px</label><br>
		    <div style="clear:both;padding:5px;"></div>
		    <span class="title device_type" style="display:none;">iOS@3x: </span>
            <input type="file8" name="file8" id="ic_iOS_3x" style="display:none;">
		    <label style="display:none;"><span class="red">* </span>250px x 50px</label><br>
		    <label style="display:block;float:right">Fields marked with <span class="red">* </span>are compulsory</label>
		    <div style="clear:both;padding:5px;"></div>
			<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="?module=dashboard&amp;action=loadexternal&amp;type=extension&amp;name=mobileapp">Back</a>
		</div>
	</form>
	<script type="text/javascript" language="javascript"> resizeWindow(); </script>
EOD;
exit;
}

if (empty($_GET['process'])) {
echo <<<EOD
<!DOCTYPE html>

$getstylesheet
</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
<style rel="stylesheet" type="text/css">
	html{
		overflow-y: hidden;
	}
	form{
		padding: 5px;
	}
	#content{
		margin: 0;
	}
</style>
<script src="../js.php?admin=1"></script>
<script type="text/javascript" language="javascript">
    function resizeWindow() {
    	window.resizeTo((490), (($('form').outerHeight(false)+window.outerHeight-window.innerHeight)));
    }
    var arr = ['#headerColor','#login_background','#login_foreground','#login_placeholder','#login_button_pressed','#login_foreground_text'];
    var arrColor = ['$headerColor','$login_background','$login_foreground','$login_placeholder','$login_button_pressed','$login_foreground_text'];
    $(function() {

    	$.each(arr,function(i,val){
    		$(val).ColorPicker({
				color: arrColor[i],
				onShow: function (colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$(val+' div').css('backgroundColor', '#' + hex);
					$(val).attr('newcolor','#'+hex);
					$(val+'_field').val('#'+hex.toUpperCase());
				}
			});
    	}) ;

		setTimeout(function(){
			resizeWindow();
		},200);
	});
</script>
<form action="?module=dashboard&action=loadexternal&type=extension&name=mobileapp&process=true" method="post" enctype="multipart/form-data">
	<div id="content" style="width:auto;height:520px;">
		<h2>Settings</h2>
		<br>
		<h3>If you would like to use your own images and colors for the mobile app, you can make necessary changes here.</h3>
		<div>
			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Home Url :</div>
				<div class="element">
					<input type="text" class="inputbox" id="homeUrl_field" name="homepage_URL" value="$homepage_URL" style="float: right;width: 147px;height:28px" required="false">
				</div>
			</div>
			<br>
			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Header Color :</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="headerColor_field" name="headerColor" value="$headerColor" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="headerColor" id="headerColor">
						<div style="background:$headerColor">
						</div>
					</div>
				</div>
			</div>
			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Login Color :</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="login_background_field" name="login_background" value="$login_background" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="login_background" id="login_background">
						<div style="background:$login_background">
						</div>
					</div>
				</div>
			</div>
			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Login foreground</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="login_foreground_field" name="login_foreground" value="$login_foreground" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="login_foreground" id="login_foreground">
						<div style="background:$login_foreground">
						</div>
				</div>
			</div>

			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Login placeholder</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="login_placeholder_field" name="login_placeholder" value="$login_placeholder" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="login_placeholder" id="login_placeholder">
						<div style="background:$login_placeholder">
						</div>
				</div>
			</div>

			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Login button pressed</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="login_button_pressed_field" name="login_button_pressed" value="$login_button_pressed" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="login_button_pressed" id="login_button_pressed">
						<div style="background:$login_button_pressed">
						</div>
				</div>
			</div>

			<div id="centernav" style="float:none;overflow:hidden;">
				<div class="title" style="padding-top:14px;">Login foreground text</div>
				<div class="element">
					<input type="text" class="inputbox themevariables" id="login_foreground_text_field" name="login_foreground_text" value="$login_foreground_text" style="float: right;width: 100px;height:28px" required="true">
					<div class="colorSelector themeSettings" field="login_foreground_text" id="login_foreground_text">
						<div style="background:$login_foreground_text">
						</div>
				</div>
			</div>


		</div>
		<div>
		    <br>
		    <br>
		    <h3 style="border-bottom:0px"><a href="?module=dashboard&amp;action=loadexternal&amp;type=extension&amp;name=mobileapp&amp;uploadimages=true">Click here</a> to set header images for your white-labelled mobileapp</h3>
		    <br>
		    <br>
		    <input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
	    </div>
	</div>
</form>
EOD;
} else {

	$data = '';

	foreach ($_POST as $field => $value) {
		$data .= '$'.$field.' = \''.$value.'\';'."\r\n";
	}

	configeditor('SETTINGS',$data,0,dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
	header("Location:?module=dashboard&action=loadexternal&type=extension&name=mobileapp");
}