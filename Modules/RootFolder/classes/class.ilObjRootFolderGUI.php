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
* Class ilObjRootFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjRootFolderGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI
* 
* @extends ilObjectGUI
*/

require_once "./Services/Container/classes/class.ilContainerGUI.php";
require_once "./Modules/Category/classes/class.ilObjCategoryGUI.php";

class ilObjRootFolderGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRootFolderGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "root";
		$this->ilContainerGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	/**
	* import categories form
	*/
	function importCategoriesFormObject ()
	{
		ilObjCategoryGUI::_importCategoriesForm($this->ref_id, $this->tpl);
	}

	/**
	* import cancelled
	*
	* @access private
	*/
	function importCancelledObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	* import categories
	*/
	function importCategoriesObject()
	{
	  ilObjCategoryGUI::_importCategories($this->ref_id,0);
	}


	/**
	 * import categories
	 */
	function importCategoriesWithRolObject()
	{
	  ilObjCategoryGUI::_importCategories($this->ref_id,1);
	}


	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""),
				array("", "view", "render"));
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

		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			case 'ilcontainerlinklistgui':
				include_once("./classes/class.ilContainerLinkListGUI.php");
				$link_list_gui =& new ilContainerLinkListGUI();
				$ret =& $this->ctrl->forwardCommand($link_list_gui);
				break;

				// container page editing
			case "ilpageobjectgui":
				$this->tpl->getStandardTemplate();
				$this->setLocator();
				ilUtil::sendInfo();
				ilUtil::infoPanel();
				//$this->prepareOutput(false);
				$ret = $this->forwardToPageObject();
				$this->setTitleAndDescription();
				$this->setPageEditorTabs();
				return $ret;
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			default:
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
	* edit category
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $lng;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
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
	* called by prepare output 
	*/
	function setTitleAndDescription()
	{
		global $lng;

		parent::setTitleAndDescription();
		$this->tpl->setDescription("");
		if ($this->object->getTitle() == "ILIAS")
		{
			$this->tpl->setTitle($lng->txt("repository"));
		}
		else
		{
			if ($this->object->getDescription() != "")
			{
				$this->tpl->setTitle($this->object->getDescription());
			}
		}
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
				$this->object->saveIcons($_FILES["cont_big_icon"],
					$_FILES["cont_small_icon"], $_FILES["cont_tiny_icon"]);
			}
			
			$this->update = $this->object->update();
		}

		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		$settings = new ilContainerSortingSettings($this->object->getId());
		$settings->setSortMode((int) $_POST['sorting']);
		$settings->update();

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getTargetScript()."?".$this->link_params));
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addTranslationObject()
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
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
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

		$_SESSION["translation_post"] = $_POST;
		$this->object->removeSmallIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}
	
	/**
	* remove tiny icon
	*
	* @access	public
	*/
	function removeTinyIconObject()
	{

		$_SESSION["translation_post"] = $_POST;
		$this->object->removeTinyIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, $_GET["mode"]));
	}

	/**
	* Get Actions
	*/
	function getActions()
	{
		$d = parent::getActions();
		unset($d["link"]);			// in root folder we have only categories (cannot be linked)
		return $d;
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
			$settings->getSortMode() == ilContainerSortingSettings::MODE_TITLE,
			'sorting',
			ilContainerSortingSettings::MODE_TITLE));
		$this->tpl->setVariable('RADIO_SORT_MANUAL',ilUtil::formRadioButton(
			$settings->getSortMode() == ilContainerSortingSettings::MODE_MANUAL,
			'sorting',
			ilContainerSortingSettings::MODE_MANUAL));
	}
	
}
?>
