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
*/

include_once "./classes/class.ilObjectGUI.php";

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
		$this->lng->loadLanguageModule('meta');

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

		// put here object specific stuff

		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);

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
	
	/**
	* Display settings for test and assessment.
	*/
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
	
	
	/**
	* Display settings for surveys.
	*/
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
	* Display settings for learning module page JS editor (Currently HTMLArea)
	*/
	function learningModuleObject()
	{
		global $ilSetting;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.advanced_editing_learning_module.html");
				
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_LM_SETTINGS", $this->lng->txt("advanced_editing_lm_settings"));
		$this->tpl->setVariable("TXT_LM_JS_EDITING", $this->lng->txt("advanced_editing_lm_js_editing"));
		$this->tpl->setVariable("TXT_LM_JS_EDITING_DESC", $this->lng->txt("advanced_editing_lm_js_editing_desc"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if ($ilSetting->get("enable_js_edit"))
		{
			$this->tpl->setVariable("JS_EDIT", "checked=\"checked\"");
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* Save settings for learning module JS editing.
	*/
	function saveLearningModuleSettingsObject()
	{
		global $ilSetting;

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		$ilSetting->set("enable_js_edit", $_POST["js_edit"]);
		$this->ctrl->redirect($this, 'learningmodule');
	}

	/**
	* Display settings for categories.
	*/
	function categoryObject()
	{
		global $ilSetting, $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.advanced_editing_category.html");
				
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CAT_SETTINGS", $this->lng->txt("adve_cat_settings"));
		$this->tpl->setVariable("TXT_CAT_PAGE_EDITING", $this->lng->txt("advanced_editing_cat_page_editing"));
		$this->tpl->setVariable("TXT_CAT_PAGE_EDITING_DESC", $this->lng->txt("advanced_editing_cat_page_editing_desc"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		$this->tpl->setVariable("TXT_CAT_PAGES",
			$this->lng->txt("cat_pages"));
		$this->tpl->setVariable("TXT_UNDO_LAST_CAT_CHANGES",
			$this->lng->txt("cat_undo_last_page_changes"));
		$this->tpl->setVariable("TXT_CLEAR_CAT_PAGE",
			$this->lng->txt("cat_clear_page"));
			
		$root_node = $tree->getNodeData(ROOT_FOLDER_ID);
		$nodes = $tree->getSubTree($root_node, true, "cat");
		$cats[0] = $this->lng->txt("please_select");
		$cats[$root_node["ref_id"]] = "- ".$this->lng->txt("repository");
		foreach($nodes as $node)
		{
			$cats[$node["ref_id"]] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",($node["depth"]-1))."- ".$node["title"];
		}
		$this->tpl->setVariable("SELECT_CATEGORY",
			ilUtil::formSelect("", "cat_id", $cats, false, true));

		if ($ilSetting->get("enable_cat_page_edit"))
		{
			$this->tpl->setVariable("CAT_PAGE_EDIT", "checked=\"checked\"");
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* Save settings for category editing
	*/
	function saveCategorySettingsObject()
	{
		global $ilSetting;

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$ilSetting->set("enable_cat_page_edit", $_POST["cat_page_edit"]);
		$this->ctrl->redirect($this, 'category');
	}

	/**
	* Save settings for category editing
	*/
	function undoLastCategoryChangeObject()
	{
		global $ilSetting;

		include_once("classes/class.ilContainer.php");
		$xpage_id = ilContainer::_lookupContainerSetting(
			ilObject::_lookupObjId($_POST["cat_id"]),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			ilUtil::sendInfo($this->lng->txt("cat_pages_undone"), true);
			include_once("./Services/XHTMLPage/classes/class.ilXHTMLPage.php");
			$xpage = new ilXHTMLPage($xpage_id);
			$xpage->undo();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cat_pages_not_created"), true);
		}

		$this->ctrl->redirect($this, 'category');
	}

	/**
	* Save settings for category editing
	*/
	function clearCategoryPageObject()
	{
		global $ilSetting;

		include_once("./classes/class.ilContainer.php");
		$xpage_id = ilContainer::_lookupContainerSetting(
			ilObject::_lookupObjId($_POST["cat_id"]),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			ilUtil::sendInfo($this->lng->txt("cat_pages_clear"), true);
			include_once("./Services/XHTMLPage/classes/class.ilXHTMLPage.php");
			$xpage = new ilXHTMLPage($xpage_id);
			$xpage->clear();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cat_pages_not_created"), true);
		}

		$this->ctrl->redirect($this, 'category');
	}

	/**
	* Save Assessment settings
	*/
	function saveSettingsObject()
	{
		$this->object->_setRichTextEditor($_POST["rte"]);
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this,'settings');
	}
	
	function saveAssessmentSettingsObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->object->_setUsedHTMLTags($_POST["html_tags"], "assessment");
		$this->ctrl->redirect($this,'assessment');
	}
	
	function saveSurveySettingsObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->object->_setUsedHTMLTags($_POST["html_tags"], "survey");
		$this->ctrl->redirect($this,'survey');
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	function addSubtabs(&$tabs_gui)
	{
		global $ilCtrl;
		
		if ($ilCtrl->getNextClass() != "ilpermissiongui")
		{
			$tabs_gui->addSubTabTarget("adve_general_settings",
											 $this->ctrl->getLinkTarget($this, "settings"),
											 array("", "view", "settings", "saveSettings"),
											 "", "");
			$tabs_gui->addSubTabTarget("adve_assessment_settings",
											 $this->ctrl->getLinkTarget($this, "assessment"),
											 array("assessment", "saveAssessmentSettings"),
											 "", "");
			$tabs_gui->addSubTabTarget("adve_survey_settings",
											 $this->ctrl->getLinkTarget($this, "survey"),
											 array("survey", "saveSurveySettings"),
											 "", "");
			$tabs_gui->addSubTabTarget("adve_cat_settings",
											 $this->ctrl->getLinkTarget($this, "category"),
											 array("category", "saveCategorySettings"),
											 "", "");
			$tabs_gui->addSubTabTarget("adve_lm_settings",
											 $this->ctrl->getLinkTarget($this, "learningModule"),
											 array("learningModule", "saveLearningModuleSettings"),
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
				$this->ctrl->getLinkTarget($this, "settings"),
					array("settings","","view", "assessment", "survey", "learningModule",
					"category"), "", "");
			
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
