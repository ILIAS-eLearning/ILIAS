<?php
/**
 * startpage for ilias
 * this file decides if a frameset is used or not
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";

//look if there is a file tpl.start.html
$startfilename = $ilias->tplPath.$ilias->account->skin."/tpl.start.html"; 

if (file_exists($startfilename))
{
	$tpl = new Template("tpl.start.html", false, false);
	$tpl->show();
}
else
{
	header("location: usr_personaldesktop.php");
}

?>