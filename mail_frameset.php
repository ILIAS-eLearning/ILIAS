<?php
/**
 * mail mainpage
 * 
 * this file shows two frames (mail_menu.php, mail.php)
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";

$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.mail_frameset.html"; 

if (isset($_GET["viewmode"]))
{
	$_SESSION["viewmode"] = $_GET["viewmode"];
}
if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
{
	$tpl = new ilTemplate("tpl.mail_frameset.html", false, false);
	if(isset($_GET["target"]))
	{
		$tpl->setVariable("FRAME_RIGHT_SRC",urldecode($_GET["target"]));
	}
	else
	{
		$tpl->setVariable("FRAME_RIGHT_SRC","mail.php");
	}
	$tpl->show();
}
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
}
?>
