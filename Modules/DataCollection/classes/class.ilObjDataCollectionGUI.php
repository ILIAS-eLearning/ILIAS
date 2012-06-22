<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
//require_once "./Modules/DataCollection/classes/class.ilDataCollectionRecordEditViewdefinitionGUI.php";
//require_once "./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinitionGUI.php";

/**
* Class ilObjDataCollectionGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
*
* @ilCtrl_Calls ilObjDataCollectionGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjDataCollectionGUI: ilPermissionGUI, ilObjectCopyGUI, ilDataCollectionView
* @ilCtrl_Calls ilObjDataCollectionGUI: ilDataCollectionFieldEditGUI, ilDataCollectionRecordEditGUI
* @ilCtrl_Calls ilObjDataCollectionGUI: ilDataCollectionRecordListGUI, ilDataCollectionRecordEditViewdefinitionGUI
* @ilCtrl_Calls ilObjDataCollectionGUI: ilDataCollectionRecordViewGUI, ilDataCollectionRecordViewViewdefinitionGUI
* @ilCtrl_Calls ilObjDataCollectionGUI: ilDataCollectionTableEditGUI, ilDataCollectionFieldListGUI, ilObjFileGUI
*
* @extends ilObject2GUI
*/
class ilObjDataCollectionGUI extends ilObject2GUI
{	

	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $lng;
	    parent::__construct($a_id, $a_id_type, $a_parent_node_id);		

		$lng->loadLanguageModule("dcl");

		If(isset($_GET['table_id']))
		{
			$this->table_id = $_GET['table_id'];
		}
		elseif($a_id > 0)
		{
			$this->table_id = $this->object->getMainTableId();
		}
	}
	
	function getStandardCmd()
	{
		return "render";
	}
	
	function getType()
	{
		return "dcl";
	}
	
	function executeCommand()
	{
		global $ilCtrl, $ilTabs;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_info");
				$this->infoScreenForward();	
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("dcl");
				$this->ctrl->forwardCommand($cp);
				break;

			case "ildatacollectionfieldlistgui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$this->addListFieldsTabs("list_fields");
				$ilTabs->setTabActive("id_fields");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionFieldListGUI.php");
				$fieldlist_gui = new ilDataCollectionFieldListGUI($this);
				$this->ctrl->forwardCommand($fieldlist_gui);
				break;

			case "ildatacollectiontableeditgui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$ilTabs->setTabActive("id_fields");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionTableEditGUI.php");
				$tableedit_gui = new ilDataCollectionTableEditGUI($this);
				$this->ctrl->forwardCommand($tableedit_gui);
				break;

			case "ildatacollectionfieldeditgui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$ilTabs->activateTab("id_fields");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionFieldEditGUI.php");
				$fieldedit_gui = new ilDataCollectionFieldEditGUI($this);
				$this->ctrl->forwardCommand($fieldedit_gui);
				break;

			case "ildatacollectionrecordlistgui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$ilTabs->activateTab("id_records");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordListGUI.php");
				$recordlist_gui = new ilDataCollectionRecordListGUI($this);
				$this->ctrl->forwardCommand($recordlist_gui);
				break;

			case "ildatacollectionrecordeditgui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$ilTabs->activateTab("id_records");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordEditGUI.php");
				$recordedit_gui = new ilDataCollectionRecordEditGUI($this);
				$this->ctrl->forwardCommand($recordedit_gui);
				break;

			case "ildatacollectionrecordviewviewdefinitiongui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$this->addListFieldsTabs("view_viewdefinition");
				$ilTabs->setTabActive("id_fields");
				include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinitionGUI.php");
				$recordedit_gui = new ilDataCollectionRecordViewViewdefinitionGUI($this);
				$this->ctrl->forwardCommand($recordedit_gui);
				break;
				
			case "ilobjfilegui":
				$this->addHeaderAction($cmd);
				$this->prepareOutput();
				$ilTabs->setTabActive("id_records");
				include_once("./Modules/File/classes/class.ilObjFile.php");
				$file_gui = new ilObjFile($this);
				$this->ctrl->forwardCommand($file_gui);
				break;

			default:								
				$this->addHeaderAction($cmd);
				return parent::executeCommand();
/*
				switch($cmd)
				{
					case "editSettings":
						$this->initEditForm();
						break;

					default:
						if(!$cmd)
						{
							$cmd = $this->getStandardCmd();
						}
						$this->$cmd();
						break;
				}
				break;
*/
		}
		//$this->addHeaderAction($cmd);
		return true;
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}

	/**
	* show Content; redirect to ilDataCollectionRecordListGUI::listRecords
	*/
	function render()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ildatacollectionrecordlistgui","listRecords");
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $ilTabs, $ilErr;
		
		$ilTabs->activateTab("id_info");

		if (!$this->checkPermissionBool("visible"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();		
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$this->ctrl->forwardCommand($info);
	}


	function addLocatorItems()
	{
		global $ilLocator;
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
		}
	}
	
	/**
	 * Deep link
	 * 
	 * @param string $a_target 
	 */
	function _goto($a_target)
	{									
		$id = explode("_", $a_target);		

		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $id[0];		
		$_GET["cmd"] = "settings";
		
		include("ilias.php");
		exit;
	}
	
	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);

		// disabling import
		unset($forms[self::CFORM_IMPORT]);	
		
		return $forms;
	}

	protected function afterSave(ilObject $a_new_object)
	{
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->redirect($this, "edit");
	}

	/*
	 * setTabs
	 */
	/**
	 * create tabs (repository/workspace switch)
	 *
	 * this had to be moved here because of the context-specific permission tab
	 */
	function setTabs()
	{		

		global $ilAccess, $ilTabs, $lng;

		// list records
		if ($ilAccess->checkAccess('read', "", $this->object->getRefId()))
		{
			$ilTabs->addTab("id_records",
				$lng->txt("content"),
				$this->ctrl->getLinkTargetByClass("ildatacollectionrecordlistgui", "listRecords"));
		}

		// info screen
		if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()))
		{
			$ilTabs->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}
		
		// settings
		if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
		{
			$ilTabs->addTab("id_settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "editObject"));
		}

		// list fields
		if ($ilAccess->checkAccess('edit_fields', "", $this->object->getRefId()))
		{
			$ilTabs->addTab("id_fields",
				$lng->txt("dcl_list_fields"),
				$this->ctrl->getLinkTargetByClass("ildatacollectionfieldlistgui", "listFields"));
		}
		
		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			//$ilTabs->addTab("export",
			//$lng->txt("export"),
			//$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}
		
		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$ilTabs->addTab("id_permissions",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}


	/**
	* Add List Fields SubTabs
	*
	* @param string $a_active 
	*/
	function addListFieldsTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;


		$ilTabs->addSubTab("list_fields",
			$lng->txt("dcl_list_fields"),
			$ilCtrl->getLinkTargetByClass("ildatacollectionfieldlistgui", "listFields"));

		$ilCtrl->setParameterByClass("ildatacollectionrecordviewviewdefinitiongui","table_id", $this->table_id);
		$ilTabs->addSubTab("view_viewdefinition",
			$lng->txt("dcl_record_view_viewdefinition"),
			$ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewviewdefinitiongui","create"));

		//TODO
		$ilTabs->addSubTab("edit_viewdefinition",
			$lng->txt("dcl_record_edit_viewdefinition"),
			$ilCtrl->getLinkTargetByClass("ildatacollectionfieldlistgui", "listFields"));

		//TODO
		$ilTabs->addSubTab("list_viewdefinition",
			$lng->txt("dcl_record_list_viewdefinition"),
			$ilCtrl->getLinkTargetByClass("ildatacollectionfieldlistgui", "listFields"));

		$ilTabs->activateSubTab($a_active);
	}
	

	/**
	 * initEditCustomForm
	 */
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $ilCtrl, $ilErr;
		
		// is_online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "is_online");
		$a_form->addItem($cb);
		
		// edit_type
		$edit_type = new ilRadioGroupInputGUI($this->lng->txt('dcl_edit_type'),'edit_type');	
		
			$opt = new ilRadioOption($this->lng->txt('dcl_edit_type_non'), 0);
			$opt->setInfo($this->lng->txt('dcl_edit_type_non_info'));
			$edit_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('dcl_edit_type_unlim'), 1);
			$opt->setInfo($this->lng->txt('dcl_edit_type_unlim_info'));
			$edit_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('dcl_edit_type_lim'), 2);
			$opt->setInfo($this->lng->txt('dcl_edit_type_lim_info'));

				$start = new ilDateTimeInputGUI($this->lng->txt('dcl_edit_start'), 'edit_start');
				$start->setShowTime(true);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('dcl_edit_end'), 'edit_end');
				$end->setShowTime(true);
				$opt->addSubItem($end);
			
			$edit_type->addOption($opt);
		$a_form->addItem($edit_type);
		
		// Rating
		$cb = new ilCheckboxInputGUI($this->lng->txt("dcl_activate_rating"), "rating");
		$a_form->addItem($cb);
		
		// Public Notes
		$cb = new ilCheckboxInputGUI($this->lng->txt("public_notes"), "public_notes");
		$a_form->addItem($cb);
		
		// Approval
		$cb = new ilCheckboxInputGUI($this->lng->txt("dcl_activate_approval"), "approval");
		$a_form->addItem($cb);
		
		// Public Notes
		$cb = new ilCheckboxInputGUI($this->lng->txt("dcl_activate_notification"), "notification");
		$a_form->addItem($cb);
	}
	
	/**
	 * getSettingsValues
	 */
	public function getEditFormCustomValues(array &$a_values)
	{
		$a_values["is_online"] = $this->object->getOnline();
		$a_values["edit_type"] = $this->object->getEditType();
		$a_values["edit_start"] = $this->object->loadDate($this->object->getEditStart(), true);
		$a_values["edit_end"] = $this->object->loadDate($this->object->getEditEnd(), true);
		$a_values["rating"] = $this->object->getRating();
		$a_values["public_notes"] = $this->object->getPublicNotes();
		$a_values["approval"] = $this->object->getApproval();
		$a_values["notification"] = $this->object->getNotification();

		return $a_values;
	}
	
	/**
	 * updateSettings
	 */
	public function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->setOnline($a_form->getInput("is_online"));
		$this->object->setEditType($a_form->getInput("edit_type"));
		$this->object->setEditStart($this->object->loadDate($a_form->getInput("edit_start"), false));
		$this->object->setEditEnd($this->object->loadDate($a_form->getInput("edit_end"), false));
		$this->object->setRating($a_form->getInput("rating"));
		$this->object->setPublicNotes($a_form->getInput("public_notes"));
		$this->object->setApproval($a_form->getInput("approval"));
		$this->object->setNotification($a_form->getInput("notification"));
	}
}

?>