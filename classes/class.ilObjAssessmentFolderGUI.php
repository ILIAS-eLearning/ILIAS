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
		global $tpl,$lng,$ilias;
/*
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tracking_settings.html");
		$tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
			"&cmd=gateway");
		$tpl->setVariable("TXT_TRACKING_SETTINGS", $this->lng->txt("tracking_settings"));
		$tpl->setVariable("TXT_ACTIVATE_TRACKING", $this->lng->txt("activate_tracking"));
		$tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("save_user_related_data"));
		$tpl->setVariable("TXT_NUMBER_RECORDS", $this->lng->txt("number_of_records"));
		$tpl->setVariable("NUMBER_RECORDS", $this->object->getRecordsTotal());
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if($this->object->_enabledTracking())
		{
			$this->tpl->setVariable("ACT_TRACK_CHECKED", " checked=\"1\" ");
		}

		if($this->object->_enabledUserRelatedData())
		{
			$this->tpl->setVariable("USER_RELATED_CHECKED", " checked=\"1\" ");
		}

		$tpl->parseCurrentBlock();
*/
	}

	/**
	* display assessment folder logs form
	*/
	function logsObject()
	{
	}
	
} // END class.ilObj<module_name>
?>
