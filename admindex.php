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
require_once "./include/ilias_header.inc";

//look if there is a file tpl.adm.html
$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.adm.html"; 

if (file_exists($startfilename))
{
	$tpl = new Template("tpl.adm.html", false, false);
	$tpl->show();
}
else
{
	header("location: content.php?expand=1");
}

?>