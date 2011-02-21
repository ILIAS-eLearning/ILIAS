<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Modules/Category/classes/class.ilObjCategoryGUI.php";

/**
* Class ilObjWorkspaceRootFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjRootFolderGUI.php 27165 2011-01-04 13:48:35Z jluetzen $Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjWorkspaceRootFolderGUI: 
* 
* @extends ilObject2GUI
*/
class ilObjWorkspaceRootFolderGUI extends ilObject2GUI
{
	function getType()
	{
		return "wsrt";
	}

	function setTabs()
	{
		global $lng;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->getAccessHandler()->checkAccess('read', '', $this->node_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		
		if ($this->getAccessHandler()->checkAccess('write', '', $this->node_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			default:
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	/**
	* Render root folder
	*/
	function render()
	{
		global $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$node = $tree->getNodeData($this->node_id);
		$nodes = $tree->getSubTree($node);
		if(sizeof($nodes) > 1)
		{
			// remove current node (== root of subtree)
			array_shift($nodes);

			foreach($nodes as $node)
			{
				var_dump($node["title"]);

			}
		}
	}
	
	/**
	* edit category
	*
	* @access	public
	*/
	function editOLD()
	{
		global $lng;

		if (!$this->getAccessHandler()->checkAccess("write", "", $this->node_id))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"));
		}
		
		$this->ctrl->setParameter($this,"mode","edit");

		// for lang selection include metadata class
		include_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";

		$this->getTemplateFile("edit",'');
		$this->showSortingSettings();
		
		// hide header icon and title
		$this->tpl->setVariable("TXT_HIDE_HEADER_ICON_AND_TITLE", $lng->txt("cntr_hide_title_and_icon"));
		if (ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"))
		{
			$this->tpl->setVariable("CHK_HIDE_ICON", ' checked="checked" ');
		}
		
		$array_push = true;

		if ($_SESSION["error_post_vars"])
		{
			$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
			$_GET["mode"] = "session";
			$array_push = false;
		}

		// load from db if edit category is called the first time
		if (($_GET["mode"] != "session"))
		{
			$data = $this->object->getTranslations();
			$_SESSION["translation_post"] = $data;
			$array_push = false;
		}	// remove a translation from session
		elseif ($_GET["entry"] != 0)
		{
			array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array());

			if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
			{
				$_SESSION["translation_post"]["default_language"] = "";
			}
		}

		$data = $_SESSION["translation_post"];

		// add additional translation form
		if (!$_GET["entry"] and $array_push)
		{
			$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
		}
		else
		{
			$count = count($data["Fobject"]);
		}

		// stripslashes in form?
		$strip = isset($_SESSION["translation_post"]) ? true : false;
		
		// add empty entry, if nothing exists
		if (count($data["Fobject"]) == 0)
		{
			$data["Fobject"][0] =
				array("title"	=> "",
					"desc"	=> "",
					"lang"	=> $lng->getDefaultLanguage()
				);
		}

		foreach ($data["Fobject"] as $key => $val)
		{
			// add translation button
			if ($key == $count -1)
			{
				$this->tpl->setCurrentBlock("addTranslation");
				$this->tpl->setVariable("TXT_ADD_TRANSLATION",$this->lng->txt("add_translation")." >>");
				$this->tpl->parseCurrentBlock();
			}

			// remove translation button
			if ($key != 0)
			{
				$this->tpl->setCurrentBlock("removeTranslation");
				$this->tpl->setVariable("TXT_REMOVE_TRANSLATION",$this->lng->txt("remove_translation"));
				$this->ctrl->setParameter($this, "entry", $key);
				$this->ctrl->setParameter($this, "mode", "edit");
				$this->tpl->setVariable("LINK_REMOVE_TRANSLATION", $this->ctrl->getLinkTarget($this, "removeTranslation"));
				$this->tpl->parseCurrentBlock();
			}

			// lang selection
			$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
			$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");

			$languages = ilMDLanguageItem::_getLanguages();

			foreach ($languages as $code => $language)
			{
				$this->tpl->setCurrentBlock("lg_option");
				$this->tpl->setVariable("VAL_LG", $code);
				$this->tpl->setVariable("TXT_LG", $language);

				if ($code == $val["lang"])
				{
					$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
				}

				$this->tpl->parseCurrentBlock();
			}

			// object data
			$this->tpl->setCurrentBlock("obj_form");

			if ($key == 0)
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("repository"));
			}
			else
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation")." ".$key);
			}

			if ($key == $data["default_language"])
			{
				$this->tpl->setVariable("CHECKED", "checked=\"checked\"");
			}

			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TXT_DESC", $this->lng->txt("title_long"));
			$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
			$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
			$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
			$this->tpl->setVariable("NUM", $key);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->showCustomIconsEditing();

		// global
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function updateOLD()
	{
		global $rbacsystem;
		if (!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$data = $_POST;

			// default language set?
			if (!isset($data["default_language"]))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),$this->ilias->error_obj->MESSAGE);
			}

			// prepare array fro further checks
			foreach ($data["Fobject"] as $key => $val)
			{
				$langs[$key] = $val["lang"];
			}

			$langs = array_count_values($langs);

			// all languages set?
			if (array_key_exists("",$langs) &&
				(count($data["Fobject"]) > 1 || $data["Fobject"][0]["title"] != ""))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_language_selected"),$this->ilias->error_obj->MESSAGE);
			}

			// no single language is selected more than once?
			if (array_sum($langs) > count($langs))
			{
				$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),$this->ilias->error_obj->MESSAGE);
			}

			// copy default translation to variable for object data entry
			$_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
			$_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

			// first delete all translation entries...
			$this->object->removeTranslations();

			// ...and write new translations to object_translation
			foreach ($data["Fobject"] as $key => $val)
			{
				if ($key == $data["default_language"])
				{
					$default = 1;
				}
				else
				{
					$default = 0;
				}

				if (trim($val["title"]) != "")
				{
					$this->object->addTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
				}
			}

			// bring back old translation, if no individual translation is given
			if (trim($_POST["Fobject"]["title"]) == "")
			{
				$_POST["Fobject"]["title"] = "ILIAS";
			}
			
			// update object data entry with default translation
			$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
			
			//save custom icons
			if ($this->ilias->getSetting("custom_icons"))
			{
				$this->object->saveIcons($_FILES["cont_big_icon"]['tmp_name'],
					$_FILES["cont_small_icon"]['tmp_name'], $_FILES["cont_tiny_icon"]['tmp_name']);
			}
			
			$this->update = $this->object->update();
		}

		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		$settings = new ilContainerSortingSettings($this->object->getId());
		$settings->setSortMode((int) $_POST['sorting']);
		$settings->update();
		
		ilContainer::_writeContainerSetting($this->object->getId(), "hide_header_icon_and_title",
			ilUtil::stripSlashes($_POST["hide_header_icon_and_title"]));

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getTargetScript()."?".$this->link_params));
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addTranslation()
	{
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::addTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$_SESSION["translation_post"] = $_POST;
		$this->ctrl->setParameter($this, "entry", 0);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "new_type", $_GET["new_type"]);
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"], "", false, false));
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslation()
	{
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::removeTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$this->ctrl->setParameter($this, "entry", $_GET["entry"]);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "new_type", $_GET["new_type"]);
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"], "", false, false));

	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeBigIcon()
	{
		$_SESSION["translation_post"] = $_POST;
		$this->object->removeBigIcon();
		$this->ctrl->redirect($this, $_GET["mode"]);
	}
	
	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeSmallIcon()
	{

		$_SESSION["translation_post"] = $_POST;
		$this->object->removeSmallIcon();
		$this->ctrl->redirect($this, $_GET["mode"]);
	}
	
	/**
	* remove tiny icon
	*
	* @access	public
	*/
	function removeTinyIcon()
	{

		$_SESSION["translation_post"] = $_POST;
		$this->object->removeTinyIcon();
		$this->ctrl->redirect($this, $_GET["mode"]);
	}
	
	/**
	* goto target group
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "",1))
		{
			$_GET["cmd"] = "frameset";
			$_GET["ref_id"] = 1;
			include("repository.php");
			exit;
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	/**
	 * show sorting settings
	 *
	 * @access protected
	 */
	protected function showSortingSettings()
	{
		$this->tpl->setVariable('TXT_SORTING',$this->lng->txt('sorting_header'));
		$this->tpl->setVariable('TXT_SORT_TITLE',$this->lng->txt('sorting_title_header'));
		$this->tpl->setVariable('INFO_SORT_TITLE',$this->lng->txt('sorting_info_title'));
		$this->tpl->setVariable('TXT_SORT_MANUAL',$this->lng->txt('sorting_manual_header'));
		$this->tpl->setVariable('INFO_SORT_MANUAL',$this->lng->txt('sorting_info_manual'));
		
		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		if($this->getCreationMode())
		{
			$settings = new ilContainerSortingSettings(0);
		}
		else
		{
			$settings = new ilContainerSortingSettings($this->object->getId());
		}
		
		
		$this->tpl->setVariable('RADIO_SORT_TITLE',ilUtil::formRadioButton(
			$settings->getSortMode() == ilContainer::SORT_TITLE,
			'sorting',
			ilContainer::SORT_TITLE));
		$this->tpl->setVariable('RADIO_SORT_MANUAL',ilUtil::formRadioButton(
			$settings->getSortMode() == ilContainer::SORT_MANUAL,
			'sorting',
			ilContainer::SORT_MANUAL));
	}
	
}
?>
