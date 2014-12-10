<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData
*/
class ilAdvancedMDRecordGUI
{
	const MODE_EDITOR = 1;
	const MODE_SEARCH = 2;
	const MODE_INFO = 3;
	
	// glossary
	const MODE_REC_SELECTION = 4;		// record selection (per object)
	const MODE_FILTER = 5;				// filter (as used e.g. in tables)
	const MODE_TABLE_HEAD = 6;				// table header (columns)
	const MODE_TABLE_CELLS = 7;			// table cells
	
	protected $lng;
	
	private $mode;
	private $obj_type;
	private $sub_type;
	private $obj_id;
	
	private $form;
	private $search_values = array();
	
	protected $editor_form; // [array]

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int mode either MODE_EDITOR or MODE_SEARCH
	 * @param int obj_type
	 * 
	 */
	public function __construct($a_mode,$a_obj_type = '',$a_obj_id = '', $a_sub_type = '', $a_sub_id = '')
	{
		global $lng;
	 	
	 	$this->lng = $lng;
	 	$this->mode = $a_mode;
	 	$this->obj_type = $a_obj_type;
	 	$this->obj_id = $a_obj_id;
	 	$this->sub_type = $a_sub_type;
	 	$this->sub_id = $a_sub_id;
	}
	
	/**
	 * set property form object
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setPropertyForm($form)
	{
	 	$this->form = $form;
	}
	
	/**
	 * Set values for search form
	 *
	 * @access public
	 * 
	 */
	public function setSearchValues($a_values)
	{
	 	$this->search_values = $a_values;
	}
	
	
	/**
	 * get info sections
	 *
	 * @access public
	 * @param object instance of ilInfoScreenGUI
	 * 
	 */
	public function setInfoObject($info)
	{
	 	$this->info = $info;
	}
	
	/**
	 * Set selected only flag
	 *
	 * @param boolean $a_val retrieve only records, that are selected by the object	
	 */
	function setSelectedOnly($a_val)
	{
		$this->selected_only = $a_val;
	}
	
	/**
	 * Get selected only flag
	 *
	 * @return boolean retrieve only records, that are selected by the object
	 */
	function getSelectedOnly()
	{
		return $this->selected_only;
	}
	
	/**
	 * Get HTML
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function parse()
	{
	 	switch($this->mode)
	 	{
	 		case self::MODE_EDITOR:
	 			return $this->parseEditor();
	 			
	 		case self::MODE_SEARCH:
	 			return $this->parseSearch();
	 		
	 		case self::MODE_INFO:
	 			return $this->parseInfoPage();
	 			
	 		case self::MODE_REC_SELECTION:
	 			return $this->parseRecordSelection();
	 			
	 		case self::MODE_FILTER:
	 			return $this->parseFilter();
	 			
	 		case self::MODE_TABLE_HEAD:
	 			return $this->parseTableHead();

	 		case self::MODE_TABLE_CELLS:
	 			return $this->parseTableCells();
	 			
	 		default:
	 			die('Not implemented yet');
	 	}
	}
		
	
	//
	// editor
	//
	
	/**
	 * Parse property form in editor mode
	 */
	protected function parseEditor()
	{	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		if ($this->getSelectedOnly())
		{
			$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type);
		}
		else
		{
			$recs = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type);
		}

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');		
		$this->editor_form = array();
		
	 	foreach($recs as $record_obj)
	 	{
			/* :TODO:
			if($this->handleECSDefinitions($def))
	 		{
	 			continue;
	 		}			 
			*/
			
			$record_id = $record_obj->getRecordId();
			
			$values = new ilAdvancedMDValues($record_id, $this->obj_id, $this->sub_type, $this->sub_id);
			$values->read();
			
			$adt_group_form = ilADTFactory::getInstance()->getFormBridgeForInstance($values->getADTGroup());
			$adt_group_form->setForm($this->form);
			$adt_group_form->setTitle($record_obj->getTitle());
			$adt_group_form->setInfo($record_obj->getDescription());
			
			foreach($values->getDefinitions() as $def)
			{
				$element = $adt_group_form->getElement($def->getFieldId());
				$element->setTitle($def->getTitle());
				$element->setInfo($def->getDescription());
				
				// definition may customize ADT form element
				$def->prepareElementForEditor($element);
				
				if($values->isDisabled($def->getFieldId()))
				{
					$element->setDisabled(true);
				}
			}
			
			$adt_group_form->addToForm();			
			
			$this->editor_form[$record_id] = array("values"=>$values, "form"=>$adt_group_form);
	 	}
	}	
	
	/**
	 * Load edit form values from post
	 * 
	 * @return bool
	 */
	public function importEditFormPostValues()
	{			
		// #13774
		if(!is_array($this->editor_form))
		{
			return false;
		}
		
		$valid = true;
		
		foreach($this->editor_form as $item)
		{		
			$item["form"]->importFromPost();
			if(!$item["form"]->validate())
			{									
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	/**
	 * Write edit form values to db
	 * 
	 * @return bool
	 */
	public function writeEditForm()
	{
		if(!sizeof($this->editor_form))
		{
			return false;
		}
		
		foreach($this->editor_form as $item)
		{					
			$item["values"]->write();
		}
		
		return true;
	}
	
	
	// 
	// search
	//		
	
	/**
	 * Parse search 
	 */
	private function parseSearch()
	{		
		// this is NOT used for the global search, see ilLuceneAdvancedSearchFields::getFormElement()
		// (so searchable flag is NOT relevant)
		// 
		// current usage: wiki page element "[amd] page list"
		
		$this->lng->loadLanguageModule('search');
		
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');		
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');			
		if ($this->getSelectedOnly())
		{
			$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type);
		}
		else
		{
			$recs = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type);
		}
		
		$this->search_form = array();
		foreach($recs as $record)
		{ 			
			$section = new ilFormSectionHeaderGUI();
			$section->setTitle($record->getTitle());
			$section->setInfo($record->getDescription());
			$this->form->addItem($section);
			
			foreach(ilAdvancedMDFieldDefinition::getInstancesByRecordId($record->getRecordId(), true) as $field)
			{									 			
	 			$field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($field->getADTDefinition(), true, false);				
				$field_form->setForm($this->form);
				$field_form->setElementId("advmd[".$field->getFieldId()."]");
				$field_form->setTitle($field->getTitle());			
				
				if(is_array($this->search_form_values) && 
					isset($this->search_form_values[$field->getFieldId()]))
				{
					$field->setSearchValueSerialized($field_form, $this->search_form_values[$field->getFieldId()]);
				}
				
				$field->prepareElementForSearch($field_form);
				
				$field_form->addToForm();
				
				$this->search_form[$field->getFieldId()] = array("def"=>$field, "value"=>$field_form);			
			}
		}		 
	}
	
	/**
	 * Load edit form values from post
	 * 
	 * @return array
	 */
	public function importSearchForm()
	{
		if(!sizeof($this->search_form))
		{
			return false;
		}
		
		$valid = true;
		$res = array();
		
		foreach($this->search_form as $field_id => $item)
		{		
			$item["value"]->importFromPost();
			if(!$item["value"]->validate())
			{									
				$valid = false;			
			}
			$value = $item["def"]->getSearchValueSerialized($item["value"]);
			if($value !== null)
			{
				$res[$field_id] = $value;
			}
		}
		
		if($valid)
		{
			return $res;
		}
	}
	
	public function setSearchFormValues(array $a_values)
	{
		$this->search_form_values = $a_values;
	}
	
	
	//
	// infoscreen
	//
	
	private function parseInfoPage()
	{				
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		include_once('Services/ADT/classes/class.ilADTFactory.php');
								
		foreach(ilAdvancedMDValues::getInstancesForObjectId($this->obj_id, $this->obj_type) as $record_id => $a_values)
		{					
			// this correctly binds group and definitions
			$a_values->read();
			
			$this->info->addSection(ilAdvancedMDRecord::_lookupTitle($record_id)); 
		
			$defs = $a_values->getDefinitions();									
			foreach($a_values->getADTGroup()->getElements() as $element_id => $element)				
			{								
				if(!$element->isNull())
				{									
					$this->info->addProperty($defs[$element_id]->getTitle(),
						ilADTFactory::getInstance()->getPresentationBridgeForInstance($element)->getHTML());					
				}
			}
		}						
	} 
	
					
	//
	// :TODO: ECS
	// 
	
	/**
	 * handle ecs definitions
	 *
	 * @access private
	 * @param object ilAdvMDFieldDefinition
	 * @return
	 */
	private function handleECSDefinitions($a_definition)
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSServerSettings.php');

		if(ilECSServerSettings::getInstance()->activeServerExists() or
			($this->obj_type != 'crs' and $this->obj_type != 'rcrs')
		)
		{
			return false;
		}
		return false;
		/*
		$mapping = ilECSDataMappingSettings::_getInstance();
		
		if($mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'begin') == $a_definition->getFieldId())
		{
			$this->showECSStart($a_definition);
			return true;
		}
		if($mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'end') == $a_definition->getFieldId())
		{
			return true;
		}
		if($mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'cycle') == $a_definition->getFieldId())
		{
			return true;
		}
		if($mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'room') == $a_definition->getFieldId())
		{
			return true;
		}
		*/
	}
	
	/**
	 * Show special form for ecs start
	 * 
	 * @access private
	 * @param object ilAdvMDFieldDefinition
	 */
	private function showECSStart($def)
	{
		global $ilUser;
		
		$this->lng->loadLanguageModule('ecs');
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
		$value_start = ilAdvancedMDValue::_getInstance($this->obj_id,$def->getFieldId());
		
		$unixtime = $value_start->getValue() ? $value_start->getValue() : mktime(8,0,0,date('m'),date('d'),date('Y'));
		
		$time = new ilDateTimeInputGUI($this->lng->txt('ecs_event_appointment'),'md['.$def->getFieldId().']');
		$time->setShowTime(true);
		$time->setDate(new ilDateTime($unixtime,IL_CAL_UNIX));
		$time->enableDateActivation($this->lng->txt('enabled'),
			'md_activated['.$def->getFieldId().']',
			$value_start->getValue() ? true : false);
		$time->setDisabled($value_start->isDisabled());
		
		$mapping = ilECSDataMappingSettings::_getInstance();
		if($field_id = $mapping->getMappingByECSName('end'))
		{
			$value_end = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id);
			
			list($hours,$minutes) = $this->parseDuration($value_start->getValue(),$value_end->getValue());
			
			$duration = new ilDurationInputGUI($this->lng->txt('ecs_duration'),'ecs_duration');
			$duration->setHours($hours);
			$duration->setMinutes($minutes);
			#$duration->setInfo($this->lng->txt('ecs_duration_info'));
			$duration->setShowHours(true);
			$duration->setShowMinutes(true);
			$time->addSubItem($duration);
		}

		if($field_id = $mapping->getMappingByECSName('cycle'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id);
			$cycle_def = new ilAdvancedMDFieldDefinition($field_id);
			switch($cycle_def->getFieldType())
			{
 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
 					$text = new ilTextInputGUI($cycle_def->getTitle(),'md['.$cycle_def->getFieldId().']');
 					$text->setValue($value->getValue());
 					$text->setSize(20);
 					$text->setMaxLength(512);
 					$text->setDisabled($value->isDisabled());
 					$time->addSubItem($text);
 					break;
 					
 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
 					$select = new ilSelectInputGUI($cycle_def->getTitle(),'md['.$cycle_def->getFieldId().']');
 					$select->setOptions($cycle_def->getFieldValuesForSelect());
 					$select->setValue($value->getValue());
 					$select->setDisabled($value->isDisabled());
 					$time->addSubItem($select);
 					break;
			}
		}
		if($field_id = $mapping->getMappingByECSName('room'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id);
			$room_def = new ilAdvancedMDFieldDefinition($field_id);
			switch($room_def->getFieldType())
			{
 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
 					$text = new ilTextInputGUI($room_def->getTitle(),'md['.$room_def->getFieldId().']');
 					$text->setValue($value->getValue());
 					$text->setSize(20);
 					$text->setMaxLength(512);
 					$text->setDisabled($value->isDisabled());
 					$time->addSubItem($text);
 					break;
 					
 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
 					$select = new ilSelectInputGUI($room_def->getTitle(),'md['.$room_def->getFieldId().']');
 					$select->setOptions($cycle_def->getFieldValuesForSelect());
 					$select->setValue($value->getValue());
 					$select->setDisabled($value->isDisabled());
 					$time->addSubItem($select);
 					break;
			}
		}
		$this->form->addItem($time);
	}

	/**
	 * parse hours and minutes from duration
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function parseDuration($u_start,$u_end)
	{
		if($u_start >= $u_end)
		{
			return array(0,0);
		}
		$diff = $u_end - $u_start;
		$hours = (int) ($diff / (60 * 60));
		$min = (int) (($diff % 3600) / 60);
		return array($hours,$min); 
	}

		
	//
	// glossary
	// 
	
	/**
	 * Parse property form in editor mode
	 *
	 * @access private
	 * 
	 */
	public function parseRecordSelection($a_sec_head = "")
	{
	 	global $ilUser;
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
	 	$first = true;
	 	foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type) as $record_obj)
	 	{
	 		$selected = ilAdvancedMDRecord::getObjRecSelection($this->obj_id, $this->sub_type);
	 		if ($first)
	 		{
	 			$first = false;
		 		$section = new ilFormSectionHeaderGUI();
		 		$sec_tit = ($a_sec_head == "")
		 			? $this->lng->txt("meta_adv_records")
		 			: $a_sec_head;
				$section->setTitle($sec_tit);
				$this->form->addItem($section);
	 		}
	 		
	 		// checkbox for each active record
	 		$cb = new ilCheckboxInputGUI($record_obj->getTitle(), "amet_use_rec[]");
	 		$cb->setInfo($record_obj->getDescription());
	 		$cb->setValue($record_obj->getRecordId());
	 		if (in_array($record_obj->getRecordId(), $selected))
	 		{
	 			$cb->setChecked(true);
	 		}
	 		$this->form->addItem($cb);
	 	}
	}
	
	/**
	 * Save selection per object
	 *
	 * @param
	 * @return
	 */
	function saveSelection()
	{
		$sel = ilUtil::stripSlashesArray($_POST["amet_use_rec"]);
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
	 	ilAdvancedMDRecord::saveObjRecSelection($this->obj_id, $this->sub_type, $sel);
	}

	/**
	 * Set table
	 *
	 * @param object $a_val table gui class	
	 */
	function setTableGUI($a_val)
	{
		$this->table_gui = $a_val;
	}
	
	/**
	 * Get table
	 *
	 * @return object table gui class
	 */
	function getTableGUI()
	{
		return $this->table_gui;
	}
	
	/**
	 * Set row data
	 *
	 * @param array $a_val assoc array of row data (containing md record data)	
	 */
	function setRowData($a_val)
	{
		$this->row_data = $a_val;
	}
	
	/**
	 * Get row data
	 *
	 * @return array assoc array of row data (containing md record data)
	 */
	function getRowData()
	{
		return $this->row_data;
	}
	
	/**
	 * Parse property for filter (table)
	 *
	 * @access private
	 * 
	 */
	private function parseFilter()
	{	 
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		if ($this->getSelectedOnly())
		{
			$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type);
		}
		else
		{
			$recs = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type);
		}
		
		$this->adt_search = array();
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	foreach($recs as $record_obj)
	 	{			
			$record_id = $record_obj->getRecordId();
			
			$defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
			foreach($defs as $def)
			{
				// :TODO: not all types supported yet
				if(!in_array($def->getType(), array(
					ilAdvancedMDFieldDefinition::TYPE_TEXT,
					ilAdvancedMDFieldDefinition::TYPE_SELECT,
					ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI,
					ilAdvancedMDFieldDefinition::TYPE_DATE,
					ilAdvancedMDFieldDefinition::TYPE_DATETIME)))
				{
					continue;
				}
				
				/* :TODO:
				if($this->handleECSDefinitions($def))
	 			{
	 				continue;
	 			}
				*/
				
				$this->adt_search[$def->getFieldId()] = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def->getADTDefinition(), true, false);
				$this->adt_search[$def->getFieldId()]->setTableGUI($this->table_gui);
				$this->adt_search[$def->getFieldId()]->setTitle($def->getTitle());
				$this->adt_search[$def->getFieldId()]->setElementId('md_'.$def->getFieldId());
				
				$this->adt_search[$def->getFieldId()]->loadFilter();
				$this->adt_search[$def->getFieldId()]->addToForm();					
			}		
	 	}
	}
	
	/**
	 * Import filter (post) values
	 */
	public function importFilter()
	{
		if(!is_array($this->adt_search))
		{
			return;
		}
		
		foreach($this->adt_search as $element)
		{
			$element->importFromPost();
		}	
	}
	
	/**
	 * Get SQL conditions for current filter value(s)
	 * 
	 * @return array
	 */
	public function getFilterElements()
	{
		if(!is_array($this->adt_search))
		{
			return;
		}
		
		$res = array();
		
		foreach($this->adt_search as $def_id => $element)
		{			
			if(!$element->isNull())
			{
				$res[$def_id] = $element;			
			}
		}	
		
		return $res;
	}
	
	
	//
	// :TODO: OBSOLETE?  not used in glossary
	// 
	
	/**
	 * Parse property for table head
	 */
	private function parseTableHead()
	{
	 	global $ilUser;
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		if ($this->getSelectedOnly())
		{
			$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type);
		}
		else
		{
			$recs = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type);
		}
	 	foreach($recs as $record_obj)
	 	{
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record_obj->getRecordId()) as $def)
	 		{
	 			if($this->handleECSDefinitions($def))
	 			{
	 				continue;
	 			}
	 			
	 			$this->table_gui->addColumn($def->getTitle(),'md_'.$def->getFieldId());
	 		}
	 	}
	}

	/**
	 * Parse table cells
	 */
	private function parseTableCells()
	{
	 	global $ilUser;
	 	
	 	$data = $this->getRowData();
	 	
	 	$html = "";
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		if ($this->getSelectedOnly())
		{
			$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type);
		}
		else
		{
			$recs = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type);
		}
	 	foreach($recs as $record_obj)
	 	{
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record_obj->getRecordId()) as $def)
	 		{
	 			if($this->handleECSDefinitions($def))
	 			{
	 				continue;
	 			}
	 			
	 			$html.= "<td class='std'>".$data['md_'.$def->getFieldId()]."</td>";
	 		}
	 	}
	 	return $html;
	}
	
}


?>