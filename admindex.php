<?php
/**
 * admin objects frameset
 * 
 * this file decides if a frameset is used or not
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";

// limit access only to admins
if (!$rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID))
{
	$ilias->raiseError("You are not entitled to access this page!",$ilias->error_obj->WARNING);
}

//look if there is a file tpl.adm.html
$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.adm.html"; 

if (file_exists($startfilename))
{
	$tpl = new ilTemplate("tpl.adm.html", false, false);
	$tpl->show();
}
else
{
	header("location: adm_object.php?expand=1");
	exit;
}
?>
