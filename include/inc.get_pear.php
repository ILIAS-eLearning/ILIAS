<?php

// First use local pear
ini_set('include_path',"./Services/PEAR/lib:".ini_get('include_path'));

// look for embedded pear
if (is_dir("./pear"))
{
	ini_set("include_path", "./pear:".ini_get("include_path"));
}

?>
