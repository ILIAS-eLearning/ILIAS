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
	$tpl = new Template("tpl.mail_frameset.html", false, false);
	$tpl->show();
}
else
{
	header("location: mail.php");
}
?>