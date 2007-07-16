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
		$table_gui->addMultiCommand("confirmDeleteRecords", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("record_id");
		
		$this->tpl->setVariable('RECORD_TABLE',$table_gui->getHTML());
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
		$c_gui->setHeaderText($this->lng->txt("md_delete_record_sure"));
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
		ilUtil::sendInfo($this->lng->txt('md_advanced_deleted_records'));
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

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFields"));
		$c_gui->setHeaderText($this->lng->txt("md_advanced_delete_fields_sure"));
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
	 * Edit one record
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function editRecord()
	{
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
		$table_gui->setTitle($this->lng->txt("md_advanced_field_table"));
		$table_gui->parseDefinitions($fields);
		$table_gui->addCommandButton("updateFields", $this->lng->txt("save"));
		$table_gui->addCommandButton('createField',$this->lng->txt('add'));
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
	 	ilUtil::sendInfo($this->lng->txt('md_advanced_added_new_record'));
	 	$this->showRecords();
	}
	
	/**
	 * Set sub tabs
	 *
	 * @access protected
	 */
	protected function setSubTabs()
	{
		$this->tabs_gui->clearSubTabs();
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
		$title = new ilTextInputGUI($this->lng->txt('record_title'),'title');
		$title->setValue($this->record->getTitle());
		$title->setSize(20);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('record_desc'),'desc');
		$desc->setValue($this->record->getDescription());
		$desc->setRows(3);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
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
				$this->form->setTitle($this->lng->txt('md_advanced_create'));
				$this->form->addCommandButton('saveRecord',$this->lng->txt('add'));
				$this->form->addCommandButton('showRecords',$this->lng->txt('cancel'));
		
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('md_advanced_edit'));
				$this->form->addCommandButton('updateRecord',$this->lng->txt('save'));
				$this->form->addCommandButton('showRecords',$this->lng->txt('cancel'));
				
				return true;
		}
	}
	
	/**
	 * load record form data
	 *
	 * @access protected
	 */
	protected function loadRecordFormData()
	{
		$this->record->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->record->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->record->setAssignedObjectTypes(isset($_POST['obj_types']) ? $_POST['obj_types'] : array());
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
}
?>