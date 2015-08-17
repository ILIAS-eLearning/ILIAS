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
$extrajs = '';

if (isset($_SESSION['cometchat']['error']) && !empty($_SESSION['cometchat']['error'])) {
	$extrajs = <<<EOD
<style>
	#alert {
		overflow: hidden;
		width: 100%;
		text-align: center;
		position: fixed;
		top: 0;
		left: 0;
		background-color: #76B6D2;
		height: 0;
		color: #fff;
		font: 15px/30px arial, sans-serif;
		opacity: .9;
	}
</style>
<script>
	$(function() {
		$.fancyalert('{$_SESSION['cometchat']['error']}');
	});

	(function($){

		$.fancyalert = function(message){
			if ($("#alert").length > 0) {
				removeElement("alert");
			}

			var html = '<div id="alert">'+message+'</div>';
			$('body').append(html);
			alertelement = $('#alert');
			if(alertelement.length) {
				var alerttimer = window.setTimeout(function () {
					alertelement.trigger('click');
				}, 5000);
				alertelement.css('border-bottom','4px solid #76B6D2');
				alertelement.animate({height: alertelement.css('line-height') || '50px'}, 200)
				.click(function () {
					window.clearTimeout(alerttimer);
					alertelement.animate({height: '0'}, 200);
					alertelement.css('border-bottom','0px solid #333333');
				});
			}
		};
	})($);
</script>
EOD;
	unset($_SESSION['cometchat']['error']);
}


if (empty($_GET['process'])) {
	global $getstylesheet;
	global $smileys_default;
	global $smileys;
	global $smlWidth;
	global $smlHeight;
	$extrajs .= '<script> var smileys = {};';
	foreach ($smileys as $code => $value) {
		$extrajs .= 'smileys["'.$code.'"] = "'.$value.'";';

	}
	$extrajs .= '</script>';
	$used = array();
	$customSmilies = '';
	foreach ($smileys_default as $pattern => $result) {
		if (!empty($used[$result])) {
		} else {
			$pattern2 = str_replace("'","\\'",$pattern);
			$title = str_replace("-"," ",ucwords(preg_replace("/\.(.*)/","",$result)));

			$customSmilies .= '<div class="smilies"><div class="sm-img"><img class="custom_smiley" width="100%" height="100%" src="'.BASE_URL.'images/smileys/'.$result.'" /><input type="file" class="imgUpload" accept="image/x-png, image/gif, image/jpeg" onchange="imgUpload(this,\''.$pattern.'\');" /></div><div class="sm-code"><input type="text" value="'.$pattern.'" readonly orignal="'.$pattern.'" rel="'.$result.'"/></div><div class="sm-delete" rel="'.$pattern.'" imgUrl="'.$result.'"></div></div>';

			$used[$result] = 1;
		}
	}


echo <<<EOD
<!DOCTYPE html>

{$getstylesheet}
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script> function resizeWindow() {window.resizeTo(($("form").outerWidth(false)+window.outerWidth-$("form").outerWidth(false)), ($('form').outerHeight(false)+window.outerHeight-window.innerHeight)); }</script>
<style>
	fieldset {
		border: 1px solid #ccc;
		padding: 10px 5px;
		width: 310px;
	}
	legend {
		font-size: 12px;
		color: #333;
	}
	#allSm {
		height: 200px;
		width: 100% !important;
		overflow: hidden;
	}
	.smilies {
		float: left;
		width: 65px;
		height: 55px;
		text-align: center;
		position: relative;
		margin: 5px;
		border: 1px solid #ccc;
		overflow: hidden;
	}
	.sm-img {
		display: inline-block;
		height: 20px;
		width: 20px;
		margin: 5px;
		position: relative;
		overflow: hidden;
	}
	.sm-code {
		display: inline-block;
		height: 20px;
		width: 100%;
	}
	.sm-code input {
		width: 90%;
		border: 0;
		outline: 0;
		background: transparent;
		margin: 0;
		height: 100%;
		padding: 0;
		text-align: center;
	}
	.sm-delete {
		position: absolute;
		background: url('http://www.aleks.com/aleks/gif/x_icon.gif') no-repeat;
		height:15px;
		width: 15px;
		background-size: 100%;
		top: 0px;
		right: 0px;
		display: none;
		cursor: pointer;
	}
	.smilies:hover .sm-delete {
		display: block;
	}
	.smilies:hover .newSmDelete {
		display: none !important;
	}
	.imgUpload {
		width: 100%;
		height: 100%;
		position: absolute;
		left: 0;
		top: 0;
		opacity: 0;
		cursor: pointer;
	}
	.enable {
		background: white !important;
		border: 1px solid #c6c6c6 !important;
	}
	.invalid {
		border: 1px solid red !important;
	}
	.valid {
		border: 1px solid green !important;
	}.sm-newImg {
		margin: 15px;
		height: 25px;
		width: 25px;
	}
</style>

<form style="height:100%" action="?module=dashboard&action=loadexternal&type=plugin&name=smilies&process=true" method="post" id="smilies" enctype="multipart/form-data">
	<div id="content" style="width:auto">
		<h2>Settings</h2><br/>
		<div style="overflow: hidden;">
			<div id="centernav" style="width:380px">
				<div class="title">Width:</div><div class="element"><input type="text" class="inputbox" name="smlWidth" value="{$smlWidth}" /></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Height:</div><div class="element"><input type="text" class="inputbox" name="smlHeight" value="{$smlHeight}" /></div>

			</div>
		</div>
		<br/>
		<div id="centernav" style="width:380px;display:none;">
			<div class="title">Add New Smiley:</div><div class="element"><input type="checkbox" class="inputbox" name="addSm" id="addSm"  style="width: auto;"></div>
			<div style="clear:both;padding:5px;"></div>
		</div>
		<div style="overflow: hidden; display:none;" id="newSm">
			<div id="centernav" style="width:380px">
				<div class="title">Code:</div><div class="element"><input type="text" class="inputbox" name="smCode" ></div>
				<div style="clear:both;padding:5px;"></div>
				<div class="title">Upload Image:</div><div class="element"><input type="file" class="inputbox" name="smImg" accept="image/x-png, image/gif, image/jpeg"></div>
			</div>
		</div>
		<div style="clear:both;padding:5px;"></div>
		<div id="customSmilies">
			<fieldset>
				<legend>More Smilies:</legend>
				<div id="allSm">
					{$customSmilies}
				</div>
			</fieldset>
		</div>
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>

	</div>
</form>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" language="javascript">
	$(function() {
		setTimeout(function(){
				resizeWindow();
			},200);
	});
</script>
<script>jqcc=jQuery;</script><script src="../js.php?type=core&name=scroll"></script>
{$extrajs}
<script>
	$(function(){
		var addSmileyCode = '<div class="smilies" id="addedSm"><div class="sm-img sm-newImg"><img class="custom_smiley" width="100%" height="100%" src="images/plus.png" title="Upload New Smiley"/><input type="file" class="imgUpload newSmImg" accept="image/x-png, image/gif, image/jpeg" onchange="imgUpload(this,\'\',1);" title="Upload New Smiley" /></div><div class="sm-code"><input type="text" value="" readonly="" orignal="CC_SMILIES" rel="" class="newSmCode" /></div><div class="sm-delete newSmDelete" rel="" imgurl=""></div></div>';

		$('#allSm').append(addSmileyCode).slimScroll({'width': '320px'});

		$('.sm-code input').live('focus',function() {
			if ($(this).hasClass('newSmCode')) {
				var newSmiley = $(this).parents('#addedSm').find('.imgUpload').get()[0].files.length;
				if (newSmiley > 0) {
					$(this).addClass('enable').removeAttr('readonly').removeClass('invalid');
				}
			} else {
				$(this).addClass('enable').removeAttr('readonly').removeClass('invalid');
			}
		});

		$('.sm-code input').live('keyup',function(e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				$(this).blur();
			}
			var smCode = $.trim($(this).val());
			if (smCode == '' || smileys.hasOwnProperty(smCode)) {
				$(this).removeClass('valid').addClass('invalid');
			} else {
				$(this).removeClass('invalid').addClass('valid');
			}
		});

		$('.sm-code input').live('blur',function() {
			var element = $(this);
			var newSmiley = 0;
			if (element.hasClass('newSmCode')) {
				newSmiley = element.parents('#addedSm').find('.imgUpload').get()[0].files.length;
			}
			element.removeClass('enable invalid valid').attr('readonly');
			var newCode = $.trim(element.val());
			var currCode = element.attr('orignal');
			var currImg = element.attr('rel');

			if (newCode == '' && newSmiley > 0) {
				element.addClass('invalid').focus();
			} else if (newCode != currCode && newCode != '') {
				if(smileys.hasOwnProperty(newCode)) {
					element.addClass('invalid');
				} else {
					$.ajax({
						url: "?module=dashboard&action=loadexternal&type=plugin&name=smilies&process=true",
						type: "POST",
						data : {currCode:currCode,newCode:newCode,currImg:currImg,ajaxAction:'code'},
						success: function(res) {
							if (res) {
								element.attr('orignal',newCode);
								if (element.hasClass('newSmCode')) {
									element.parent().siblings().attr('rel',newCode).attr('imgurl',currImg).removeClass('newSmDelete');
									element.parents('.smilies').removeAttr('id');
									element.parents('.smilies').find('.sm-img input').removeClass('newSmImg');
									element.parents('.smilies').find('.sm-code input').removeClass('newSmCode');
									$('#allSm').append(addSmileyCode);
								}
							}
						}
					});
				}
			}
		});

		$('.sm-delete').live('click',function() {
			if (confirm("Are you sure you want to remove this smiley?")) {
				var element = $(this);
				var code = element.attr('rel');
				var imgUrl = element.attr('imgUrl');
				$.ajax({
					url: "?module=dashboard&action=loadexternal&type=plugin&name=smilies&process=true",
					type: "POST",
					data : {code:code,imgUrl:imgUrl,ajaxAction:'del'},
					success: function(res) {
						if (res == 1) {
							element.parent().remove();
							delete smileys[code];
						}
					}
				});
			}
		});
	});

	function imgUpload(elem,code,newSmiley) {
		var fd = new FormData();
		fd.append("newImg", elem.files[0]);
		fd.append("ajaxAction", 'img');
		if (newSmiley == 1) {
			$('.newSmCode').attr('rel',elem.files[0].name).click().focus();
			code = 'CC_SMILIES';
		}
		$(elem).addClass('active').parent().removeClass('sm-newImg');
		if (code != '' && code != 'undefined') {
			fd.append("code", code);
			var xhr = new XMLHttpRequest();
			xhr.elem = elem;
			xhr.addEventListener("load", uploadComplete, false);
			xhr.open("POST", "?module=dashboard&action=loadexternal&type=plugin&name=smilies&process=true");
			xhr.send(fd);
		}
	}

	function uploadComplete(evt) {
		var input = evt.target.elem;
		imgPreview(input);
	}

	function imgPreview(input) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function (e) {
				$('.imgUpload.active').parent().find('.custom_smiley').attr('src', e.target.result);
				$('.imgUpload').removeClass('active');
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	setTimeout(function () {
		var myform = document.getElementById('smilies');
		myform.addEventListener('submit', function(e) {
			e.preventDefault();
			var smCode = $.trim(document.getElementsByName('smCode')[0].value);
			var smImg = $.trim(document.getElementsByName('smImg')[0].value);
			var addSm = $("#addSm").is(":checked");

			if (addSm && (smCode == '')) {
				alert('Please enter valid code for new smiley.');
				return false;
			} else if (addSm && smileys.hasOwnProperty(smCode)) {
				alert('The smiley code is already exist. Please try with different code.');
				return false;
			} else if (addSm && smImg == '' ) {
				alert('Please upload image for new smiley.');
				return false;
			} else {
				myform.submit();
			}
		});
	}, 500);
</script>
EOD;
} else {
	global $smileys_default;
	global $smileys;
	$error = 1;

	if (!empty($_POST['ajaxAction']) && $_POST['ajaxAction'] == 'code') {
		$error = 0;
		if ($_POST['currCode'] != 'CC_SMILIES') {
			$smileys_default[$_POST['newCode']] = $smileys_default[$_POST['currCode']];
			unset($smileys_default[$_POST['currCode']]);
		} else {
			$smileys_default[$_POST['newCode']] = $_POST['currImg'];
		}
		echo 1;
	} elseif (!empty($_POST['ajaxAction']) && $_POST['ajaxAction'] == 'img') {
		if (move_uploaded_file($_FILES['newImg']['tmp_name'], dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."smileys".DIRECTORY_SEPARATOR.$_FILES['newImg']['name'])) {
			if ($_POST['code'] != 'CC_SMILIES') {
				$smileys_default[$_POST['code']] = $_FILES['newImg']['name'];
				$error = 0;
			}
			echo 1;
		}
	} elseif (!empty($_POST['ajaxAction']) && $_POST['ajaxAction'] == 'del') {
		unset($smileys_default[$_POST['code']]);
		unlink(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."smileys".DIRECTORY_SEPARATOR.$_POST['imgUrl']);
		$error = 0;
		echo 1;
	}

	if (!$error) {
		$smData = '\$smileys_default = ';
		$smData .= var_export($smileys_default,true);
		$smData = substr_replace($smData, '', strrpos($smData, ','), strlen(','));
		$smData .= ";";
		configeditor('SMILEYS',$smData,0);
		if (empty($_POST['ajaxAction'])) {
			$_SESSION['cometchat']['error'] = 'Smiley added successfully';
		}
	}

	$data = '';
	foreach ($_POST as $field => $value) {
		if ($field == 'smlWidth' || $field == 'smlHeight') {
			$data .= '$'.$field.' = \''.$value.'\';'."\r\n";
		}
	}
	if ($data != '') {
		configeditor('SETTINGS',$data,0,dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
	}
	if (empty($_POST['ajaxAction'])) {
		header("Location:?module=dashboard&action=loadexternal&type=plugin&name=smilies");
	}
}