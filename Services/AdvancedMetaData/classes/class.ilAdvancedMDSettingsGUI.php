<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilAdvancedMDSettingsGUI:
* @ingroup ServicesAdvancedMetaData
*/

include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDSettingsGUI
{
	protected $lng;
	protected $tpl;
	protected $ctrl;
	protected $tabs;
	
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
		$this->record_objs = $this->getRecordObjects();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_records.html','Services/AdvancedMetaData');

		include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordTableGUI.php");
		$table_gui = new ilAdvancedMDRecordTableGUI($this, "showRecords");
		$table_gui->setTitle($this->lng->txt("md_record_list_table"));
		$table_gui->parseRecords($this->record_objs);
		$table_gui->addCommandButton("updateRecords", $this->lng->txt("save"));
		$table_gui->addCommandButton('createRecord',$this->lng->txt('add'));
		$table_gui->addMultiCommand("exportRecords",$this->lng->txt('export'));
		$table_gui->addMultiCommand("confirmDeleteRecords", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("record_id");
		$this->tpl->setVariable('RECORD_TABLE',$table_gui->getHTML());
		
		if(!$this->initFormSubstitutions())
		{
			return true;
		}
		if (is_object($this->form))
		{
			$this->tpl->setVariable('SUBSTITUTION_TABLE',$this->form->getHTML());
		}
		return true;
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
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
	 		$sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
	 		$sub->setSubstitutions(array());
			$sub->enableDescription($_POST['enabled_desc_'.$obj_type]);
			asort($_POST['position'][$obj_type],SORT_NUMERIC);
			foreach($_POST['position'][$obj_type] as $field_id => $pos)
			{
				if(isset($_POST['show'][$obj_type][$field_id]) and $_POST['show'][$obj_type][$field_id])
				{
					$sub->appendSubstitution($field_id);
				}			
			}
			$sub->update();
		}
		
	 	
	 	/*
	 	foreach(ilAdvancedMDRecord::_getAllRecordsByObjectType() as $obj_type => $visible_record)
	 	{
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
	 		$sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
	 		$sub->setSubstitutionString(($_POST['substitution_'.$obj_type]));
	 		$sub->enableDescription($_POST['enabled_desc_'.$obj_type]);
	 		$sub->update();
	 	}
	 	*/
	 	ilUtil::sendInfo($this->lng->txt('settings_saved'));
	 	$this->showRecords();
	 	return true;
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordXMLWriter.php');
	 	$xml_writer = new ilAdvancedMDRecordXMLWriter($_POST['record_id']);
	 	$xml_writer->write();
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
	 	$export_files = new ilAdvancedMDRecordExportFiles();
	 	$export_files->create($xml_writer->xmlDumpMem());
	 	
	 	ilUtil::sendInfo($this->lng->txt('md_adv_records_exported'));
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
	 		ilUtil::sendInfo($this->lng->txt('md_adv_select_one_file'));
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->editFiles();
	 		return false;
	 	}

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
		$files = new ilAdvancedMDRecordExportFiles();
		
		foreach($_POST['file_id'] as $file_id)
		{
			$files->deleteByFileId((int) $file_id);
		}
		ilUtil::sendInfo($this->lng->txt('md_adv_deleted_files'));
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
		foreach($_POST['record_id'] as $record_id)
		{
			$record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
			$record->delete();			
		}
		ilUtil::sendInfo($this->lng->txt('md_adv_deleted_records'));
		$this->showRecords();
		return true; 	
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
			$new_types = isset($_POST['obj_types'][$record_obj->getRecordId()]) ?
				$_POST['obj_types'][$record_obj->getRecordId()] :
				array();
			$record_obj->setAssignedObjectTypes($new_types);
			$record_obj->setActive(isset($_POST['active'][$record_obj->getRecordId()]));
			$record_obj->update();
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
	 	$this->showRecords();
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->editRecord();
	 		return false;
	 	}
		$this->ctrl->saveParameter($this,'record_id');
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFields"));
		$c_gui->setHeaderText($this->lng->txt("md_adv_delete_fields_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "showRecords");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteFields");

		// add items to delete
		foreach($_POST["field_id"] as $field_id)
		{
			$field = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
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
	 	if(!isset($_POST['field_id']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->editRecord();
	 		return false;
	 	}
		foreach($_POST["field_id"] as $field_id)
		{
			$field = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
			$field->delete();
		}	 	
	 	ilUtil::sendInfo($this->lng->txt('md_adv_deleted_fields'));
	 	$this->editRecord();
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
	 	$_SESSION['num_values'] = 5;
	 	
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.edit_record.html','Services/AdvancedMetaData');
	 	$this->ctrl->saveParameter($this,'record_id');
	 	$this->initRecordObject();
	 	$this->initForm('edit');
	 	$this->tpl->setVariable('EDIT_RECORD_TABLE',$this->form->getHTML());
	 	
		// show field table
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$fields = ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($this->record->getRecordId());

		include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldTableGUI.php");
		$table_gui = new ilAdvancedMDFieldTableGUI($this, "editRecord");
		$table_gui->setTitle($this->lng->txt("md_adv_field_table"));
		$table_gui->parseDefinitions($fields);
		$table_gui->addCommandButton("updateFields", $this->lng->txt("save"));
		$table_gui->addCommandButton('createField',$this->lng->txt('add'));
		$table_gui->addCommandButton('showRecords',$this->lng->txt('cancel'));
		$table_gui->addMultiCommand("confirmDeleteFields", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("field_id");
		
		$this->tpl->setVariable('FIELDS_TABLE',$table_gui->getHTML());
	}
	
	/**
	 * Update fields 
	 *
	 * @access public
	 * 
	 */
	public function updateFields()
	{
	 	if(!isset($_GET['record_id']) or !$_GET['record_id'])
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}

		if(!isset($_POST['position']) or !is_array($_POST['position']))
		{
			$this->showRecords();
			return false;
		}
		// sort by position
		asort($_POST['position'],SORT_NUMERIC);
		$counter = 1;
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		foreach($_POST['position'] as $field_id => $pos)
		{
			$definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
			$definition->setPosition($counter++);
			$definition->enableSearchable(isset($_POST['searchable'][$field_id]) ? true : false);
			$definition->update();
		}
		
	 	ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editRecord();
		return true;	 	
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->showRecords();
	 		return false;
	 	}
	 	$this->initRecordObject();
	 	$this->loadRecordFormData();
	 	
	 	if(!$this->record->validate())
	 	{
	 		ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
	 		$this->editRecord();
	 		return false;
	 	}
	 	$this->record->update();
	 	ilUtil::sendInfo($this->lng->txt('settings_saved'));
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
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.new_record.html','Services/AdvancedMetaData');
		
		$this->initRecordObject();
		$this->initForm('create');
		$this->tpl->setVariable('NEW_RECORD_TABLE',$this->form->getHTML());
		
		// Import Table
		$this->initImportForm();
		$this->tpl->setVariable('IMPORT_RECORD_TABLE',$this->import_form->getHTML());
		return true;
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
		$this->import_form->setFormAction($this->ctrl->getFormAction($this));
		
		// add file property
		$file = new ilFileInputGUI($this->lng->txt('file'),'file');
		$file->setSuffixes(array('xml'));
		$file->setRequired(true);
		$this->import_form->addItem($file);
		
		$this->import_form->setTitle($this->lng->txt('md_adv_import_record'));
		$this->import_form->addCommandButton('importRecord',$this->lng->txt('import'));
		$this->import_form->addCommandButton('editRecord',$this->lng->txt('cancel'));
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
	 		ilUtil::sendInfo($this->lng->txt('md_adv_added_new_record'));
	 		$this->showRecords();
	 	}
	 	catch(ilSAXParserException $exc)
	 	{
	 		ilUtil::sendInfo($exc->getMessage());
	 		$this->createRecord();
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
	 		ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
	 		$this->createRecord();
	 		return false;
	 	}
	 	$this->record->save();
	 	ilUtil::sendInfo($this->lng->txt('md_adv_added_new_record'));
	 	$this->showRecords();
	}
	
	/**
	 * Edit field
	 *
	 * @access public
	 * 
	 */
	public function editField()
	{
		 $this->ctrl->saveParameter($this,'record_id');
		 $this->ctrl->saveParameter($this,'field_id');
		 
		 $this->field_definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId((int) $_GET['field_id']);
		 $this->initFieldForm('edit');
		 $this->tpl->setContent($this->form->getHTML());
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
		
		$this->ctrl->saveParameter($this,'record_id');
		 
		$this->field_definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId((int) $_GET['field_id']);
		$this->loadFieldFormData();
		
		if(!$this->field_definition->validate())
		{
			ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
			$this->editField();
			return false;
		}
		$this->field_definition->update();
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editRecord();
	}
	
	/**
	 * Show field type selection
	 *
	 * @access public
	 * 
	 */
	public function createField()
	{
	 	$this->ctrl->saveParameter($this,'record_id');

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$this->field_definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId(0);
	 	$this->initFieldForm('create');
	 	$this->tpl->setContent($this->form->getHTML());	
	}
	
	/**
	 * add value
	 *
	 * @access public
	 * 
	 */
	public function addValue()
	{
	 	++$_SESSION['num_values'];
	 	
 		$this->field_definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId(isset($_GET['field_id']) ? (int) $_GET['field_id'] : 0);
 		$this->loadFieldFormData();
		
	 	if(isset($_GET['field_id']) and $_GET['field_id'])
	 	{
	 		$this->editField();
	 	}
	 	else
	 	{
		 	$this->createField();
	 	}
	}
	
	/**
	 * create field
	 *
	 * @access public
	 */
	public function saveField()
	{
	 	global $ilErr;
	 	
	 	$this->ctrl->saveParameter($this,'record_id');
	 	$this->field_definition = ilAdvancedMDFieldDefinition::_getInstanceByFieldId(0);
		$this->loadFieldFormData();
		
		if(!$this->field_definition->validate())
		{
			ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
			$this->createField();
			return false;
		}
		$this->field_definition->add();
		ilUtil::sendInfo($this->lng->txt('save_settings'));
		$this->editRecord();
	}
	
	
	/**
	 * init field form
	 *
	 * @access protected
	 */
	protected function initFieldForm($a_mode)
	{
		if(is_object($this->field_form))
		{
			return true;
		}
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->field_definition->getTitle());
		$title->setSize(20);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'description');
		$desc->setValue($this->field_definition->getDescription());
		$desc->setRows(3);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		// Searchable
		$check = new ilCheckboxInputGUI($this->lng->txt('md_adv_searchable'),'searchable');
		$check->setChecked($this->field_definition->isSearchable());
		$check->setValue(1);
		$this->form->addItem($check);
		
		// field type
		$radio = new ilRadioGroupInputGUI($this->lng->txt('field_type'), "field_type");
		$radio->setValue($this->field_definition->getFieldType() ? 
			$this->field_definition->getFieldType() : 
			ilAdvancedMDFieldDefinition::TYPE_TEXT);
		$radio->setRequired(true);

		$radio_option = new ilRadioOption($this->lng->txt("udf_type_text"),ilAdvancedMDFieldDefinition::TYPE_TEXT);
		$radio->addOption($radio_option);

		$radio_option = new ilRadioOption($this->lng->txt("udf_type_date"),ilAdvancedMDFieldDefinition::TYPE_DATE);
		$radio->addOption($radio_option);

		$radio_option = new ilRadioOption($this->lng->txt("udf_type_select"),ilAdvancedMDFieldDefinition::TYPE_SELECT);
		$radio->addOption($radio_option);
		
		$values = $this->field_definition->getFieldValues();
		$max_values = max(count($values),$_SESSION['num_values']);
		for($i = 1; $i <= $max_values;$i++)
		{
			$title = new ilTextInputGUI($this->lng->txt('udf_value').' '.$i,'value_'.$i);
			$title->setValue(isset($values[$i - 1]) ? $values[$i - 1] : '');
			$title->setSize(20);
			$title->setMaxLength(70);
			$radio_option->addSubItem($title);
		}
		$this->form->addItem($radio);
		
		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('md_adv_create_field'));
				$this->form->addCommandButton('saveField',$this->lng->txt('create'));
				$this->form->addCommandButton('addValue',$this->lng->txt('md_adv_add_value'));
				$this->form->addCommandButton('editRecord',$this->lng->txt('cancel'));
		
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('md_adv_edit_field'));
				$this->form->addCommandButton('updateField',$this->lng->txt('save'));
				$this->form->addCommandButton('addValue',$this->lng->txt('md_adv_add_value'));
				$this->form->addCommandButton('editRecord',$this->lng->txt('cancel'));
				
				return true;
		}
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
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		$desc->setValue($this->record->getDescription());
		$desc->setRows(3);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		// active
		$check = new ilCheckboxInputGUI($this->lng->txt('md_adv_active'),'active');
		$check->setChecked($this->record->isActive());
		$check->setValue(1);
		$this->form->addItem($check);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('md_obj_types'));
		$this->form->addItem($section);
		
		foreach(ilAdvancedMDRecord::_getAssignableObjectTypes() as $type)
		{
			$check = new ilCheckboxInputGUI($this->lng->txt('objs_'.$type),'obj_types[]');
			$check->setChecked(in_array($type,$this->record->getAssignedObjectTypes()) ? true : false);
			$check->setValue($type);
			$this->form->addItem($check);
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
			return true;
		}

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTableWidth('100%');

		// substitution
		foreach($visible_records as $obj_type => $records)
		{
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
			$sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
			
			// Show section
			$section = new ilFormSectionHeaderGUI();
			$section->setSectionIcon(ilUtil::getImagePath('icon_'.$obj_type.'_s.gif'),$this->lng->txt('objs_'.$obj_type));
			$section->setTitle($this->lng->txt('objs_'.$obj_type));
			$this->form->addItem($section);
			
			$check = new ilCheckboxInputGUI($this->lng->txt('description'),'enabled_desc_'.$obj_type);
			$check->setValue(1);
			$check->setOptionTitle($this->lng->txt('md_adv_desc_show'));
			$check->setChecked($sub->isDescriptionEnabled() ? true : false);
			$this->form->addItem($check);
			
			#$area = new ilTextAreaInputGUI($this->lng->txt('md_adv_substitution'),'substitution_'.$obj_type);
			#$area->setUseRte(true);
			#$area->setRteTagSet('standard');
			#$area->setValue(ilUtil::prepareFormOutput($sub->getSubstitutionString()));
			#$area->setRows(5);
			#$area->setCols(80);
			#$this->form->addItem($area);

			$definitions = ilAdvancedMDFieldDefinition::_getActiveDefinitionsByObjType($obj_type);
			$definitions = $sub->sortDefinitions($definitions);
			
			$counter = 1;
			foreach($definitions as $definition_id)
			{
				$def = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($definition_id);
				
				$check = new ilCheckboxInputGUI($def->getTitle(),'show['.$obj_type.']['.$definition_id.']');
				$check->setValue(1);
				$check->setOptionTitle($this->lng->txt('md_adv_show'));
				$check->setChecked($sub->isSubstituted($definition_id));
				
				$pos = new ilTextInputGUI($this->lng->txt('position').':','position['.$obj_type.']['.$definition_id.']');
				$pos->setSize(3);
				$pos->setMaxLength(4);
				$pos->setValue(sprintf('%.1f',$counter++));
				$check->addSubItem($pos);
				
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
		$this->record->setActive(ilUtil::stripSlashes($_POST['active']));
		$this->record->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->record->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->record->setAssignedObjectTypes(isset($_POST['obj_types']) ? $_POST['obj_types'] : array());
	}
	
	/**
	 * load field definition from form data 
	 *
	 * @access protected
	 */
	protected function loadFieldFormData()
	{
		$this->field_definition->setRecordId((int) $_GET['record_id']);
		$this->field_definition->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->field_definition->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->field_definition->enableSearchable(isset($_POST['searchable']) ? true : false);
		$this->field_definition->setFieldType(ilUtil::stripSlashes($_POST['field_type']));
		$this->field_definition->setFieldValues(array());
		
		for($i = 1; $i <= $_SESSION['num_values'];$i++)
		{
			if(isset($_POST['value_'.$i]))
			{
				$this->field_definition->appendFieldValue($_POST['value_'.$i]);
			}
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
			
		$this->tabs_gui->addSubTabTarget("md_adv_file_list",
								 $this->ctrl->getLinkTarget($this, "showFiles"),
								 "showFiles",
								 array(),
								 '',
								 false);
		
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