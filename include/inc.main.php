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

//output
include_once("HTML/IT.php");
$TPLPATH = "./templates";

$tplmain = new IntegratedTemplate($TPLPATH);
if (file_exists($TPLPATH."/"."tpl.main.html"))
{
   $tplmain->loadTemplateFile("tpl.main.html", false, false);
}
else
{
   die ("Maintemplate doesn't exist");
}

?>