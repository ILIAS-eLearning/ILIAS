<?php
/**
* groups
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilObjGroupGUI.php";



$newGroup = new ilObjGroupGUI(array(), $ref_id, true);
$method = $cmd;
$editObject = "showDetails";
$newGroup->$editObject();
$tpl->show();

?>
