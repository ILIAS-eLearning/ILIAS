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
* Class ilObjAssessmentFolderGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjAssessmentFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	var $conditions;

	function ilObjAssessmentFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $rbacsystem;

		$this->type = "assf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);

		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"),$this->ilias->error_obj->WARNING);
		}

	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);

		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}


	/**
	* display assessment folder settings form
	*/
	function settingsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.assessment_settings.html");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		$this->tpl->setVariable("TXT_ACTIVATE_ASSESSMENT_LOGGING", $this->lng->txt("activate_assessment_logging"));
		$this->tpl->setVariable("TXT_ASSESSMENT_SETTINGS", $this->lng->txt("assessment_settings"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if($this->object->_enabledAssessmentLogging())
		{
			$this->tpl->setVariable("ASSESSMENT_LOGGING_CHECKED", " checked=\"checked\"");
		}

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save Assessment settings
	*/
	function saveSettingsObject()
	{
		if ($_POST["chb_assessment_logging"] == 1)
		{
			$this->object->_enableAssessmentLogging(1);
		}
		else
		{
			$this->object->_enableAssessmentLogging(0);
		}
		sendInfo($this->lng->txt("msg_obj_modified"));
		$this->settingsObject();
	}

	/**
	* display assessment folder logs form
	*/
	function logsObject()
	{
	}
	
} // END class.ilObj<module_name>
?>
