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
  header("Content-Type: image/$type");
  header('Content-Length: '.filesize($image));
  readfile($image);
	if (is_file($image))
	{
		unlink ($image);
	}
?>