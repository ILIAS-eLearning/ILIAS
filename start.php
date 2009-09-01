<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * startpage for ilias
 * this file decides if a frameset is used or not.
 * Frames set definition is done in 'tpl.start.html'
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
//require_once "./include/inc.header.php";

require_once "./include/inc.header.php";
ilUtil::redirect("index.php");

exit;
//include("index.php");
//exit;


global $ilBench, $ilCtrl;

if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID || !empty($_GET["ref_id"]))
{
	if (empty($_GET["ref_id"]))
	{
		$_GET["ref_id"] = ROOT_FOLDER_ID;
	}
	$ilCtrl->initBaseClass("");
	$ilCtrl->setCmd("frameset");
	$start_script = "repository.php";
}
else
{
	$ilCtrl->initBaseClass("ilPersonalDesktopGUI");
	$start_script = "ilias.php";
}

include($start_script);


?>
