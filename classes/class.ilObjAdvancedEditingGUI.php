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
* Class ilObjAdvancedEditingGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjAdvancedEditingGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "class.ilObjectGUI.php";

class ilObjAdvancedEditingGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	var $conditions;

	function ilObjAdvancedEditingGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $rbacsystem;

		$this->type = "adve";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read_adve"),$this->ilias->error_obj->WARNING);
		}
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "settings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
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

		$this->ctrl->redirect($this);
		//header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		//exit();
	}


	/**
	* display assessment folder settings form
	*/
	function settingsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.advanced_editing.html");
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ADVANCED_EDITING_SETTINGS", $this->lng->txt("advanced_editing_settings"));
		$this->tpl->setVariable("TXT_ALLOW_JAVASCRIPT_EDITOR", $this->lng->txt("advanced_editing_allow_javascript_editor"));
		$this->tpl->setVariable("NO_RTE_EDITOR", $this->lng->txt("advanced_editing_no_rte"));
		$this->tpl->setVariable("TINY_MCE_EDITOR", $this->lng->txt("advanced_editing_tinymce"));
		$editor = $this->object->_getRichTextEditor();
		switch ($editor)
		{
			case "tinymce":
				$this->tpl->setVariable("SELECTED_TINY_MCE_EDITOR", " selected=\"selected\"");
				break;
		}
		
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		$this->tpl->parseCurrentBlock();
	}
	
	function assessmentObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.advanced_editing_assessment.html");
		
		$alltags =& $this->object->getHTMLTags();
		$usedtags =& $this->object->_getUsedHTMLTags("assessment");
		foreach ($alltags as $tag)
		{
			$this->tpl->setCurrentBlock("html_tag_row");
			$this->tpl->setVariable("HTML_TAG", $tag);
			if (is_array($usedtags))
			{
				if (in_array($tag, $usedtags))
				{
					$this->tpl->setVariable("HTML_TAG_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ASSESSMENT_SETTINGS", $this->lng->txt("advanced_editing_assessment_settings"));
		$this->tpl->setVariable("TXT_ALLOW_HTML_TAGS", $this->lng->txt("advanced_editing_allow_html_tags"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		$this->tpl->parseCurrentBlock();
	}
	
	function surveyObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.advanced_editing_survey.html");
		
		$alltags =& $this->object->getHTMLTags();
		$usedtags =& $this->object->_getUsedHTMLTags("survey");
		foreach ($alltags as $tag)
		{
			$this->tpl->setCurrentBlock("html_tag_row");
			$this->tpl->setVariable("HTML_TAG", $tag);
			if (is_array($usedtags))
			{
				if (in_array($tag, $usedtags))
				{
					$this->tpl->setVariable("HTML_TAG_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SURVEY_SETTINGS", $this->lng->txt("advanced_editing_survey_settings"));
		$this->tpl->setVariable("TXT_ALLOW_HTML_TAGS", $this->lng->txt("advanced_editing_allow_html_tags"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save Assessment settings
	*/
	function saveSettingsObject()
	{
		$this->object->_setRichTextEditor($_POST["rte"]);
		sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this,'settings');
	}
	
	function saveAssessmentSettingsObject()
	{
		sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->object->_setUsedHTMLTags($_POST["html_tags"], "assessment");
		$this->ctrl->redirect($this,'assessment');
	}
	
	function saveSurveySettingsObject()
	{
		sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->object->_setUsedHTMLTags($_POST["html_tags"], "survey");
		$this->ctrl->redirect($this,'survey');
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	function addSubtabs(&$tabs_gui)
	{
		$tabs_gui->addSubTabTarget("adve_general_settings",
										 $this->ctrl->getLinkTarget($this, "settings"),
										 array("", "view", "settings", "saveSettings"),
										 "", "");
		if ($this->object->_getRichTextEditor())
		{
			$tabs_gui->addSubTabTarget("adve_assessment_settings",
											 $this->ctrl->getLinkTarget($this, "assessment"),
											 array("assessment", "saveAssessmentSettings"),
											 "", "");
			$tabs_gui->addSubTabTarget("adve_survey_settings",
											 $this->ctrl->getLinkTarget($this, "survey"),
											 array("survey", "saveSurveySettings"),
											 "", "");
		}
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings","","view", "assessment", "survey"), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
		$this->addSubtabs($tabs_gui);
	}
} // END class.ilObjAdvancedEditingGUI
?>
