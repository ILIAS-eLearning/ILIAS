<?php
	if (!$_GET["gfx"]) exit();
	chdir("../..");
	require_once "./include/inc.header.php";
	include_once "./Services/Utilities/classes/class.ilUtil.php";
	// calculate directory of temporary file
	$dirname = dirname(ilUtil::ilTempnam());
	// remove special path symbols from the file name to prevent security hacks
	$image = str_replace("..", "", $_GET["gfx"]);
	$image = str_replace("/", "", $image);
	$image = str_replace("%", "", $image);
	$image = str_replace("\\", "", $image);
	$image = $dirname . "/" . $image;
	$size = getimagesize($image);
	// only proceed if the file is an image
	if (is_array($size) && (strpos($size["mime"], "image") !== FALSE))
	{
		header("Content-Type: " . $size["mime"]);
		header('Content-Length: '.filesize($image));
		readfile($image);
		if (is_file($image))
		{
			// it's a temporary file, delete it after it was shown to save disk space
			unlink ($image);
		}
	}
?>