<?php

// First use local pear
set_include_path(
	"./Services/PEAR/lib".PATH_SEPARATOR.
	'./Services/OpenId/lib'.PATH_SEPARATOR.
	ini_get('include_path'));
// look for embedded pear
if (is_dir("./pear"))
{
	set_include_path("./pear".PATH_SEPARATOR.ini_get('include_path'));
}
?>