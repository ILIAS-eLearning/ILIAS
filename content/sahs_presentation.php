<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* scorm learning module presentation script
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";
require_once "classes/class.ilObjectGUI.php";
require_once "./content/classes/class.ilObjSAHSLearningModule.php";

$lng->loadLanguageModule("content");

$ref_id=$_GET["ref_id"];

if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
}

$obj_id = ilObject::_lookupObjectId($ref_id);
$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);


// PAYMENT STUFF
// check if object is purchased
include_once './payment/classes/class.ilPaymentObject.php';
include_once './classes/class.ilSearch.php';

if(!ilPaymentObject::_hasAccess($_GET['ref_id']))
{
	ilUtil::redirect('../payment/start_purchase.php?ref_id='.$_GET['ref_id']);
}
if(!ilSearch::_checkParentConditions($_GET['ref_id']))
{
	$ilias->error_obj->raiseError($lng->txt('access_denied'),$ilias->error_obj->WARNING);
}


switch ($type)
{
	case "scorm":
				//SCORM
				require_once "./content/classes/SCORM/class.ilSCORMPresentationGUI.php";
				$scorm_presentation = new ilSCORMPresentationGUI();
				break;

	case "aicc":
				//AICC
				require_once "./content/classes/AICC/class.ilAICCPresentationGUI.php";
				$aicc_presentation = new ilAICCPresentationGUI();
				break;

	case "hacp":
				//HACP
				require_once "./content/classes/HACP/class.ilHACPPresentationGUI.php";
				$hacp_presentation = new ilHACPPresentationGUI();
				break;
	default:

		//unknown type
		require_once "./content/classes/class.ilLMPresentationGUI.php";
		$lm_presentation = new ilLMPresentationGUI();

}


?>
