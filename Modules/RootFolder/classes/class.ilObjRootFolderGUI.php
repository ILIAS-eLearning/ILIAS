<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjRootFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjRootFolderGUI: ilPermissionGUI, ilContainerPageGUI, ilContainerLinkListGUI, 
* @ilCtrl_Calls ilObjRootFolderGUI: ilColumnGUI, ilObjectCopyGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjRootFolderGUI: ilCommonActionDispatcherGUI, ilObjectTranslationGUI
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
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "root";
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		
		$lng->loadLanguageModule("cntr");
		$lng->loadLanguageModule("obj");
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


	function getTabs()
	{
		global $rbacsystem, $lng, $ilHelp;
		
		$ilHelp->setScreenIdComponent("root");

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}

		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs();

	}

	function executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		// show repository tree
		$this->showRepTree();
		
		switch($next_class)
		{
			case 'ilcontainerlinklistgui':
				include_once("Services/Container/classes/class.ilContainerLinkListGUI.php");
				$link_list_gui = new ilContainerLinkListGUI();
				$ret =& $this->ctrl->forwardCommand($link_list_gui);
				break;

				// container page editing
			case "ilcontainerpagegui":
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
				$perm_gui = new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "ilcolumngui":
				$this->checkPermission("read");
				$this->prepareOutput();
				include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
			
			case "ilcommonactiondispatchergui":	
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjecttranslationgui':
				$this->checkPermissionBool("write");
				$this->prepareOutput();
				//$this->tabs_gui->setTabActive('export');
				$this->setEditTabs("settings_trans");
				include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
				$transgui = new ilObjectTranslationGUI($this);
				$this->ctrl->forwardCommand($transgui);
				break;

			default:
				
				// fix bug http://www.ilias.de/mantis/view.php?id=10305
				if ($cmd == "infoScreen")
				{
					$this->checkPermission("visible");
				}
				else
				{
					$this->checkPermission("read");
				}
				$this->prepareOutput();
				include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
		
		include_once "Services/Object/classes/class.ilObjectListGUI.php";
		ilObjectListGUI::prepareJSLinks("", 
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false),
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false));
		
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
					$this->tpl->setDescription($this->object->getDescription()); // #13479
				}
			}
		}
	}

	protected function setEditTabs($active_tab = "settings_misc")
	{
		$this->tabs_gui->addSubTab("settings_misc",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "edit"));

		/*$this->tabs_gui->addSubTab("settings_trans",
			$this->lng->txt("title_and_translations"),
			$this->ctrl->getLinkTarget($this, "editTranslations"));*/

		$this->tabs_gui->addSubTab("settings_trans",
			$this->lng->txt("obj_multilinguality"),
			$this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", ""));


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
		

		$this->initSortingForm(
				$form,
				array(
					ilContainer::SORT_TITLE,
					ilContainer::SORT_CREATION,
					ilcontainer::SORT_MANUAL
				)
		);


		$this->showCustomIconsEditing(1, $form, false);
		
		
		$hide = new ilCheckboxInputGUI($this->lng->txt("cntr_hide_title_and_icon"), "hide_header_icon_and_title");
		$hide->setChecked(ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"));
		$form->addItem($hide);
		

		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("addTranslation", $this->lng->txt("add_translation"));

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
		global $ilSetting;

		if (!$this->checkPermissionBool("write"))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$form = $this->initEditForm();
			if($form->checkInput())
			{
				$this->saveSortingSettings($form);

				// save custom icons
				//save custom icons
				if ($ilSetting->get("custom_icons"))
				{
					if($_POST["cont_icon_delete"])
					{
						$this->object->removeCustomIcon();
					}
					$this->object->saveIcons($_FILES["cont_icon"]['tmp_name']);
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


		$this->ctrl->redirectByClass("ilobjecttranslationgui", "");

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
	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		ilObjectGUI::_gotoRepositoryRoot(true);
	}
	
}
?>
