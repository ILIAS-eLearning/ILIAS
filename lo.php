<?php
/**
 * learning objects mainpage
 * 
 * this file decides if a frameset is used or not
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";

//look if there is a file tpl.lo.html
$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.lo.html"; 

if (isset($_GET["viewmode"]))
{
	$_SESSION["viewmode"] = $_GET["viewmode"];
}


// TODO: Treeview not implemented yet
if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
{
	$tpl = new Template("tpl.lo.html", false, false);
	$tpl->show();
}
else
{
	header("location: lo_content.php?expand=1");
}

?>