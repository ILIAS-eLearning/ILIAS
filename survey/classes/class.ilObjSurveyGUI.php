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
* Class ilObjSurveyGUI
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObjectGUI.php";
require_once "classes/class.ilMetaDataGUI.php";
require_once "classes/class.ilUtil.php";

class ilObjSurveyGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
    		global $lng;
		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->setTabTargetScript("questionpool.php");
		if ($a_prepare_output) {
			$this->prepareOutput();
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
		
		header("Location:".$this->getReturnLocation("save","questionpool.php?".$this->link_params));
		exit();
	}

/**
* Returns the GET parameters for the survey object URLs
*
* Returns the GET parameters for the survey object URLs
*
* @access public
*/
  function getAddParameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }
	
/**
* Creates the properties form for the survey object
*
* Creates the properties form for the survey object
*
* @access public
*/
  function propertiesObject()
  {
		global $rbacsystem;
		
    $add_parameter = $this->getAddParameter();
    if ($_POST["cmd"]["save"]) {
			$this->updateObject();
      sendInfo($this->lng->txt("msg_obj_modified"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
    }
    if ($_POST["cmd"]["apply"]) {
			$this->updateObject();
      sendInfo($this->lng->txt("msg_obj_modified"));
    }
    if ($_POST["cmd"]["cancel"]) {
      sendInfo($this->lng->txt("msg_cancel"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
      exit();
    }

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_properties.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
//		$this->tpl->setVariable("VALUE_AUTHOR", $this->object->getAuthor());
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("VALUE_DESCRIPTION", $this->object->getDescription());
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("introduction"));
//		$this->tpl->setVariable("VALUE_INTRODUCTION", $this->object->getIntroduction());
		$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("status"));
		$this->tpl->setVariable("TEXT_START_DATE", $this->lng->txt("start_date"));
		$this->tpl->setVariable("VALUE_START_DATE", ilUtil::makeDateSelect("start_date", "", "", ""));
		$this->tpl->setVariable("TEXT_END_DATE", $this->lng->txt("end_date"));
		$this->tpl->setVariable("VALUE_END_DATE", ilUtil::makeDateSelect("end_date", "", "", ""));
		$this->tpl->setVariable("TEXT_EVALUATION_ACCESS", $this->lng->txt("evaluation_access"));
		$this->tpl->setVariable("VALUE_OFFLINE", $this->lng->txt("offline"));
		$this->tpl->setVariable("VALUE_ONLINE", $this->lng->txt("online"));
		$this->tpl->setVariable("TEXT_ENABLED", $this->lng->txt("enabled"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    if ($rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
    $this->tpl->parseCurrentBlock();
  }
	
} // END class.ilObjSurveyGUI
?>
