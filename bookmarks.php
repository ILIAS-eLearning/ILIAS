<?php
/**
* personal bookmark administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
require_once "./include/inc.header.php";

//
// main
//

// determine post or get command
if ($_GET["cmd"] == "post")
{
	$cmd = key($_POST["cmd"]);
}
else
{
	$cmd = $_GET["cmd"];
}
if(empty($cmd))
{
	$cmd = "view";
}
$type = (empty($_POST["type"])) ? $_GET["type"] : $_POST["type"];
if(!empty($type))
{
	$cmd.= $objDefinition->getClassName($type);
}

// call method of BookmarkAdministrationGUI class
require_once "./classes/class.ilBookmarkAdministrationGUI.php";
$bookmarkAdminGUI = new ilBookmarkAdministrationGUI($_GET["bmf_id"]);
$bookmarkAdminGUI->$cmd();

$tpl->show();

?>
