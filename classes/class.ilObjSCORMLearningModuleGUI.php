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

require_once "classes/class.ilObjectGUI.php";

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjSCORMLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORMLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "slm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

	}

	function save()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree, $objDefinition;

		parent::save();

		/*
		if ($rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			// create and insert object in objecttree
			require_once("classes/class.ilObjLearningModule.php");
			$newObj = new ilObjSCORMLearningModule();
			$newObj->setType("slm");
			$newObj->setTitle("");
			$newObj->setDescription("");
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);
			$newObj->setPermissions($_GET["ref_id"]);

			unset($newObj);
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?".$this->link_params);
		exit();*/
	}

}
?>