<?php

// look for embedded pear
if (is_dir("./pear"))
{
	ini_set("include_path", "./pear:".ini_get("include_path"));
}

?>
