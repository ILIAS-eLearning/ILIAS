<?php

	chdir("..");
	require_once "./include/inc.header.php";
	require_once "./classes/class.ilUtil.php";
	
	if (!$_GET["gfx"])
		exit();
	$image = $_GET["gfx"];
  header('Content-Type: image/jpeg');
  header('Content-Length: '.filesize($image));
  readfile($image);
	system ("rm -f $image");
?>