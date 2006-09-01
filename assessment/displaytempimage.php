<?php
	if (!$_GET["gfx"])
		exit();
	$image = $_GET["gfx"];
	if (strlen($_GET["type"]))
	{
		$type = $_GET["type"];
	}
	else
	{
		$type = "jpeg";
	}
	$size = getimagesize($image);
	if (is_array($size) && (strpos($size["mime"], "image") !== FALSE))
	{
		header("Content-Type: image/$type");
		header('Content-Length: '.filesize($image));
		readfile($image);
		if (is_file($image))
		{
			unlink ($image);
		}
	}
?>