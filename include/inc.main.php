<?php

/**
* displays variable
*/
function vd($mixed)
{
	echo "<pre>";
	var_dump($mixed);
	echo "</pre>";
} //end function

$tplmain = new Template("tpl.main.html", false, false);

?>