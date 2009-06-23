<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
* Class ilObjCategoryGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjCategoryGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI, ilObjUserGUI, ilObjUserFolderGUI
* @ilCtrl_Calls ilObjCategoryGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjCategoryGUI: ilColumnGUI
* 
* @ingroup ModulesCategory
*/
class ilObjCategoryGUI extends ilContainerGUI
{
	var $ctrl;

	/**
	* Constructor
	* @access public
	*/
	function ilObjCategoryGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		//global $ilCtrl;

		// CONTROL OPTIONS
		//$this->ctrl =& $ilCtrl;
		//$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "cat";
		$this->ilContainerGUI($a_data,(int) $a_id,$a_call_by_reference,false);
	}

	function &executeCommand()
	{
		global $rbacsystem, $ilNavigationHistory, $ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			case "ilobjusergui":
				include_once('./Services/User/classes/class.ilObjUserGUI.php');
				
				$this->tabs_gui->setTabActive('administrate_users');
				if(!$_GET['obj_id'])
				{
					$this->gui_obj = new ilObjUserGUI("",$_GET['ref_id'],true, false);
					$this->gui_obj->setCreationMode($this->creation_mode);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				else
				{
					$this->gui_obj = new ilObjUserGUI("", $_GET['obj_id'],false, false);
					$this->gui_obj->setCreationMode($this->creation_mode);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				break;

			case "ilobjuserfoldergui":
				include_once('./Services/User/classes/class.ilObjUserFolderGUI.php');

				$this->tabs_gui->setTabActive('administrate_users');
				$this->gui_obj = new ilObjUserFolderGUI("",(int) $_GET['ref_id'],true, false);
				$this->gui_obj->setUserOwnerId((int) $_GET['ref_id']);
				$this->gui_obj->setCreationMode($this->creation_mode);
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				break;
				
			case "ilcolumngui":
				$this->checkPermission("read");
				$this->prepareOutput();
				//$this->getSubItems();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->renderObject();
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->infoScreen();
				break;
				
			case 'ilcontainerlinklistgui':
				include_once("./classes/class.ilContainerLinkListGUI.php");
				$link_list_gui =& new ilContainerLinkListGUI();
				$ret =& $this->ctrl->forwardCommand($link_list_gui);
				break;

			// container page editing
			case "ilpageobjectgui":
				$this->checkPermission("write");
				$this->prepareOutput(false);
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;
				
			default:
				$this->checkPermission("visible");
				
				// add entry to navigation history
				if (!$this->getCreationMode() &&
					$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
				{
					$ilNavigationHistory->addItem($_GET["ref_id"],
						"repository.php?cmd=frameset&ref_id=".$_GET["ref_id"], "cat");
				}

				$this->prepareOutput();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));

				if(!$cmd)
				{
					$cmd = "render";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}

	/**
	* Get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $lng;

		if ($this->ctrl->getCmd() == "editPageContent")
		{
			return;
		}
		#$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "render")
				? true
				: false;
			$tabs_gui->addTab("view_content", $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));

			//BEGIN ChangeEvent add info tab to category object
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjcategorygui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary","", "infoScreen"),
				 "", "", $force_active);
			//END ChangeEvent add info tab to category object
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}

		if($rbacsystem->checkAccess('cat_administrate_users',$this->ref_id))
		{
			$tabs_gui->addTarget("administrate_users",
				$this->ctrl->getLinkTarget($this, "listUsers"), "listUsers", get_class($this));
		}
		
		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

	}

	/**
	* Render category
	*/
	function renderObject()
	{
		global $ilTabs;
		
		$ilTabs->activateTab("view_content");
		$ret =  parent::renderObject();
		return $ret;

	}

	/**
	* create new category form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			//add template for buttons
			$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

			// only in administration
			// to do: make this in repository work
			if (false)
			{
				$this->tpl->setCurrentBlock("btn_cell");
				$this->tpl->setVariable("BTN_LINK",
				$this->ctrl->getLinkTarget($this, "importCategoriesForm"));
				$this->tpl->setVariable("BTN_TXT", $this->lng->txt("import_categories"));
				$this->tpl->parseCurrentBlock();
			}

			$this->getTemplateFile("edit",$new_type);

			$array_push = true;

			if ($_SESSION["error_post_vars"])
			{
				$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
				$array_push = false;
			}

			// clear session data if a fresh category should be created
			if (($_GET["mode"] != "session"))
			{
				unset($_SESSION["translation_post"]);
			}	// remove a translation from session
			elseif ($_GET["entry"] != 0)
			{
				array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array());

				if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
				{
					$_SESSION["translation_post"]["default_language"] = "";
				}
			}

			// stripslashes in form output?
			$strip = isset($_SESSION["translation_post"]) ? true : false;

			$data = $_SESSION["translation_post"];

			if (!is_array($data["Fobject"]))
			{
				$data["Fobject"] = array();
			}

			// add additional translation form
			if (!$_GET["entry"] and $array_push)
			{
				$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
			}
			else
			{
				$count = count($data["Fobject"]);
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
					$this->ctrl->setParameter($this, "new_type", $new_type);
					$this->ctrl->setParameter($this, "mode", "create");
					$this->tpl->setVariable("LINK_REMOVE_TRANSLATION", $this->ctrl->getLinkTarget($this, "removeTranslation"));

					$this->tpl->parseCurrentBlock();
				}

				// lang selection
				$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
				$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");

				include_once('Services/MetaData/classes/class.ilMDLanguageItem.php');
				$languages = ilMDLanguageItem::_getLanguages();

				foreach($languages as $code => $language)
				{
					$this->tpl->setCurrentBlock("lg_option");
					$this->tpl->setVariable("VAL_LG", $code);
					$this->tpl->setVariable("TXT_LG", $language);

					if ($count == 1 AND $code == $this->ilias->account->getPref("language") AND !isset($_SESSION["translation_post"]))
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}
					elseif ($code == $val["lang"])
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}

					$this->tpl->parseCurrentBlock();
				}

				if ($key == 0)
				{
					$this->tpl->setCurrentBlock("type_image");
					$this->tpl->setVariable("TYPE_IMG",
						ilUtil::getImagePath("icon_cat.gif"));
					$this->tpl->parseCurrentBlock();
				}
				
				// object data
				$this->tpl->setCurrentBlock("obj_form");

				if ($key == 0)
				{
					$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
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
				$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
				$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
				$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
				$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
				$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
				$this->tpl->setVariable("NUM", $key);
				$this->tpl->parseCurrentBlock();
			}

			// global
			$this->ctrl->setParameter($this, "mode", "create");
			$this->ctrl->setParameter($this, "new_type", $new_type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "save"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->showSortingSettings();
		}
		
		$this->fillCloneTemplate('CLONE_WIZARD','cat');
	}

	/**
	* save category
	* @access	public
	*/
	function saveObject()
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
		if (array_key_exists("",$langs))
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

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)
		//$roles = $newObj->initDefaultRoles();

		// write translations to object_translation
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

			$newObj->addTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
		}
		
		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		$settings = new ilContainerSortingSettings($newObj->getId());
		$settings->setSortMode((int) $_POST['sorting']);
		$settings->save();
		

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("cat_added"),true);
		//$this->ctrl->setParameter($this, "ref_id", $newObj->getRefId());
		
		// BEGIN ChangeEvent: Record object creation
		global $ilUser;
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			ilChangeEvent::_recordWriteEvent($newObj->getId(), $ilUser->getId(), 'create');
		}
		// END ChangeEvent: Record object creation

		$this->redirectToRefId($_GET["ref_id"]);
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilCtrl;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}

		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		if ($ilCtrl->getNextClass() == "ilinfoscreengui")
		{
			$ilCtrl->forwardCommand($info);
		}
		else
		{
			return $ilCtrl->getHTML($info);
		}
	}
	
	/**
	 * Edit extended category settings
	 *
	 * @access protected
	 */
	protected function editInfoObject()
	{
		$this->checkPermission("write");
		$this->getSubTabs('edit');
		$this->tabs_gui->setTabActive('edit_properties');
		$this->tabs_gui->setSubTabActive('edit_cat_settings');
		
		$this->initExtendedSettings();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Update info (extended meta data) 
	 * 
	 * @access protected
	 */
	protected function updateInfoObject()
	{
		$this->checkPermission("write");
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,
			'crs',$this->object->getId());
		$record_gui->loadFromPost();
		$record_gui->saveValues();

		ilUtil::sendSuccess($this->lng->txt("settings_saved"));
		$this->editInfoObject();
		return true;
	}
	
	
	/**
	 * build property form for extended category settings
	 *
	 * @access protected
	 */
	protected function initExtendedSettings()
	{
		if(is_object($this->form))
		{
			return true;
		}
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt('ext_cat_settings'));
		$this->form->addCommandButton('updateInfo',$this->lng->txt('save'));
		$this->form->addCommandButton('editInfo',$this->lng->txt('cancel'));

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'cat',$this->object->getId());
		$record_gui->setPropertyForm($this->form);
		$record_gui->parse();
		
		return true;
	}
	

	/**
	* edit category
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->getSubTabs('edit');
		$this->ctrl->setParameter($this,"mode","edit");

		// for lang selection include metadata class
		include_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";

		$this->getTemplateFile("edit",'');
		$this->showSortingSettings();
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

		foreach ((array) $data["Fobject"] as $key => $val)
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
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
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
			$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
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
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "update"));
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
	function updateObject()
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
			if (array_key_exists("",$langs))
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

				$this->object->addTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
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

		// BEGIN ChangeEvent: Record update
		global $ilUser;
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
		}
		// END ChangeEvent: Record update

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getTargetScript()."?".$this->link_params));
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addTranslationObject()
	{
		$this->checkPermission("write");
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::addTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$_SESSION["translation_post"] = $_POST;
		$this->ctrl->setParameter($this, "entry", 0);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "new_type", $_GET["new_type"]);
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
		$this->checkPermission("write");
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::removeTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$this->ctrl->setParameter($this, "entry", $_GET["entry"]);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "new_type", $_GET["new_type"]);
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));

	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeBigIconObject()
	{
		$this->checkPermission("write");
		$_SESSION["translation_post"] = $_POST;
		$this->object->removeBigIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}
	
	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeSmallIconObject()
	{
		$this->checkPermission("write");
		$_SESSION["translation_post"] = $_POST;
		$this->object->removeSmallIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeTinyIconObject()
	{
		$this->checkPermission("write");
		$_SESSION["translation_post"] = $_POST;
		$this->object->removeTinyIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}

	/**
	* display form for category import
	*/
	function importCategoriesFormObject ()
	{
		ilObjCategoryGUI::_importCategoriesForm($this->ref_id, $this->tpl);
	}

	/**
	* display form for category import (static, also called by RootFolderGUI)
	*/
	function _importCategoriesForm ($a_ref_id, &$a_tpl)
	{
		global $lng, $rbacreview;

		$a_tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.cat_import_form.html",
			"Modules/Category");

		$a_tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$a_tpl->setVariable("TXT_IMPORT_CATEGORIES", $lng->txt("import_categories"));
		$a_tpl->setVariable("TXT_HIERARCHY_OPTION", $lng->txt("import_cat_localrol"));
		$a_tpl->setVariable("TXT_IMPORT_FILE", $lng->txt("import_file"));
		$a_tpl->setVariable("TXT_IMPORT_TABLE", $lng->txt("import_cat_table"));

		$a_tpl->setVariable("BTN_IMPORT", $lng->txt("import"));
		$a_tpl->setVariable("BTN_CANCEL", $lng->txt("cancel"));

		// NEED TO FILL ADOPT_PERMISSIONS HTML FORM....
		$parent_role_ids = $rbacreview->getParentRoleIds($a_ref_id,true);
		
		// sort output for correct color changing
		ksort($parent_role_ids);
		
		foreach ($parent_role_ids as $key => $par)
		  {
		    if ($par["obj_id"] != SYSTEM_ROLE_ID)
		      {
			$check = ilUtil::formCheckbox(0,"adopt[]",$par["obj_id"],1);
			$output["adopt"][$key]["css_row_adopt"] = ilUtil::switchColor($key, "tblrow1", "tblrow2");
			$output["adopt"][$key]["check_adopt"] = $check;
			$output["adopt"][$key]["role_id"] = $par["obj_id"];
			$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
			$output["adopt"][$key]["role_name"] = $par["title"];
		      }
		  }
		
		//var_dump($output);

		// BEGIN ADOPT PERMISSIONS
		foreach ($output["adopt"] as $key => $value)
		  {
		    $a_tpl->setCurrentBlock("ADOPT_PERM_ROW");
		    $a_tpl->setVariable("CSS_ROW_ADOPT",$value["css_row_adopt"]);
		    $a_tpl->setVariable("CHECK_ADOPT",$value["check_adopt"]);
		    $a_tpl->setVariable("LABEL_ID",$value["role_id"]);
		    $a_tpl->setVariable("TYPE",$value["type"]);
		    $a_tpl->setVariable("ROLE_NAME",$value["role_name"]);
		    $a_tpl->parseCurrentBlock();
		  }
	}


	/**
	* import cancelled
	*
	* @access private
	*/
	function importCancelledObject()
	{
		$this->ctrl->redirect($this);
	}

	/**
	* get user import directory name
	*/
	function _getImportDir()
	{
		return ilUtil::getDataDir()."/cat_import";
	}

	/**
	* import categories
	*/
	function importCategoriesObject()
	{
		ilObjCategoryGUI::_importCategories($_GET["ref_id"]);
		// call to importCategories with $withrol = 0
		ilObjCategoryGUI::_importCategories($_GET["ref_id"], 0);
	}
	
        /**
	 * import categories with local rol
	 */
	function importCategoriesWithRolObject()
	{
	
	  //echo "entra aqui";
	  // call to importCategories with $withrol = 1
	  ilObjCategoryGUI::_importCategories($_GET["ref_id"], 1);
	}

	/**
	* import categories (static, also called by RootFolderGUI)
	*/
	
	function _importCategories($a_ref_id, $withrol_tmp)	
	{
		global $lng;

		require_once("./Modules/Category/classes/class.ilCategoryImportParser.php");

		$import_dir = ilObjCategoryGUI::_getImportDir();

		// create user import directory if necessary
		if (!@is_dir($import_dir))
		{
			ilUtil::createDirectory($import_dir);
		}

		// move uploaded file to user import directory

		$file_name = $_FILES["importFile"]["name"];

		// added to prevent empty file names
		if (!strcmp($file_name,"")) {
		  ilUtil::sendFailure($lng->txt("no_import_file_found"), true);
		  $this->ctrl->redirect($this);
		}

		$parts = pathinfo($file_name);
		$full_path = $import_dir."/".$file_name;
		//move_uploaded_file($_FILES["importFile"]["tmp_name"], $full_path);
		ilUtil::moveUploadedFile($_FILES["importFile"]["tmp_name"], $file_name, $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		$subdir = basename($parts["basename"],".".$parts["extension"]);
		$xml_file = $import_dir."/".$subdir."/".$subdir.".xml";
		// CategoryImportParser
		//var_dump($_POST);
		$importParser = new ilCategoryImportParser($xml_file, $a_ref_id, $withrol_tmp);
		$importParser->startParsing();

		ilUtil::sendSuccess($lng->txt("categories_imported"), true);
		$this->ctrl->redirect($this);
	}

	function applyFilterObject()
	{
		unset($_GET['offset']);
		unset($_SESSION['lua_offset'][$this->object->getRefId()]);
		$this->listUsersObject();
	}

	// METHODS for local user administration
	function listUsersObject($show_delete = false)
	{
		global $ilUser,$rbacreview;

		include_once './Services/User/classes/class.ilLocalUser.php';
		include_once './Services/User/classes/class.ilObjUserGUI.php';

		global $rbacsystem,$rbacreview;

		if(!$rbacsystem->checkAccess("cat_administrate_users",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_admin_users"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tabs_gui->setTabActive('administrate_users');


		$_GET['sort_by'] = ($_SESSION['lua_sort_by'][$this->object->getRefId()] = 
							($_GET['sort_by'] ? $_GET['sort_by'] : $_SESSION['lua_sort_by'][$this->object->getRefId()]));
		$_GET['sort_order'] = $_SESSION['lua_sort_order'][$this->object->getRefId()] = 
			($_GET['sort_order'] ? $_GET['sort_order'] : $_SESSION['lua_sort_order'][$this->object->getRefId()]);
		$_GET['offset'] = $_SESSION['lua_offset'][$this->object->getRefId()] = 
			(isset($_GET['offset']) ? $_GET['offset'] : $_SESSION['lua_offset'][$this->object->getRefId()]);


		// default to local users view
		if(!isset($_SESSION['filtered_users'][$this->object->getRefId()]))
		{
			$_SESSION['filtered_users'][$this->object->getRefId()] = $this->object->getRefId();
		}

		$_SESSION['delete_users'] = $show_delete ? $_SESSION['delete_users'] : array();
		$_SESSION['filtered_users'][$this->object->getRefId()] = isset($_POST['filter']) ? 
			$_POST['filter'] : 
			$_SESSION['filtered_users'][$this->object->getRefId()];

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.cat_admin_users.html',
			"Modules/Category");
		$parent = ilLocalUser::_getFolderIds();
		if(count($parent) > 1)
		{
			$this->tpl->setCurrentBlock("filter");
			$this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
			$this->tpl->setVariable("SELECT_FILTER",$this->__buildFilterSelect($parent));
			$this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("FILTER_NAME",'applyFilter');
			$this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		if(count($rbacreview->getGlobalAssignableRoles()) or in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			// add user button
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilobjusergui','create'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('add_user'));
			$this->tpl->parseCurrentBlock();

			// import user button
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilobjuserfoldergui','importUserForm'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('import_users'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
		}
		if(!count($users = ilLocalUser::_getAllUserIds($_SESSION['filtered_users'][$this->object->getRefId()])))
		{
			ilUtil::sendInfo($this->lng->txt('no_local_users'));
		}


		if($show_delete)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeleteUsers');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}
		
		$counter = 0;
		$editable = false;

		// pre sort
		$users = ilLocalUser::_getUserData($_SESSION['filtered_users'][$this->object->getRefId()]);
		$this->all_users_count = count($users);

		$users = ilUtil::sortArray($users,$_GET["sort_by"] ? $_GET['sort_by'] : 'login',$_GET["sort_order"]);
		$users = array_slice($users,$_GET["offset"],$ilUser->getPref('hits_per_page'));

		foreach($users as $user_data)
		{
			if($user_data['time_limit_owner'] == $this->object->getRefId())
			{
				$editable = true;
				$f_result[$counter][]	= ilUtil::formCheckbox(in_array($user_data['usr_id'],$_SESSION['delete_users']) ? 1 : 0,
															   "user_ids[]",$user_data['usr_id']);

				$this->ctrl->setParameterByClass('ilobjusergui','obj_id',$user_data['usr_id']);
				$f_result[$counter][]	= '<a  href="'.$this->ctrl->getLinkTargetByClass('ilobjusergui','edit').'">'.
					$user_data['login'].'</a>';
			}
			else
			{
				$f_result[$counter][]	= '&nbsp;';
				$f_result[$counter][]	= $user_data['login'];
			}

			$f_result[$counter][]	= $user_data['firstname'];
			$f_result[$counter][]	= $user_data['lastname'];

			
			switch($user_data['time_limit_owner'])
			{
				case 7:
					$f_result[$counter][]	= $this->lng->txt('global');
					break;

				default:
					$f_result[$counter][] = ($title = ilObject::_lookupTitle(ilObject::_lookupObjId($user_data['time_limit_owner']))) ?
						$title : '';
			}
			
			// role assignment
			$this->ctrl->setParameter($this,'obj_id',$user_data['usr_id']);
			$f_result[$counter][]	= '<a class="il_ContainerItemCommand" href="'.$this->ctrl->getLinkTarget($this,'assignRoles').'">'.
				$this->lng->txt('edit').'</a>';
			
			++$counter;
		}
		$this->__showUsersTable($f_result,"listUsersObject",$editable);
		
		return true;
	}

	function performDeleteUsersObject()
	{
		include_once './Services/User/classes/class.ilLocalUser.php';
		$this->checkPermission("cat_administrate_users");

		foreach($_SESSION['delete_users'] as $user_id)
		{
			if(!in_array($user_id,ilLocalUser::_getAllUserIds($this->object->getRefId())))
			{
				die('user id not valid');
			}
			if(!$tmp_obj =& ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			$tmp_obj->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('deleted_users'));
		$this->listUsersObject();

		return true;
	}
			
	function deleteUserObject()
	{
		$this->checkPermission("cat_administrate_users");
		if(!count($_POST['user_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('no_users_selected'));
			$this->listUsersObject();
			
			return true;
		}
		$_SESSION['delete_users'] = $_POST['user_ids'];

		ilUtil::sendQuestion($this->lng->txt('sure_delete_selected_users'));
		$this->listUsersObject(true);
		return true;
	}

	function assignRolesObject()
	{
		global $rbacreview;
		
		$this->checkPermission("cat_administrate_users");

		include_once './Services/User/classes/class.ilLocalUser.php';

		if(!isset($_GET['obj_id']))
		{
			ilUtil::sendFailure('no_user_selected');
			$this->listUsersObject();

			return true;
		}

		$this->tabs_gui->setTabActive('administrate_users');

		$roles = $this->__getAssignableRoles();
		
		if(!count($roles))
		{
			#ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
			#$this->listUsersObject();

			#return true;
		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.cat_role_assignment.html',
			"Modules/Category");
		$this->__showButton('listUsers',$this->lng->txt('back'));

		$ass_roles = $rbacreview->assignedRoles($_GET['obj_id']);

		$counter = 0;
		foreach($roles as $role)
		{
			$role_obj =& ilObjectFactory::getInstanceByObjId($role['obj_id']);
			
			$disabled = false;
			$f_result[$counter][] = ilUtil::formCheckbox(in_array($role['obj_id'],$ass_roles) ? 1 : 0,
														 'role_ids[]',
														 $role['obj_id'],
														 $disabled);
			$f_result[$counter][] = $role_obj->getTitle();
			$f_result[$counter][] = $role_obj->getDescription();
			$f_result[$counter][] = $role['role_type'] == 'global' ? 
				$this->lng->txt('global') :
				$this->lng->txt('local');
			
			unset($role_obj);
			++$counter;
		}
		$this->__showRolesTable($f_result,"assignRolesObject");
	}

	function assignSaveObject()
	{
		global $rbacreview,$rbacadmin;
		$this->checkPermission("cat_administrate_users");

		include_once './Services/User/classes/class.ilLocalUser.php';
		// check hack
		if(!isset($_GET['obj_id']) or !in_array($_REQUEST['obj_id'],ilLocalUser::_getAllUserIds()))
		{
			ilUtil::sendFailure('no_user_selected');
			$this->listUsersObject();

			return true;
		}
		$roles = $this->__getAssignableRoles();

		// check minimum one global role
		if(!$this->__checkGlobalRoles($_POST['role_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('no_global_role_left'));
			$this->assignRolesObject();

			return false;
		}
		
		$new_role_ids = $_POST['role_ids'] ? $_POST['role_ids'] : array();
		$assigned_roles = $rbacreview->assignedRoles((int) $_REQUEST['obj_id']);
		foreach($roles as $role)
		{
			if(in_array($role['obj_id'],$new_role_ids) and !in_array($role['obj_id'],$assigned_roles))
			{
				$rbacadmin->assignUser($role['obj_id'],(int) $_REQUEST['obj_id']);
			}
			if(in_array($role['obj_id'],$assigned_roles) and !in_array($role['obj_id'],$new_role_ids))
			{
				$rbacadmin->deassignUser($role['obj_id'],(int) $_REQUEST['obj_id']);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('role_assignment_updated'));
		$this->assignRolesObject();
		
		return true;
	}

	// PRIVATE
	function __getAssignableRoles()
	{
		global $rbacreview,$ilUser;

		// check local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		// Admin => all roles
		if(in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			$global_roles = $rbacreview->getGlobalRolesArray();
		}
		elseif($tmp_obj->getTimeLimitOwner() == $this->object->getRefId())
		{
			$global_roles = $rbacreview->getGlobalAssignableRoles();
		}			
		else
		{
			$global_roles = array();
		}
		return $roles = array_merge($global_roles,
									$rbacreview->getAssignableChildRoles($this->object->getRefId()));
	}

	function __checkGlobalRoles($new_assigned)
	{
		global $rbacreview,$ilUser;

		$this->checkPermission("cat_administrate_users");

		// return true if it's not a local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		if($tmp_obj->getTimeLimitOwner() != $this->object->getRefId() and
		   !in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			return true;
		}

		// new assignment by form
		$new_assigned = $new_assigned ? $new_assigned : array();
		$assigned = $rbacreview->assignedRoles((int) $_GET['obj_id']);

		// all assignable globals
		if(!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			$ga = $rbacreview->getGlobalAssignableRoles();
		}
		else
		{
			$ga = $rbacreview->getGlobalRolesArray();
		}
		$global_assignable = array();
		foreach($ga as $role)
		{
			$global_assignable[] = $role['obj_id'];
		}

		$new_visible_assigned_roles = array_intersect($new_assigned,$global_assignable);
		$all_assigned_roles = array_intersect($assigned,$rbacreview->getGlobalRoles());
		$main_assigned_roles = array_diff($all_assigned_roles,$global_assignable);

		if(!count($new_visible_assigned_roles) and !count($main_assigned_roles))
		{
			return false;
		}
		return true;
	}


	function __showRolesTable($a_result_set,$a_from = "")
	{
		$this->checkPermission("cat_administrate_users");

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$this->ctrl->setParameter($this,'obj_id',$_GET['obj_id']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		// SET FOOTER BUTTONS
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		
		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","assignSave");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("change_assignment"));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_GET['obj_id']);
		$title = $this->lng->txt('role_assignment').' ('.$tmp_obj->getFullname().')';

		$tbl->setTitle($title,"icon_role.gif",$this->lng->txt("role_assignment"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("title"),
								   $this->lng->txt('description'),
								   $this->lng->txt("type")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "description",
								  "type"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "assignRoles",
								  "obj_id" => $_GET['obj_id'],
								  "cmdClass" => "ilobjcategorygui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","35%","45%","16%"));

		$this->set_unlimited = true;
		$this->__setTableGUIBasicData($tbl,$a_result_set,$a_from,true);
		$tbl->render();

		$this->tpl->setVariable("ROLES_TABLE",$tbl->tpl->get());

		return true;
	}		

	function __showUsersTable($a_result_set,$a_from = "",$a_footer = true)
	{
		$this->checkPermission("cat_administrate_users");
		
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$this->ctrl->setParameter($this,'sort_by',$_GET['sort_by']);
		$this->ctrl->setParameter($this,'sort_order',$_GET['sort_order']);
		$this->ctrl->setParameter($this,'offset',$_GET['offset']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		if($a_footer)
		{
			// SET FOOTER BUTTONS
			$tpl->setVariable("COLUMN_COUNTS",6);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

			$tpl->setCurrentBlock("tbl_action_button");
			$tpl->setVariable("BTN_NAME","deleteUser");
			$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("tbl_action_row");
			$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
			$tpl->parseCurrentBlock();
		}

		$tbl->setTitle($this->lng->txt("users"),"icon_usr_b.gif",$this->lng->txt("users"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt('context'),
								   $this->lng->txt('role_assignment')));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "context",
								  "role_assignment"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "listUsers",
								  "cmdClass" => "ilobjcategorygui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","20%","20%","20%","20%","20%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set,$a_from,true);
		$tbl->render();

		$this->tpl->setVariable("USERS_TABLE",$tbl->tpl->get());

		return true;
	}		

	function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "",$a_footer = true)
	{
		global $ilUser;

		switch ($a_from)
		{
			case "listUsersObject":
				$tbl->setOrderColumn($_GET["sort_by"]);
				$tbl->setOrderDirection($_GET["sort_order"]);
				$tbl->setOffset($_GET["offset"]);
				$tbl->setMaxCount($this->all_users_count);
				$tbl->setLimit($ilUser->getPref('hits_per_page'));
				$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
				$tbl->setData($result_set);
				$tbl->disable('auto_sort');

				return true;


			case "assignRolesObject":
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
				if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
				{
					$_GET["sort_by"] = "login";
				}
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
			
			case "clipboardObject":
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				$tbl->disable("footer");
				break;
				
			default:
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		if($this->set_unlimited)
		{
			$tbl->setLimit($_GET["limit"]*100);
		}
		else
		{
			$tbl->setLimit($_GET['limit']);
		}
		$tbl->setMaxCount(count($result_set));

		if($a_footer)
		{
			$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		}
		else
		{
			$tbl->disable('footer');
		}
		$tbl->setData($result_set);
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __buildFilterSelect($a_parent_ids)
	{
		$action[0] = $this->lng->txt('all_users');
		$action[$this->object->getRefId()] = $this->lng->txt('users').
			' ('.ilObject::_lookupTitle(ilObject::_lookupObjId($this->object->getRefId())).')';

		foreach($a_parent_ids as $parent)
		{
			if($parent == $this->object->getRefId())
			{
				continue;
			}
			switch($parent)
			{
				case ilLocalUser::_getUserFolderId():
					$action[ilLocalUser::_getUserFolderId()] = $this->lng->txt('global_user'); 
					
					break;

				default:
					$action[$parent] = $this->lng->txt('users').' ('.ilObject::_lookupTitle(ilObject::_lookupObjId($parent)).')';

					break;
			}
		}
		return ilUtil::formSelect($_SESSION['filtered_users'][$this->object->getRefId()],"filter",$action,false,true);
	}
	
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "frameset";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
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
		$this->checkPermission("write");
		
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
	
	/**
	 * Add sub tabs
	 * @param string 
	 * @access protected
	 */
	protected function getSubTabs($a_section)
	{
		switch($a_section)
		{
			case 'edit':
				$this->tabs_gui->addSubTabTarget("edit_properties",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));
												 
				include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
				if(in_array('cat',ilAdvancedMDRecord::_getActivatedObjTypes()))
				{
					$this->tabs_gui->addSubTabTarget("edit_cat_settings",
													 $this->ctrl->getLinkTarget($this,'editInfo'),
													 "editInfo", get_class($this));
				}
		}
	}


} // END class.ilObjCategoryGUI
?>
