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
	private $values = array();
	private $search_values = array();

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
	
	/**
	 * Load values from post
	 *
	 * @access public
	 * 
	 */
	public function loadFromPost()
	{
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		
		if(!isset($_POST['md']))
		{
			return false;
		}
		foreach($_POST['md'] as $field_id => $value)
		{
			$def = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
			switch($def->getFieldType())
			{
				case ilAdvancedMDFieldDefinition::TYPE_DATE:

					if(is_array($value) and $_POST['md_activated'][$field_id])
					{
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) 0;
						$dt['minutes'] = (int) 0;
						$dt['seconds'] = (int) 0;
						$date = new ilDate($dt,IL_CAL_FKT_GETDATE);
						$value = $date->get(IL_CAL_UNIX);
					}
					else
					{
						$value = 0;
					}
					break;

				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
					
					if(is_array($value) and $_POST['md_activated'][$field_id])
					{
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) $value['time']['h'];
						$dt['minutes'] = (int) $value['time']['m'];
						$dt['seconds'] = (int) 0;
						$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE);
						$value = $date->get(IL_CAL_UNIX);
					}
					else
					{
						$value = 0;
					}
					break;
				
				default:
					$value = ilUtil::stripSlashes($value);
					break;
			}
			$val = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id,$this->sub_type,$this->sub_id);
			$val->setValue($value);
			$this->values[] = $val;
			unset($value);
		}
		$this->loadECSDurationPost();
	}
	
	/**
	 * load ecs duration post
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadECSDurationPost()
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSServerSettings.php');
		
		if(!ilECSServerSettings::getInstance()->activeServerExists())
		{
			return false;
		}
		$mapping = ilECSDataMappingSettings::_getInstance();
		
		if(!$start_id = $mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'begin'))
		{
			return false;
		}
		if(!$end_id = $mapping->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'end'))
		{
			return false;
		}
		if(!$_POST['md_activated'][$start_id])
		{
			$end = 0;
		}
		else
		{
			$end = $this->toUnixTime($_POST['md'][$start_id]['date'],$_POST['md'][$start_id]['time']);
			$end = $end + (60 * 60 * $_POST['ecs_duration']['hh']) + (60 * $_POST['ecs_duration']['mm']);
		}
		$val = ilAdvancedMDValue::_getInstance($this->obj_id,$end_id);
		$val->setValue($end);
		$this->values[] = $val;
		return true;
	}
	
	/**
	 * Save values
	 *
	 * @access public
	 * 
	 */
	public function saveValues()
	{
	 	foreach($this->values as $value)
	 	{
	 		$value->save();
	 	}
	 	return true;
	}
	
	/**
	 * Parse property form in editor mode
	 *
	 * @access private
	 * 
	 */
	private function parseEditor()
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
	 		$section = new ilFormSectionHeaderGUI();
	 		$section->setTitle($record_obj->getTitle());
	 		$section->setInfo($record_obj->getDescription());
	 		$this->form->addItem($section);
	 		
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record_obj->getRecordId()) as $def)
	 		{
	 			if($this->handleECSDefinitions($def))
	 			{
	 				continue;
	 			}
	 			
	 			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
				$value = ilAdvancedMDValue::_getInstance($this->obj_id,$def->getFieldId(),$this->sub_type,$this->sub_id);
	 			
	 			switch($def->getFieldType())
	 			{
	 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
	 					$text = new ilTextInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$text->setValue($value->getValue());
	 					$text->setSize(40);
	 					$text->setMaxLength(512);
	 					$text->setDisabled($value->isDisabled());
	 					$text->setInfo($def->getDescription());
	 					$this->form->addItem($text);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 					$select = new ilSelectInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$select->setOptions($def->getFieldValuesForSelect());
	 					$select->setValue($value->getValue());
	 					$select->setDisabled($value->isDisabled());
	 					$select->setInfo($def->getDescription());

	 					$this->form->addItem($select);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATE:
	 					
	 					$unixtime = $value->getValue() ? $value->getValue() : mktime(8,0,0,date('m'),date('d'),date('Y'));

	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$time->setShowTime(false);
						$time->setStartYear(1901);
	 					$time->setDate(new ilDate($unixtime,IL_CAL_UNIX));
	 					$time->enableDateActivation($this->lng->txt('enabled'),
							'md_activated['.$def->getFieldId().']',
							$value->getValue() ? true : false);
						$time->setDisabled($value->isDisabled());
						$time->setInfo($def->getDescription());
	 					$this->form->addItem($time);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:

	 					$unixtime = $value->getValue() ? $value->getValue() : mktime(8,0,0,date('m'),date('d'),date('Y'));

	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$time->setShowTime(true);
	 					$time->setDate(new ilDateTime($unixtime,IL_CAL_UNIX,$ilUser->getTimeZone()));
	 					$time->enableDateActivation($this->lng->txt('enabled'),
							'md_activated['.$def->getFieldId().']',
							$value->getValue() ? true : false);
						$time->setDisabled($value->isDisabled());
	 					$this->form->addItem($time);
	 					break;
	 			}
	 		}
	 	}
	}
	
	/**
	 * Parse search 
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function parseSearch()
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		foreach(ilAdvancedMDRecord::_getActiveSearchableRecords() as $record)
		{ 
			$section = new ilFormSectionHeaderGUI();
			$section->setTitle($record->getTitle());
			$this->form->addItem($section);
			
			foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record->getRecordId()) as $field)
			{
				if(!$field->isSearchable())
				{
					continue;
				}
	 			switch($field->getFieldType())
	 			{
	 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:

						$group = new ilRadioGroupInputGUI('','boolean['.$field->getFieldId().']');
						$group->setValue(isset($this->search_values['boolean'][$field->getFieldId()]) ? 
							$this->search_values['boolean'][$field->getFieldId()] : 0);
						$radio_option = new ilRadioOption($this->lng->txt("search_any_word"),0);
						$radio_option->setValue(0);
						$group->addOption($radio_option);
						$radio_option = new ilRadioOption($this->lng->txt("search_all_words"),1);
						$radio_option->setValue(1);
						$group->addOption($radio_option);
						
						$text = new ilTextInputGUI($field->getTitle(),$field->getFieldId());
						$text->setValue(isset($this->search_values[$field->getFieldId()]) ?
							$this->search_values[$field->getFieldId()] :
							'');
						$text->setSize(30);
						$text->setMaxLength(255);
						
						
						$text->addSubItem($group);
						$this->form->addItem($text);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 					$select = new ilSelectInputGUI($field->getTitle(),$field->getFieldId());
	 					$select->setValue(isset($this->search_values[$field->getFieldId()]) ?
	 						$this->search_values[$field->getFieldId()] :
	 						0);
						$options = array(0 => $this->lng->txt('search_any'));
						$counter = 1;
						foreach($field->getFieldValues() as $key => $value)
						{
							$options[$counter++] = $value;	
						}
						$select->setOptions($options);
						$this->form->addItem($select);	 					
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
	 				case ilAdvancedMDFieldDefinition::TYPE_DATE:
	 					$check = new ilCheckboxInputGUI($field->getTitle(),$field->getFieldId());
	 					$check->setValue(1);
	 					$check->setChecked(isset($this->search_values[$field->getFieldId()]) ?
	 						$this->search_values[$field->getFieldId()] : 0);
	 				
	 					$time = new ilDateTimeInputGUI($this->lng->txt('from'),'date_start['.$field->getFieldId().']');
	 					if($field->getFieldType() == ilAdvancedMDFieldDefinition::TYPE_DATE)
	 					{
	 						$time->setShowTime(false);
	 					}
	 					else
	 					{
	 						$time->setShowTime(true);
	 					}
						if(isset($this->search_values['date_start'][$field->getFieldId()]) and 0)
						{
							#$time->setUnixTime($this->toUnixTime($this->search_values['date_start'][$field->getFieldId()]['date'],$this->search_values['date_start'][$field->getFieldId()]['time']));							
						}
						else
						{
							$time->setDate(new ilDateTime(mktime(8,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
						}
	 					$check->addSubItem($time);

	 					$time = new ilDateTimeInputGUI($this->lng->txt('until'),'date_end['.$field->getFieldId().']');
	 					if($field->getFieldType() == ilAdvancedMDFieldDefinition::TYPE_DATE)
	 					{
	 						$time->setShowTime(false);
	 					}
	 					else
	 					{
	 						$time->setShowTime(true);
	 					}
						if(isset($this->search_values['date_end'][$field->getFieldId()]) and 0)
						{
							#$time->setUnixTime($this->toUnixTime($this->search_values['date_end'][$field->getFieldId()]['date'],$this->search_values['date_end'][$field->getFieldId()]['time']));							
						}
						else
						{
							$time->setDate(new ilDateTime(mktime(16,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
						}
	 					$check->addSubItem($time);

	 					$this->form->addItem($check);
	 					break;
	 			}
			}
		}
	}
	
	private function parseInfoPage()
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
	 	foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type, $this->sub_type) as $record_obj)
	 	{
	 		$this->info->addSection($record_obj->getTitle());
	 		
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record_obj->getRecordId()) as $def)
	 		{
	 			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
				$value = ilAdvancedMDValue::_getInstance($this->obj_id,$def->getFieldId());
	 			
	 			switch($def->getFieldType())
	 			{
	 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
	 					if($value->getValue())
	 					{
		 					$this->info->addProperty($def->getTitle(),$value->getValue());
	 					}
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 					if($value->getValue())
	 					{
	 						$this->info->addProperty($def->getTitle(),$value->getValue());
	 					}
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATE:
	 					if($value->getValue())
						{
							$this->info->addProperty($def->getTitle(),ilDatePresentation::formatDate(new ilDate($value->getValue(),IL_CAL_UNIX)));
						}
	 					break;
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
	 					if($value->getValue())
						{
							$this->info->addProperty($def->getTitle(),ilDatePresentation::formatDate(new ilDateTime($value->getValue(),IL_CAL_UNIX)));
						}
	 					break;
	 			}
	 		}
	 	}
		
	} 
	
	/**
	 * convert input array to unix time
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function toUnixTime($date,$time = array())
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}
	
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
	 			
	 			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
	 			
	 			switch($def->getFieldType())
	 			{
	 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
	 					$text = new ilTextInputGUI($def->getTitle(),'md_'.$def->getFieldId());
	 					$text->setSize(20);
	 					$text->setMaxLength(512);
	 					$text->setSubmitFormOnEnter(true);
	 					$this->table_gui->addFilterItem($text);
	 					$text->readFromSession();
	 					$this->table_gui->filter['md_'.$def->getFieldId()] = $text->getValue();
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 					include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
	 					$select = new ilSelectInputGUI($def->getTitle(),'md_'.$def->getFieldId());
	 					$select->setOptions($def->getFieldValuesForSelect());
	 					$this->table_gui->addFilterItem($select);
	 					$select->readFromSession();
	 					$this->table_gui->filter['md_'.$def->getFieldId()] = $select->getValue();
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATE:
	 					include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md_'.$def->getFieldId());
	 					$time->setShowTime(false);
	 					$time->enableDateActivation($this->lng->txt('enabled'),
							'md_activated['.$def->getFieldId().']', false);
	 					$this->table_gui->addFilterItem($time);
	 					$time->readFromSession();
	 					$this->table_gui->filter['md_'.$def->getFieldId()] = $time->getDate();
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:

	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md_'.$def->getFieldId());
	 					$time->setShowTime(true);
	 					$time->enableDateActivation($this->lng->txt('enabled'),
							'md_activated['.$def->getFieldId().']', false);
	 					$this->table_gui->addFilterItem($time);
	 					$time->readFromSession();
	 					$this->table_gui->filter['md_'.$def->getFieldId()] = $time->getValue();
	 					break;
	 			}
	 		}
	 	}
	}
	
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