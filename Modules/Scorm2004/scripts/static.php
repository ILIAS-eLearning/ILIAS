<?

/*
	
	this will later send all static js in one compressed file
	for speeding things a little bit up  
	
*/

$srcs = 'remoting.js scormapi.js gui.js sequencing.js player.js';

header("Content-Type: application/x-javascript; charset=UTF-8");
ob_start("ob_gzhandler");
session_cache_limiter('public');

foreach(explode(' ', $srcs) as $src)
{
	print("\n/*** include($src) ***/\n");
	include($src);
}

?>