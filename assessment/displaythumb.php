<?php

	chdir("..");
	require_once "./include/inc.header.php";
	require_once "./classes/class.ilUtil.php";
	
	if (!$_GET["gfx"])
		exit();
	if (($_GET["size"] > 20) and ($_GET["size"] < 300)) {
		$size = $_GET["size"];
	} else {
		$size = 100;
	}
	$imagepath = $_GET["gfx"];
	$thumbpath = $imagepath . "." . $ilias->account->id . "." . mt_rand(200,2000) . "." . "thumb.jpg";
	$convert_cmd = ilUtil::getConvertCmd() . " $imagepath -resize $sizex$size $thumbpath";
	system($convert_cmd);
  header('Content-Type: image/jpeg');
  header('Content-Length: '.filesize($thumbpath));
  readfile($thumbpath);
	system ("rm -f $thumbpath");
?>