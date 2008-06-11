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
	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$time->setShowTime(false);
	 					$time->setUnixTime($value->getValue());
	 					$time->enableDateActivation($this->lng->txt('enabled'),
							'md_activated['.$def->getFieldId().']',
							$value->getValue() ? true : false);
						$time->setDisabled($value->isDisabled());
						$time->setInfo($def->getDescription());
	 					$this->form->addItem($time);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
	 					$time = new ilDateTimeInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$time->setShowTime(true);
	 					$time->setUnixTime($value->getValue());
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
						if(isset($this->search_values['date_start'][$field->getFieldId()]))
						{
							$time->setUnixTime($this->toUnixTime($this->search_values['date_start'][$field->getFieldId()]['date'],$this->search_values['date_start'][$field->getFieldId()]['time']));							
						}
						else
						{
							$time->setUnixTime(mktime(8,0,0,date('m'),date('d'),date('Y')));
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
						if(isset($this->search_values['date_end'][$field->getFieldId()]))
						{
							$time->setUnixTime($this->toUnixTime($this->search_values['date_end'][$field->getFieldId()]['date'],$this->search_values['date_end'][$field->getFieldId()]['time']));							
						}
						else
						{
		 					$time->setUnixTime(mktime(16,0,0,date('m')+1,date('d'),date('Y')));
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
							$this->info->addProperty($def->getTitle(),ilFormat::formatUnixTime($value->getValue()));
						}
	 					break;
	 				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
	 					if($value->getValue())
						{
							$this->info->addProperty($def->getTitle(),ilFormat::formatUnixTime($value->getValue(),true));
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
	
}


?>