<?php
	if (!$_GET["gfx"])
		exit();
	$image = $_GET["gfx"];
  header('Content-Type: image/jpeg');
  header('Content-Length: '.filesize($image));
  readfile($image);
	system ("rm -f $image");
?>