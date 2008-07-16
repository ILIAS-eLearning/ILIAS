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
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData
*/
class ilAdvancedMDRecordGUI
{
	const MODE_EDITOR = 1;
	const MODE_SEARCH = 2;
	const MODE_INFO = 3;
	
	protected $lng;
	
	private $mode;
	private $obj_type;
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
	public function __construct($a_mode,$a_obj_type = '',$a_obj_id = '')
	{
		global $lng;
	 	
	 	$this->lng = $lng;
	 	$this->mode = $a_mode;
	 	$this->obj_type = $a_obj_type;
	 	$this->obj_id = $a_obj_id;
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
				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
					
					if(is_array($value) and $_POST['md_activated'][$field_id])
					{
						$value = $this->toUnixTime($value['date'],$value['time']);
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
			$val = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id);
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
		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		
		if(!ilECSSettings::_getInstance()->isEnabled())
		{
			return false;
		}
		$mapping = ilECSDataMappingSettings::_getInstance();
		
		if(!$start_id = $mapping->getMappingByECSName('begin'))
		{
			return false;
		}
		if(!$end_id = $mapping->getMappingByECSName('end'))
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
	 	foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type) as $record_obj)
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
				$value = ilAdvancedMDValue::_getInstance($this->obj_id,$def->getFieldId());
	 			
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
							$time->setDate(new iDateTime(mktime(8,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
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
							$time->setDate(new iDateTime(mktime(16,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
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
	 	foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type) as $record_obj)
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
		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		
		if(!ilECSSettings::_getInstance()->isEnabled() or ($this->obj_type != 'crs' and $this->obj_type != 'rcrs'))
		{
			return false;
		}
		$mapping = ilECSDataMappingSettings::_getInstance();
		
		if($mapping->getMappingByECSName('begin') == $a_definition->getFieldId())
		{
			$this->showECSStart($a_definition);
			return true;
		}
		if($mapping->getMappingByECSName('end') == $a_definition->getFieldId())
		{
			return true;
		}
		if($mapping->getMappingByECSName('cycle') == $a_definition->getFieldId())
		{
			return true;
		}
		if($mapping->getMappingByECSName('room') == $a_definition->getFieldId())
		{
			return true;
		}
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
	
}


?>