<?php

/*
* output bookmark frameset
*
* @author Alex Killing <alex.killing@gmx.de>
* @package ilias-core
* @version $Id$
*/

require_once "./include/inc.header.php";

//$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.bookmark_frameset.html";

//if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
//{
	$tpl = new ilTemplate("tpl.bookmark_frameset.html", false, false);
/*
	if(isset($_GET["target"]))
	{
		$tpl->setVariable("FRAME_RIGHT_SRC",urldecode($_GET["target"]));
	}
	else
	{
		$tpl->setVariable("FRAME_RIGHT_SRC","mail.php");
	}*/
	$tpl->show();
//}
/*
else
{
	if(isset($_GET["target"]))
	{
		header("location: ".urldecode($_GET["target"]));
	}
	else
	{
		header("location: mail.php");
	}
}*/
?>
