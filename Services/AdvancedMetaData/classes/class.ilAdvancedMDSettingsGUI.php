<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDPermissionHelper.php');

/** 
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilAdvancedMDSettingsGUI:
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDSettingsGUI
{
	protected $lng;
	protected $tpl;
	protected $ctrl;
	protected $tabs;
	protected $permissions; // [ilAdvancedMDPermissionHelper]
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct()
	{
	 	global $tpl,$lng,$ilCtrl,$ilTabs;
	 	
	 	$this->ctrl = $ilCtrl;
	 	$this->lng = $lng;
	 	$this->lng->loadLanguageModule('meta');
	 	$this->tpl = $tpl;
	 	$this->tabs_gui = $ilTabs;
		
		$this->permissions = ilAdvancedMDPermissionHelper::getInstance();
	}
	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		$this->setSubTabs();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'showRecords';
				}
				$this->$cmd();
		}	 	
	}
	
	/**
	 * show record list
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function showRecords()
	{
		global $ilToolbar;
		
		$perm = $this->getPermissions()->hasPermissions(
			ilAdvancedMDPermissionHelper::CONTEXT_MD,
			$_REQUEST["ref_id"],
			array(
				ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD
				,ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS
		));				
		
		if($perm[ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD])
		{		
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";		
			$button = ilLinkButton::getInstance();
			$button->setCaption("add");
			$button->setUrl($this->ctrl->getLinkTarget($this, "createRecord"));		
			$ilToolbar->addButtonInstance($button);
			
			if($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS])
			{
				$ilToolbar->addSeparator();
			}
		}
		
		if($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS])
		{
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";		
			$button = ilLinkButton::getInstance();
			$button->setCaption("import");
			$button->setUrl($this->ctrl->getLinkTarget($this, "importRecords"));		
			$ilToolbar->addButtonInstance($button);			
		}
		
		$this->record_objs = $this->getRecordObjects();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_records.html','Services/AdvancedMetaData');

		include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordTableGUI.php");
		$table_gui = new ilAdvancedMDRecordTableGUI($this, "showRecords", $this->getPermissions());
		$table_gui->setTitle($this->lng->txt("md_record_list_table"));
		$table_gui->parseRecords($this->record_objs);		
		
		// permissions?
		$table_gui->addCommandButton("updateRecords", $this->lng->txt("save"));
		//$table_gui->addCommandButton('createRecord',$this->lng->txt('add'));			
		$table_gui->addMultiCommand("exportRecords",$this->lng->txt('export'));		
		$table_gui->addMultiCommand("confirmDeleteRecords", $this->lng->txt("delete"));		
		$table_gui->setSelectAllCheckbox("record_id");
		
		$this->tpl->setVariable('RECORD_TABLE',$table_gui->getHTML());
		
		return true;
	}
	
	public function showPresentation()
	{		
		if($this->initFormSubstitutions())
		{		
			if (is_object($this->form))
			{
				$this->tabs_gui->setSubTabActive('md_adv_presentation');
				return $this->tpl->setContent($this->form->getHTML());
			}
		}
		return $this->showRecords();
	}
	
	/**
	 * Update substitution
	 *
	 * @access public
	 * 
	 */
	public function updateSubstitutions()
	{
		foreach(ilAdvancedMDRecord::_getActivatedObjTypes() as $obj_type)
		{			
			$perm = null;
			// :TODO: hardwired?
			if(in_array($obj_type, array("crs", "cat")))
			{
				$perm =	$this->getPermissions()->hasPermissions(
					ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
					$obj_type,
					array(
						ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
						,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
						,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
				));
			}
			
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
	 		$sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
			
			if($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION])
			{
				$sub->enableDescription($_POST['enabled_desc_'.$obj_type]);
			}
			
			if($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES])
			{
				$sub->enableFieldNames((int) $_POST['enabled_field_names_'.$obj_type]);
			}
			
			$definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($obj_type);
			$definitions = $sub->sortDefinitions($definitions);
		
			// gather existing data
			$counter = 1;
			$old_sub = array();
			foreach($definitions as $def)
			{				
				$field_id = $def->getFieldId();
				$old_sub[$field_id] = array(
					"active" => $sub->isSubstituted($field_id),
					"pos" => $counter++,
					"bold" => $sub->isBold($field_id),
					"newline" => $sub->hasNewline($field_id)					
				);				
			}
		
	 		$sub->resetSubstitutions(array());
			
			$new_sub = array();
			foreach($definitions as $def)
			{	
				$field_id = $def->getFieldId();
				$old = $old_sub[$field_id];
				
				$perm_def = $this->getSubstitutionFieldPermissions($obj_type, $field_id);
				if($perm_def["show"])
				{
					$active = (isset($_POST['show'][$obj_type][$field_id]) && $_POST['show'][$obj_type][$field_id]);
				}
				else
				{
					$active = $old["active"];
				}
				
				if($active)
				{					
					$new_sub[$field_id] = $old;
				
					if($perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS])
					{
						$new_sub[$field_id]["pos"] = (int)$_POST['position'][$obj_type][$field_id];
					}
					if($perm_def["bold"])
					{
						$new_sub[$field_id]["bold"] = (isset($_POST['bold'][$obj_type][$field_id]) && $_POST['bold'][$obj_type][$field_id]);
					}
					if($perm_def["newline"])
					{
						$new_sub[$field_id]["newline"] = (isset($_POST['newline'][$obj_type][$field_id]) && $_POST['newline'][$obj_type][$field_id]);
					}										
				}
			}
		
			if(sizeof($new_sub))
			{
				$new_sub = ilUtil::sortArray($new_sub, "pos", "asc", true, true);
				foreach($new_sub as $field_id => $field)
				{
					$sub->appendSubstitution($field_id, $field["bold"], $field["newline"]);
				}
			}
			
			$sub->update();
		}
		
	 	
	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
	 	$this->ctrl->redirect($this, "showPresentation");
	}
	
	/**
	 * Export records
	 *
	 * @access public
	 */
	public function exportRecords()
	{
	 	if(!isset($_POST['record_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
		
		// all records have to be exportable
		$fail = array();
		foreach($_POST['record_id'] as $record_id)
		{
			if(!$this->getPermissions()->hasPermission(
				ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
				$record_id,
				ilAdvancedMDPermissionHelper::ACTION_RECORD_EXPORT))
			{		
				$record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);															
				$fail[] = $record->getTitle(); 
			}
		}
		if($fail)
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_copy')." ".implode(", ", $fail), true);
			$this->ctrl->redirect($this, "showRecords");	
		}
		
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordXMLWriter.php');
	 	$xml_writer = new ilAdvancedMDRecordXMLWriter($_POST['record_id']);
	 	$xml_writer->write();
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
	 	$export_files = new ilAdvancedMDRecordExportFiles();
	 	$export_files->create($xml_writer->xmlDumpMem());
	 	
	 	ilUtil::sendSuccess($this->lng->txt('md_adv_records_exported'));
	 	$this->showFiles();
	}
	
	/**
	 * show export files
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function showFiles()
	{		
		$this->tabs_gui->setSubTabActive('md_adv_file_list');
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
		$files = new ilAdvancedMDRecordExportFiles();
		$file_data = $files->readFilesInfo();

		include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFilesTableGUI.php");
		$table_gui = new ilAdvancedMDRecordExportFilesTableGUI($this, "showFiles");
		$table_gui->setTitle($this->lng->txt("md_record_export_table"));
		$table_gui->parseFiles($file_data);
		$table_gui->addMultiCommand("downloadFile",$this->lng->txt('download'));
		$table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
		$table_gui->addCommandButton('showFiles',$this->lng->txt('cancel'));
		$table_gui->setSelectAllCheckbox("file_id");
		
		$this->tpl->setContent($table_gui->getHTML());
	}
	
	/**
	 * Download XML file
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function downloadFile()
	{
	 	if(!isset($_POST['file_id']) or count($_POST['file_id']) != 1)
	 	{
	 		ilUtil::sendFailure($this->lng->txt('md_adv_select_one_file'));
	 		$this->showFiles();
	 		return false;
	 	}
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
	 	$files = new ilAdvancedMDRecordExportFiles();
	 	$abs_path = $files->getAbsolutePathByFileId((int) $_POST['file_id'][0]);
		
	 	ilUtil::deliverFile($abs_path,'ilias_meta_data_record.xml','application/xml');
	}
	
	/**
	 * confirm delete files
	 *
	 * @access public
	 * 
	 */
	public function confirmDeleteFiles()
	{
	 	if(!isset($_POST['file_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->showFiles();
	 		return false;
	 	}
	
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
		$c_gui->setHeaderText($this->lng->txt("md_adv_delete_files_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "showFiles");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
		$files = new ilAdvancedMDRecordExportFiles();
		$file_data = $files->readFilesInfo();


		// add items to delete
		foreach($_POST["file_id"] as $file_id)
		{
			$info = $file_data[$file_id];
			$c_gui->addItem("file_id[]", $file_id, is_array($info['name']) ? implode(',',$info['name']) : 'No Records');
		}
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * Delete files
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deleteFiles()
	{
	 	if(!isset($_POST['file_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->editFiles();
	 		return false;
	 	}

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
		$files = new ilAdvancedMDRecordExportFiles();
		
		foreach($_POST['file_id'] as $file_id)
		{
			$files->deleteByFileId((int) $file_id);
		}
		ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_files'));
		$this->showFiles();
	}
	
	/**
	 * Confirm delete
	 *
	 * @access public
	 * 
	 */
	public function confirmDeleteRecords()
	{
	 	if(!isset($_POST['record_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRecords"));
		$c_gui->setHeaderText($this->lng->txt("md_adv_delete_record_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "showRecords");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteRecords");

		// add items to delete
		foreach($_POST["record_id"] as $record_id)
		{
			$record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
			$c_gui->addItem("record_id[]", $record_id, $record->getTitle() ? $record->getTitle() : 'No Title');
		}
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * Permanently delete records
	 *
	 * @access public
	 * 
	 */
	public function deleteRecords()
	{
	 	if(!isset($_POST['record_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
		
		// all records have to be deletable
		$fail = array();
		foreach($_POST['record_id'] as $record_id)
		{
			if(!$this->getPermissions()->hasPermission(
				ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
				$record_id,
				ilAdvancedMDPermissionHelper::ACTION_RECORD_DELETE))
			{		
				$record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);															
				$fail[] = $record->getTitle(); 
			}
		}
		if($fail)
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_delete')." ".implode(", ", $fail), true);
			$this->ctrl->redirect($this, "showRecords");	
		}
		
		foreach($_POST['record_id'] as $record_id)
		{
			$record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);											
			$record->delete();						
		}
		ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_records'), true);
		$this->ctrl->redirect($this, "showRecords");	
	}
	
	/**
	 * Save records (assigned object typed)
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function updateRecords()
	{
		foreach($this->getRecordObjects() as $record_obj)
		{
			$perm = $this->getPermissions()->hasPermissions(
				ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
				$record_obj->getRecordId(),
				array(
					ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
					,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
						ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
				));
						
			if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES])
			{
				$obj_types = array();
				if (is_array($_POST['obj_types'][$record_obj->getRecordId()]))
				{
					foreach ($_POST['obj_types'][$record_obj->getRecordId()] as $t)
					{
						$t = explode(":", $t);
						$obj_types[] = array(
							"obj_type" => ilUtil::stripSlashes($t[0]),
							"sub_type" => ilUtil::stripSlashes($t[1])
							);
					}
				}
				$record_obj->setAssignedObjectTypes($obj_types);
			}
			
			if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION])
			{
				$record_obj->setActive(isset($_POST['active'][$record_obj->getRecordId()]));
			}
			
			$record_obj->update();
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
	 	$this->ctrl->redirect($this, "showRecords");
	}
	
	/**
	 * show delete fields confirmation screen
	 *
	 * @access public
	 * 
	 */
	public function confirmDeleteFields()
	{
	 	if(!isset($_POST['field_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->editFields();
	 		return false;
	 	}
		
		$this->ctrl->saveParameter($this,'record_id');
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFields"));
		$c_gui->setHeaderText($this->lng->txt("md_adv_delete_fields_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "editFields");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteFields");

		// add items to delete
		foreach($_POST["field_id"] as $field_id)
		{
			$field = ilAdvancedMDFieldDefinition::getInstance($field_id);
			$c_gui->addItem("field_id[]", $field_id, $field->getTitle() ? $field->getTitle() : 'No Title');
		}
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * delete fields
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deleteFields()
	{
		$this->ctrl->saveParameter($this,'record_id');
		
	 	if(!isset($_POST['field_id']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->editFields();
	 		return false;
	 	}
		
		// all fields have to be deletable
		$fail = array();
		foreach($_POST['field_id'] as $field_id)
		{
			if(!$this->getPermissions()->hasPermission(
				ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
				$field_id,
				ilAdvancedMDPermissionHelper::ACTION_FIELD_DELETE))
			{		
				$field = ilAdvancedMDFieldDefinition::getInstance($field_id);											
				$fail[] = $field->getTitle(); 
			}
		}
		if($fail)
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_delete')." ".implode(", ", $fail), true);
			$this->ctrl->redirect($this, "editFields");	
		}
				
		foreach($_POST["field_id"] as $field_id)
		{
			$field = ilAdvancedMDFieldDefinition::getInstance($field_id);
			$field->delete();
		}	 	
	 	ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_fields'), true);
	 	$this->ctrl->redirect($this, "editFields");
	}
	
	/**
	 * Edit one record
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function editRecord()
	{	 		 	
	 	$this->ctrl->saveParameter($this,'record_id');
	 	$this->initRecordObject();
	 	$this->initForm('edit');
	 	$this->tpl->setContent($this->form->getHTML());
	 	
	}
	
	public function editFields()
	{		
		global $ilToolbar;
		
		$this->ctrl->saveParameter($this,'record_id');
	 	$this->initRecordObject();
		
		$perm = $this->getPermissions()->hasPermissions(
			ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
			$this->record->getRecordId(),
			array(
				ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD
				,ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS
		));
		
		if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD])
		{		
			// type selection
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$types = new ilSelectInputGUI("", "ftype");		
			$options = array();
			foreach(ilAdvancedMDFieldDefinition::getValidTypes() as $type)
			{
				$field = ilAdvancedMDFieldDefinition::getInstance(null, $type);
				$options[$type] = $this->lng->txt($field->getTypeTitle());
			}	
			$types->setOptions($options);		
			$ilToolbar->addInputItem($types);		
			
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "createField"));
			
			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";		
			$button = ilSubmitButton::getInstance();
			$button->setCaption("add");
			$button->setCommand("createField");
			$ilToolbar->addButtonInstance($button);			
		}
	
		// show field table
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->record->getRecordId());
		
		include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldTableGUI.php");
		$table_gui = new ilAdvancedMDFieldTableGUI($this, "editRecord", $this->getPermissions(), $perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS]);
		$table_gui->setTitle($this->lng->txt("md_adv_field_table"));
		$table_gui->parseDefinitions($fields);
		if(sizeof($fields))
		{
			$table_gui->addCommandButton("updateFields", $this->lng->txt("save"));
		}		
		$table_gui->addCommandButton("showRecords", $this->lng->txt('cancel'));
		$table_gui->addMultiCommand("confirmDeleteFields", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("field_id");
		
		$this->tpl->setContent($table_gui->getHTML());
	}
	
	/**
	 * Update fields 
	 *
	 * @access public
	 * 
	 */
	public function updateFields()
	{
		$this->ctrl->saveParameter($this,'record_id');
		
	 	if(!isset($_GET['record_id']) or !$_GET['record_id'])
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->editFields();
	 		return false;
	 	}
		
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($_GET['record_id']);
		
		if($this->getPermissions()->hasPermission(
			ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
			$_GET['record_id'],
			ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS))
		{			
			if(!isset($_POST['position']) or !is_array($_POST['position']))
			{
				$this->editFields();
				return false;
			}
			// sort by position
			asort($_POST['position'],SORT_NUMERIC);
			$positions = array_flip(array_keys($_POST['position'])); 	
			foreach($fields as $field)
			{
				$field->setPosition($positions[$field->getFieldId()]);
				$field->update();				
			}
		}
				
		foreach($fields as $field)
		{			
			if($this->getPermissions()->hasPermission(
				ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
				$field->getFieldId(),
				ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
				ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE))
			{		
				$field->setSearchable(isset($_POST['searchable'][$field->getFieldId()]) ? true : false);
				$field->update();
			}						
		}
		
		
	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
		$this->ctrl->redirect($this, "editFields");		
	}

	/**
	 * Update record
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function updateRecord()
	{
	 	global $ilErr;
	 	
	 	if(!isset($_GET['record_id']) or !$_GET['record_id'])
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
	 	$this->initRecordObject();
	 	$this->loadRecordFormData();
	 	
	 	if(!$this->record->validate())
	 	{
	 		ilUtil::sendFailure($this->lng->txt($ilErr->getMessage()));
	 		$this->editRecord();
	 		return false;
	 	}
	 	$this->record->update();
	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'));
	 	$this->showRecords();
	 	return true;
	}
	

	/**
	 * Show  
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function createRecord()
	{
		$this->initRecordObject();
		$this->initForm('create');
		$this->tpl->setContent($this->form->getHTML());				
		return true;
	}
	
	public function importRecords()
	{
		// Import Table
		$this->initImportForm();
		$this->tpl->setContent($this->import_form->getHTML());
	}
	
	/**
	 * show import form
	 *
	 * @access protected
	 */
	protected function initImportForm()
	{
		if(is_object($this->import_form))
		{
			return true;
		}
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->import_form = new ilPropertyFormGUI();
		$this->import_form->setMultipart(true);
		$this->import_form->setFormAction($this->ctrl->getFormAction($this));
		
		// add file property
		$file = new ilFileInputGUI($this->lng->txt('file'),'file');
		$file->setSuffixes(array('xml'));
		$file->setRequired(true);
		$this->import_form->addItem($file);
		
		$this->import_form->setTitle($this->lng->txt('md_adv_import_record'));
		$this->import_form->addCommandButton('importRecord',$this->lng->txt('import'));
		$this->import_form->addCommandButton('showRecords',$this->lng->txt('cancel'));
	}
	
	/**
	 * import xml file
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function importRecord()
	{
	 	$this->initImportForm();
	 	if(!$this->import_form->checkInput())
	 	{
			$this->import_form->setValuesByPost();
			$this->createRecord();
			return false;
	 	}
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordImportFiles.php');
	 	$import_files = new ilAdvancedMDRecordImportFiles();
	 	if(!$create_time = $import_files->moveUploadedFile($_FILES['file']['tmp_name']))
	 	{
	 		$this->createRecord();
	 		return false;
	 	}
	 	
	 	try
	 	{
		 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordParser.php');
		 	$parser = new ilAdvancedMDRecordParser($import_files->getImportFileByCreationDate($create_time));
		 	
		 	// Validate
	 		$parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT_VALIDATION);
	 		$parser->startParsing();
	 		
	 		// Insert
	 		$parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT);
	 		$parser->startParsing();
	 		ilUtil::sendSuccess($this->lng->txt('md_adv_added_new_record'), true);
	 		$this->ctrl->redirect($this, "showRecords");
	 	}
	 	catch(ilSAXParserException $exc)
	 	{
	 		ilUtil::sendFailure($exc->getMessage(), true);
	 		$this->ctrl->redirect($this, "importRecords");
	 	}

		// Finally delete import file
		$import_files->deleteFileByCreationDate($create_time);
		return true;
	}
	
	
	/**
	 * Save record
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function saveRecord()
	{
	 	global $ilErr;
	 	
	 	$this->initRecordObject();
	 	$this->loadRecordFormData();
	 	
	 	if(!$this->record->validate())
	 	{
	 		ilUtil::sendFailure($this->lng->txt($ilErr->getMessage()));
	 		$this->createRecord();
	 		return false;
	 	}
	 	$this->record->save();
	 	ilUtil::sendSuccess($this->lng->txt('md_adv_added_new_record'));
	 	$this->showRecords();
	}
	
	/**
	 * Edit field
	 *
	 * @access public
	 * 
	 */
	public function editField(ilPropertyFormGUI $a_form = null)
	{
		if(!$_REQUEST["record_id"] || !$_REQUEST["field_id"])
		{
			return $this->editFields();
		}
		
		 $this->ctrl->saveParameter($this,'record_id');
		 $this->ctrl->saveParameter($this,'field_id');
		 		 
		 if(!$a_form)
		 {
			$field_definition = ilAdvancedMDFieldDefinition::getInstance((int)$_REQUEST['field_id']);
			$a_form = $this->initFieldForm($field_definition);
		 }
		 $this->tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Update field
	 *
	 * @access public
	 * 
	 */
	public function updateField()
	{
		global $ilErr;
		
		if(!$_REQUEST["record_id"] || !$_REQUEST["field_id"])
		{
			return $this->editFields();
		}
		
		$this->ctrl->saveParameter($this,'record_id');
		$this->ctrl->saveParameter($this,'field_id');		 		 
		 
		$confirm = false;
		$field_definition = ilAdvancedMDFieldDefinition::getInstance((int)$_REQUEST['field_id']);
		$form = $this->initFieldForm($field_definition);		
		if($form->checkInput())
		{
			$field_definition->importDefinitionFormPostValues($form, $this->getPermissions());			
			if(!$field_definition->importDefinitionFormPostValuesNeedsConfirmation())
			{
				$field_definition->update();

				ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
				$this->ctrl->redirect($this, "editFields");
			}
			else
			{
				$confirm = true;
			}
		}
		
		// fields needs confirmation of updated settings
		if($confirm)
		{
			ilUtil::sendInfo($this->lng->txt("md_adv_confirm_definition"));
			$field_definition->prepareDefinitionFormConfirmation($form);
		}		
		
		$form->setValuesByPost();
		$this->editField($form);		
	}
	
	/**
	 * Show field type selection
	 *
	 * @access public
	 * 
	 */
	public function createField(ilPropertyFormGUI $a_form = null)
	{
		
		if(!$_REQUEST["record_id"] || !$_REQUEST["ftype"])
		{
			return $this->editFields();
		}
		
	 	$this->ctrl->saveParameter($this,'record_id');
	 	$this->ctrl->saveParameter($this,'ftype');

		if(!$a_form)
		{		
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
			$field_definition = ilAdvancedMDFieldDefinition::getInstance(null, $_REQUEST["ftype"]);		
			$field_definition->setRecordId($_REQUEST["record_id"]);
			$a_form = $this->initFieldForm($field_definition);
		}
	 	$this->tpl->setContent($a_form->getHTML());	
	}
	
	/**
	 * create field
	 *
	 * @access public
	 */
	public function saveField()
	{
	 	global $ilErr;
	 	
	 	if(!$_REQUEST["record_id"] || !$_REQUEST["ftype"])
		{
			return $this->editFields();
		}
		
	 	$this->ctrl->saveParameter($this,'record_id');
	 	$this->ctrl->saveParameter($this,'ftype');
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	$field_definition = ilAdvancedMDFieldDefinition::getInstance(null, $_REQUEST["ftype"]);
		$field_definition->setRecordId($_REQUEST["record_id"]);
		$form = $this->initFieldForm($field_definition);
		
		if($form->checkInput())
		{
			$field_definition->importDefinitionFormPostValues($form, $this->getPermissions());
			$field_definition->save();
			
			ilUtil::sendSuccess($this->lng->txt('save_settings'), true);
			$this->ctrl->redirect($this, "editFields");			
		}
		
		$form->setValuesByPost();
		$this->createField($form);		
	}
		
	/**
	 * init field form
	 *
	 * @access protected
	 */
	protected function initFieldForm(ilAdvancedMDFieldDefinition $a_definition)
	{										
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$type = new ilNonEditableValueGUI($this->lng->txt("type"));
		$type->setValue($this->lng->txt($a_definition->getTypeTitle()));
		$form->addItem($type);
		
		$a_definition->addToFieldDefinitionForm($form, $this->getPermissions());
	
		if(!$a_definition->getFieldId())
		{			
			$form->setTitle($this->lng->txt('md_adv_create_field'));
			$form->addCommandButton('saveField',$this->lng->txt('create'));						
		}
		else
		{			
			$form->setTitle($this->lng->txt('md_adv_edit_field'));
			$form->addCommandButton('updateField',$this->lng->txt('save'));			
		}
		
		$form->addCommandButton('editFields',$this->lng->txt('cancel'));
		
		return $form;
	}
	
	/**
	 * Init Form 
	 *
	 * @access protected
	 */
	protected function initForm($a_mode)
	{
		if(is_object($this->form))
		{
			return true;
		}
		
		$perm = $this->getPermissions()->hasPermissions(
			ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
			$this->record->getRecordId(),
			array(
				array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE)
				,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION)
				,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
				,ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
		));
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->record->getTitle());
		$title->setSize(20);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		if(!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE])
		{
			$title->setDisabled(true);
		}
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		$desc->setValue($this->record->getDescription());
		$desc->setRows(3);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		if(!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION])
		{
			$desc->setDisabled(true);
		}
		
		// active
		$check = new ilCheckboxInputGUI($this->lng->txt('md_adv_active'),'active');
		$check->setChecked($this->record->isActive());
		$check->setValue(1);
		$this->form->addItem($check);
		
		if(!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION])
		{
			$check->setDisabled(true);
		}
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('md_obj_types'));
		$this->form->addItem($section);
		
		foreach(ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $type)
		{
			$t = $type["obj_type"].":".$type["sub_type"];
			$this->lng->loadLanguageModule($type["obj_type"]);
			$check = new ilCheckboxInputGUI($type["text"],'obj_types[]');
			$check->setChecked($this->record->isAssignedObjectType($type["obj_type"], $type["sub_type"]));
			$check->setValue($t);
			$this->form->addItem($check);
						
			if(!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES])
			{
				$check->setDisabled(true);
			}
		}				
		
		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('md_adv_create_record'));
				$this->form->addCommandButton('saveRecord',$this->lng->txt('add'));
				$this->form->addCommandButton('showRecords',$this->lng->txt('cancel'));
		
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('md_adv_edit_record'));
				$this->form->addCommandButton('updateRecord',$this->lng->txt('save'));
				$this->form->addCommandButton('showRecords',$this->lng->txt('cancel'));
				
				return true;
		}
	}
	
	protected function getSubstitutionFieldPermissions($a_obj_type, $a_field_id)
	{
		if($a_obj_type == "crs")
		{
			$perm =	$this->getPermissions()->hasPermissions(
				ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_COURSE,
				$a_field_id,
				array(
					ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD						
					,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
						ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
					,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
						ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
			));
			return array(
				"show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD]						
				,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
				,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
			);
		}
		else if($a_obj_type == "cat")
		{			
			$perm =	$this->getPermissions()->hasPermissions(
				ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_CATEGORY,
				$a_field_id,
				array(
					ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD							
					,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
						ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
					,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
						ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
			));
			return array(
				"show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD]						
				,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
				,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
			);
		}			
	}
	
	/**
	 * init form table 'substitutions'
	 *
	 * @access protected
	 */
	protected function initFormSubstitutions()
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		if(!$visible_records = ilAdvancedMDRecord::_getAllRecordsByObjectType())
		{
			return;
		}

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		#$this->form->setTableWidth('100%');

		// substitution
		foreach($visible_records as $obj_type => $records)
		{
			$perm = null;
			// :TODO: hardwird ?
			if(in_array($obj_type, array("crs", "cat")))
			{
				$perm =	$this->getPermissions()->hasPermissions(
					ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
					$obj_type,
					array(
						ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
						,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
						,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
				));
			}
			
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
			$sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
			
			// Show section
			$section = new ilFormSectionHeaderGUI();
			$section->setTitle($this->lng->txt('objs_'.$obj_type));
			$this->form->addItem($section);
			
			$check = new ilCheckboxInputGUI($this->lng->txt('description'),'enabled_desc_'.$obj_type);
			$check->setValue(1);
			$check->setOptionTitle($this->lng->txt('md_adv_desc_show'));
			$check->setChecked($sub->isDescriptionEnabled() ? true : false);
			$this->form->addItem($check);
			
			if($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION])
			{
				$check->setDisabled(true);
			}
			
			$check = new ilCheckboxInputGUI($this->lng->txt('md_adv_field_names'),'enabled_field_names_'.$obj_type);
			$check->setValue(1);
			$check->setOptionTitle($this->lng->txt('md_adv_fields_show'));
			$check->setChecked($sub->enabledFieldNames() ? true : false);
			$this->form->addItem($check);
			
			if($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES])
			{
				$check->setDisabled(true);
			}
			
			#$area = new ilTextAreaInputGUI($this->lng->txt('md_adv_substitution'),'substitution_'.$obj_type);
			#$area->setUseRte(true);
			#$area->setRteTagSet('standard');
			#$area->setValue(ilUtil::prepareFormOutput($sub->getSubstitutionString()));
			#$area->setRows(5);
			#$area->setCols(80);
			#$this->form->addItem($area);
			
			if($perm)
			{
				$perm_pos = $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS];
			}

			$definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($obj_type);
			$definitions = $sub->sortDefinitions($definitions);
		
			$counter = 1;
			foreach($definitions as $def)
			{				
				$definition_id = $def->getFieldId();
				
				$perm = $this->getSubstitutionFieldPermissions($obj_type, $definition_id);
			
				$title = ilAdvancedMDRecord::_lookupTitle($def->getRecordId());
				$title = $def->getTitle().' ('.$title.')';								
				
				$check = new ilCheckboxInputGUI($title,'show['.$obj_type.']['.$definition_id.']');
				$check->setValue(1);
				$check->setOptionTitle($this->lng->txt('md_adv_show'));
				$check->setChecked($sub->isSubstituted($definition_id));
				
				if($perm && !$perm["show"])
				{
					$check->setDisabled(true);
				}
				
				$pos = new ilNumberInputGUI($this->lng->txt('position'),'position['.$obj_type.']['.$definition_id.']');
				$pos->setSize(3);				
				$pos->setMaxLength(4);
				$pos->allowDecimals(true);				
				$pos->setValue(sprintf('%.1f',$counter++));
				$check->addSubItem($pos);
				
				if($perm && !$perm_pos)
				{
					$pos->setDisabled(true);
				}
				
				$bold = new ilCheckboxInputGUI($this->lng->txt('bold'),'bold['.$obj_type.']['.$definition_id.']');
				$bold->setValue(1);
				$bold->setChecked($sub->isBold($definition_id));
				$check->addSubItem($bold);
				
				if($perm && !$perm["bold"])
				{
					$bold->setDisabled(true);
				}

				$bold = new ilCheckboxInputGUI($this->lng->txt('newline'),'newline['.$obj_type.']['.$definition_id.']');
				$bold->setValue(1);
				$bold->setChecked($sub->hasNewline($definition_id));
				$check->addSubItem($bold);
				
				if($perm && !$perm["newline"])
				{
					$bold->setDisabled(true);
				}


				$this->form->addItem($check);
			}
			
			
			// placeholder
			/*
			$custom = new ilCustomInputGUI($this->lng->txt('md_adv_placeholders'));
			$tpl = new ilTemplate('tpl.placeholder_info.html',true,true,'Services/AdvancedMetaData');
			foreach($records as $record)
			{
				foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record->getRecordId()) as $definition)
				{
					$tpl->setCurrentBlock('field');
					$tpl->setVariable('FIELD_NAME',$definition->getTitle());
					$tpl->setVariable('MODULE_VARS','[IF_F_'.$definition->getFieldId().']...[F_'.$definition->getFieldId().']'.
						'[/IF_F_'.$definition->getFieldId().']');
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setCurrentBlock('record');
				$tpl->setVariable('PLACEHOLDER_FOR',$this->lng->txt('md_adv_placeholder_for'));
				$tpl->setVariable('TITLE',$record->getTitle());
				$tpl->parseCurrentBlock();
			}
			$custom->setHTML($tpl->get());
			$this->form->addItem($custom);
			*/
		}
		$this->form->setTitle($this->lng->txt('md_adv_substitution_table'));
		$this->form->addCommandButton('updateSubstitutions',$this->lng->txt('save'));
		return true;
	}
	
	/**
	 * load record form data
	 *
	 * @access protected
	 */
	protected function loadRecordFormData()
	{
		$perm = $this->getPermissions()->hasPermissions(
			ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
			$this->record->getRecordId(),
			array(
				array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE)
				,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION)
				,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,  
					ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
				,ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
			));
		
		if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION])
		{
			$this->record->setActive(ilUtil::stripSlashes($_POST['active']));
		}
		if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE])
		{
				$this->record->setTitle(ilUtil::stripSlashes($_POST['title']));
		}
		if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION])
		{
			$this->record->setDescription(ilUtil::stripSlashes($_POST['desc']));
		}
		if($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES])
		{					
			$obj_types = array();
			if (is_array($_POST['obj_types']))
			{
				foreach ($_POST['obj_types'] as $t)
				{
					$t = explode(":", $t);
					$obj_types[] = array(
						"obj_type" => ilUtil::stripSlashes($t[0]),
						"sub_type" => ilUtil::stripSlashes($t[1])
						);
				}
			}
			$this->record->setAssignedObjectTypes($obj_types);
		}
	}
	
	/**
	 * Init record object 
	 *
	 * @access protected
	 */
	protected function initRecordObject()
	{
		if(is_object($this->record))
		{
			return $this->record;
		}
		
		$record_id = isset($_GET['record_id']) ?
			$_GET['record_id'] :
			0; 
		return $this->record = ilAdvancedMDRecord::_getInstanceByRecordId($_GET['record_id']);
	}

	/**
	 * Set sub tabs
	 *
	 * @access protected
	 */
	protected function setSubTabs()
	{
		$this->tabs_gui->clearSubTabs();

		$this->tabs_gui->addSubTabTarget("md_adv_record_list",
								 $this->ctrl->getLinkTarget($this, "showRecords"),
								'',
								'',
								'',
								true);
						
		
		if(ilAdvancedMDRecord::_getAllRecordsByObjectType())
		{			
			$this->tabs_gui->addSubTabTarget("md_adv_presentation",
									 $this->ctrl->getLinkTarget($this, "showPresentation"));
		}
			
		$this->tabs_gui->addSubTabTarget("md_adv_file_list",
								 $this->ctrl->getLinkTarget($this, "showFiles"),
								 "showFiles");		
	}
	
	/**
	 * Get and cache record objects 
	 *
	 * @access protected
	 */
	protected function getRecordObjects()
	{
		if(!isset($this->record_objs))
		{
			return $this->record_objs = ilAdvancedMDRecord::_getRecords();
		}
		return $this->record_objs;
	}
	
}
?>