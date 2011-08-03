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

	protected function setEditTabs($active_tab = "settings_misc")
	{
		$this->tabs_gui->addSubTab("settings_misc",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "edit"));

		$this->tabs_gui->addSubTab("settings_trans",
			$this->lng->txt("title_and_translations"),
			$this->ctrl->getLinkTarget($this, "editTranslations"));

		$this->tabs_gui->activateTab("settings");
		$this->tabs_gui->activateSubTab($active_tab);
	}
	
	function initEditForm()
	{
		$this->setEditTabs();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("repository"));
		

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
			$form = $this->initEditForm();
			if($form->checkInput())
			{
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
				ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
				ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());				
				// END ChangeEvent: Record update

				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
				$this->ctrl->redirect($this, "edit");
			}

			// display form to correct errors
			$this->setEditTabs();
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Edit title and translations
	 */
	function editTranslationsObject($a_get_post_values = false)
	{
		global $tpl;

		$this->lng->loadLanguageModule($this->object->getType());
		$this->setEditTabs("settings_trans");

		include_once("./Services/Object/classes/class.ilObjectTranslationTableGUI.php");
		$table = new ilObjectTranslationTableGUI($this, "editTranslations", true,
			"Translation");
		if ($a_get_post_values)
		{
			$vals = array();
			foreach($_POST["title"] as $k => $v)
			{
				$vals[] = array("title" => $v,
					"desc" => $_POST["desc"][$k],
					"lang" => $_POST["lang"][$k],
					"default" => ($_POST["default"] == $k));
			}
			$table->setData($vals);
		}
		else
		{
			$data = $this->object->getTranslations();
			foreach($data["Fobject"] as $k => $v)
			{
				$data["Fobject"][$k]["default"] = ($k == $data["default_language"]);
			}
			$table->setData($data["Fobject"]);
		}
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Save title and translations
	 */
	function saveTranslationsObject()
	{
		if (!$this->checkPermissionBool("write"))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// default language set?
		if (!isset($_POST["default"]) && count($_POST["lang"]) > 0)
		{
			ilUtil::sendFailure($this->lng->txt("msg_no_default_language"));
			return $this->editTranslationsObject(true);
		}

		// all languages set?
		if (array_key_exists("",$_POST["lang"]))
		{
			ilUtil::sendFailure($this->lng->txt("msg_no_language_selected"));
			return $this->editTranslationsObject(true);
		}

		// no single language is selected more than once?
		if (count(array_unique($_POST["lang"])) < count($_POST["lang"]))
		{
			ilUtil::sendFailure($this->lng->txt("msg_multi_language_selected"));
			return $this->editTranslationsObject(true);
		}

		// save the stuff
		$this->object->removeTranslations();

		if(sizeof($_POST["title"]))
		{
			foreach($_POST["title"] as $k => $v)
			{
				// update object data if default
				$is_default = ($_POST["default"] == $k);
				if($is_default)
				{
					$this->object->setTitle(ilUtil::stripSlashes($v));
					$this->object->setDescription(ilUtil::stripSlashes($_POST["desc"][$k]));
					$this->object->update();
				}

				$this->object->addTranslation(
					ilUtil::stripSlashes($v),
					ilUtil::stripSlashes($_POST["desc"][$k]),
					ilUtil::stripSlashes($_POST["lang"][$k]),
					$is_default);
			}
		}
		else
		{
			// revert to original title
			$this->object->setTitle("ILIAS");
			$this->object->setDescription("");
			$this->object->update();
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "editTranslations");
	}

	/**
	 * Add a translation
	 */
	function addTranslationObject()
	{
		if(sizeof($_POST["title"]))
		{
			$k = max(array_keys($_POST["title"]))+1;
		}
		else
		{
			$k = 0;
		}
		$_POST["title"][$k] = "";
		$this->editTranslationsObject(true);
	}

	/**
	 * Remove translation
	 */
	function deleteTranslationsObject()
	{
		$del_default = true;
		foreach($_POST["title"] as $k => $v)
		{
			if ($_POST["check"][$k])
			{
				unset($_POST["title"][$k]);
				unset($_POST["desc"][$k]);
				unset($_POST["lang"][$k]);
				if($_POST["default"] == $k)
				{
					$del_default = true;
				}
			}
		}
		// set new default
		if($del_default && sizeof($_POST["title"]))
		{
			$_POST["default"] = array_shift(array_keys($_POST["title"]));
		}
		$this->saveTranslationsObject();
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
