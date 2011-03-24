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


/**
* Class ilObjRootFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjRootFolderGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI, ilColumnGUI, ilObjectCopyGUI, ilObjStyleSheetGUI
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
		global $lng;
		
		$this->type = "root";
		$this->ilContainerGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		
		$lng->loadLanguageModule("cntr");
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
		global $rbacsystem, $lng;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("settings",
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
				$this->prepareOutput(false);
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "ilcolumngui":
				$this->checkPermission("read");
				$this->prepareOutput();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
				$this->renderObject();
				break;

			case 'ilobjectcopygui':
				$this->prepareOutput();
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('root');
				$this->ctrl->forwardCommand($cp);
				break;
				
			case "ilobjstylesheetgui":
				$this->forwardToStyleSheet();
				break;

			default:
				$this->prepareOutput();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));

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
	* Render root folder
	*/
	function renderObject()
	{
		global $ilTabs;
		
		$ilTabs->activateTab("view_content");
		$ret =  parent::renderObject();
		return $ret;
	}


	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		global $lng;

		parent::setTitleAndDescription();
		$this->tpl->setDescription("");
		if (!ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"))
		{
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
	}
	
	function initEditForm()
	{
		// $this->lng->loadLanguageModule($this->object->getType());

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("repository"));


		// list translations

		include_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";

		$translations = $this->object->getTranslations();
		$languages = ilMDLanguageItem::_getLanguages();
		
		foreach($translations["Fobject"] as $idx => $trans)
		{
			$title = new ilCustomInputGUI($this->lng->txt("translation").": ".
				$languages[$trans["lang"]], "");
			if($idx)
			{
				$this->ctrl->setParameter($this, "rmvtr", $trans["lang"]);
				$title->setHTML("<div align=\"right\" class=\"small\">".
					"<a href=\"".$this->ctrl->getLinkTarget($this, "removeTranslation").
					"\">".$this->lng->txt("remove_translation")."</a></div>");
			}
			$form->addItem($title);

			// title
			$ti = new ilTextInputGUI($this->lng->txt("title"), "title_".$idx);
			$ti->setMaxLength(128);
			$ti->setSize(40);
			if($idx)
			{
				$ti->setRequired(true);
			}
			$ti->setValue($trans["title"]);
			$title->addSubItem($ti);

			// description
			$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc_".$idx);
			$ta->setCols(40);
			$ta->setRows(2);
			$ta->setValue($trans["desc"]);
			$title->addSubItem($ta);

			// language
			$tl = new ilSelectInputGUI($this->lng->txt("language"), "lang_".$idx);
			$tl->setOptions($languages);
			$tl->setRequired(true);				
			$tl->setValue($trans["lang"]);
			$title->addSubItem($tl);

			// default (form gui does not support "single" radiobutton yet)
			$checked = ($idx == $translations["default_language"]) ? " checked=\"checked\"" : "";
			$td = new ilCustomInputGUI($this->lng->txt("default"), "default");
			$td->setHTML("<input type=\"radio\" name=\"default\" value=\"".$idx."\"".$checked." />");
			$title->addSubItem($td);
		}


		// sorting

		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		$settings = new ilContainerSortingSettings($this->object->getId());

		$sort = new ilRadioGroupInputGUI($this->lng->txt('sorting_header'), "sorting");
		$sort_title = new ilRadioOption($this->lng->txt('sorting_title_header'),
			ilContainer::SORT_TITLE);
		$sort_title->setInfo($this->lng->txt('sorting_info_title'));
		$sort->addOption($sort_title);

		$sort_manual = new ilRadioOption($this->lng->txt('sorting_manual_header'),
			ilContainer::SORT_MANUAL);
		$sort_manual->setInfo($this->lng->txt('sorting_info_manual'));
		$sort->addOption($sort_manual);

		$sort->setValue($settings->getSortMode());
		$form->addItem($sort);


		$this->showCustomIconsEditing(1, $form, false);
		
		
		$hide = new ilCheckboxInputGUI($this->lng->txt("cntr_hide_title_and_icon"), "hide_header_icon_and_title");
		$hide->setChecked(ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"));
		$form->addItem($hide);
		

		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("addTranslationForm", $this->lng->txt("add_translation"));

		return $form;
	}

	function getEditFormValues()
	{
		// values are set in initEditForm()
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function updateObject()
	{
		if (!$this->checkPermissionBool("write"))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// default language set? not part of form
			if (!isset($_POST["default"]) && isset($_POST["title_0"]))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),
					$this->ilias->error_obj->MESSAGE);
			}

			$form = $this->initEditForm();
			if($form->checkInput())
			{
				// gather translation data
				$langs = $trans = array();
				$translations = $this->object->getTranslations();				
			    foreach(array_keys($translations["Fobject"]) as $idx)
				{
					$trans[$idx] = array("title" => $form->getInput("title_".$idx),
						"desc" => $form->getInput("desc_".$idx),
						"lang" => $form->getInput("lang_".$idx));

					$langs[] = $trans[$idx]["lang"];
				}
				unset($translations);

				// check for duplicate languages
				if(sizeof($langs) != sizeof(array_unique($langs)))
				{
					$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),
						$this->ilias->error_obj->MESSAGE);
				}
				unset($langs);

				// first delete all translation entries...
				$this->object->removeTranslations();

				// set translations
				$default = $form->getInput("default");
				foreach($trans as $idx => $lang)
				{
					$is_default = false;
					if($idx == $default)
					{
						// bring back old translation, if no individual translation is given
						if (trim($lang["title"]) == "")
						{
							$lang["title"] = "ILIAS";
						}

						$this->object->setTitle($lang["title"]);
						$this->object->setDescription($lang["desc"]);
						$is_default = true;

						if($lang["title"] == "ILIAS")
						{
							continue;
						}
					}

					$this->object->addTranslation($lang["title"], $lang["desc"],
						$lang["lang"], $is_default);
				}
				unset($trans);

				$this->object->update();

				include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
				$settings = new ilContainerSortingSettings($this->object->getId());
				$settings->setSortMode($form->getInput("sorting"));
				$settings->update();

				// save custom icons
				if ($this->ilias->getSetting("custom_icons"))
				{
					if($form->getItemByPostVar("cont_big_icon")->getDeletionFlag())
					{
						$this->object->removeBigIcon();
					}
					if($form->getItemByPostVar("cont_tiny_icon")->getDeletionFlag())
					{
						$this->object->removeTinyIcon();
					}

					$this->object->saveIcons($_FILES["cont_big_icon"]['tmp_name'],
						null, $_FILES["cont_tiny_icon"]['tmp_name']);
				}

				// hide icon/title
				ilContainer::_writeContainerSetting($this->object->getId(),
					"hide_header_icon_and_title",
					$form->getInput("hide_header_icon_and_title"));

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
				$this->ctrl->redirect($this, "edit");
			}

			// display form to correct errors
			$this->tabs_gui->setTabActive("settings");
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
		}
	}

	function addTranslationFormObject(ilPropertyFormGUI $a_form = null)
	{
		$this->tabs_gui->setTabActive("settings");
		$this->lng->loadLanguageModule($this->object->getType());

		if(!$a_form)
		{
			$a_form = $this->initTranslationForm();
		}
		$this->tpl->setContent($a_form->getHTML());
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addTranslationObject()
	{
		$this->checkPermission("write");

		$form = $this->initTranslationForm();
		if($form->checkInput())
		{
			// first translation is default
			$default = false;
			$translations = $this->object->getTranslations();
			if(!sizeof($translations["Fobject"]))
			{
				$default = true;

				// update object
				$this->object->setTitle($form->getInput("title"));
				$this->object->setDescription($form->getInput("desc"));
				$this->object->update();
			}

			$this->object->addTranslation($form->getInput("title"),
				$form->getInput("desc"), $form->getInput("lang"), $default);

			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "edit");
		}

		$this->addTranslationFormObject($form);
	}

	protected function initTranslationForm()
	{
		global $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("repository").": ".
			$this->lng->txt("translation"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		// language
		$tl = new ilSelectInputGUI($this->lng->txt("language"), "lang");

		include_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";
		$languages = array(""=>"")+ilMDLanguageItem::_getLanguages();
		$translations = $this->object->getTranslations();
	
		// if first translation set user language as default
		if(!sizeof($translations["Fobject"]))
		{
			$tl->setValue($ilUser->getPref("language"));
		}
		// remove used languages from selection
		else
		{
			foreach($translations["Fobject"] as $idx => $trans)
			{
				unset($languages[$trans["lang"]]);
			}
		}

		$tl->setOptions($languages);
		$tl->setRequired(true);
		$form->addItem($tl);
		

		$form->addCommandButton("addTranslation", $this->lng->txt("save"));
		$form->addCommandButton("edit", $this->lng->txt("cancel"));

		return $form;
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
		$id = $_REQUEST["rmvtr"];
		if($id)
		{
			$this->object->deleteTranslation($id);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "edit");
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
