<?php
$errorjs = '';
if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

if (isset($_SESSION['cometchat']['error']) && !empty($_SESSION['cometchat']['error'])) {
		$errorjs = <<<EOD
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
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

	$errorMsg = '';
	$innercontent = '"';

	if (!checkcURL()) {
		$errorMsg = "<h2 id='errormsg' style='font-size: 11px; color: rgb(255, 0, 0);'>cURL extension is disabled on your server. Please contact your webhost to enable it. cURL is required for Translate Conversations.</h2>";
		$innercontent = 'display:none;"';
	}

echo <<<EOD
<!DOCTYPE html>

$getstylesheet
<style>
	#alert {
		overflow: hidden;
		width: 390px;
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
<script src="../js.php?admin=1"></script>
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=module&name=twitter&process=true" method="post">
	<div id="content" style="width:auto">
			<h2>Settings</h2>
			<h3>If you are unsure about any value, please skip them</h3>
			<div>
				{$errorMsg}
				<div id="centernav" style="width:380px; {$innercontent}">
					<div class="title">Twitter Username:</div><div class="element"><input type="text" class="inputbox" name="twitteruser" value="$twitteruser"></div>
					<div style="clear:both;padding:5px;"></div>

					<div class="title">Number of Tweets:</div><div class="element"><input type="text" class="inputbox" name="notweets" value="$notweets"></div>
					<div style="clear:both;padding:5px;"></div>

					<div class="title">Consumer key:</div><div class="element"><input type="text" class="inputbox" name="consumerkey" value="$consumerkey"></div>
					<div style="clear:both;padding:5px;"></div>

					<div class="title">Consumer Secret:</div><div class="element"><input type="text" class="inputbox" name="consumersecret" value="$consumersecret"></div>
					<div style="clear:both;padding:5px;"></div>

					<div class="title">Access token:</div><div class="element"><input type="text" class="inputbox" name="accesstoken" value="$accesstoken"></div>
					<div style="clear:both;padding:5px;"></div>

					<div class="title">Access token secret:</div><div class="element"><input type="text" class="inputbox" name="accesstokensecret" value="$accesstokensecret"></div>
					<div style="clear:both;padding:5px;"></div>

				</div>
			</div>

			<div style="clear:both;padding:7.5px;"></div>
			<input type="submit" value="Update Settings" class="button" style = "{$innercontent}" >&nbsp;&nbsp;<a href="javascript:window.close();" style = "{$innercontent}" >cancel or close</a>
	</div>
</form>
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
$errorjs
EOD;
} else {
	$dataerror = 0;
	$data = '';
	$_POST['notweets'] = $_POST['notweets'] ? $_POST['notweets'] : 0;
	foreach ($_POST as $field => $value) {
		$data .= '$'.$field.' = \''.$value.'\';'."\r\n";
		if(empty($value) && !$dataerror && $field != 'notweets') {
			$dataerror = 1;
		}
	}

	if($dataerror) {
		$_SESSION['cometchat']['error'] = 'Please enter all the configuration details.';
	} else {
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'twitteroauth'.DIRECTORY_SEPARATOR.'twitteroauth.php');
		$connection = new TwitterOAuth($_POST['consumerkey'], $_POST['consumersecret'], $_POST['accesstoken'], $_POST['accesstokensecret']);
		$followers = $connection->get("https://api.twitter.com/1.1/followers/list.json?cursor=-1&screen_name=".$_POST['twitteruser']."&count=1");
		if(isset($followers->errors)) {
			$_SESSION['cometchat']['error'] = 'Twitter authentication failed.';
		} else {
			$_SESSION['cometchat']['error'] = 'Twitter details updated successfully.';
			configeditor('SETTINGS',$data,0,dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
		}
	}
	header("Location:?module=dashboard&action=loadexternal&type=module&name=twitter");
}