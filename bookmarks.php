<?php
/**
* bookmark view
*
* @author Peter Gabriel <pgabriel@databay.de>
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

// call method of BookmarkManager
require_once "./classes/class.ilBookmarkFolderGUI.php";
$bookmarkFolderGUI = new ilBookmarkFolderGUI($_GET["bmf_id"]);
$bookmarkFolderGUI->$cmd();

$tpl->show();

?>
