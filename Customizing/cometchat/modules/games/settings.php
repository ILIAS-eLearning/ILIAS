<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

if (empty($_GET['process'])) {
	global $getstylesheet;
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

	echo <<<EOD
	<!DOCTYPE html>
	{$getstylesheet}
	<form style="height:100%" action="?module=dashboard&action=loadexternal&type=module&name=games&process=true" method="post">
	<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>Banned keywords will hide games which contain those keywords. Separate each word by comma e.g. adult, 18+, spiders</h3>

		<div>
			<div id="centernav" style="width:380px">
				<div class="title">Banned keywords:</div>
				<div class="element">
					<textarea type="text" class="inputbox" name="keywords">{$keywords}</textarea>
				</div>
				<div style="clear:both;padding:5px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button" />&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
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
	header("Location:?module=dashboard&action=loadexternal&type=module&name=games");
}