<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/** 
 * AMD field type select
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelect extends ilAdvancedMDFieldDefinition
{		
	protected $options = array();
	protected $confirm_objects; // [array]
	protected $confirmed_objects; // [array]
	
	
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_SELECT;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
		$def->setNumeric(false);
		
		$options = $this->getOptions();
		$def->setOptions(array_combine($options, $options));
		
		return $def;
	}	
	
	
	// 
	// properties
	// 
	
	/**
	 * Set options
	 *
	 * @param array $a_values
	 */
	public function setOptions(array $a_values = null)
	{
		if($a_values !== null)
		{
			foreach($a_values as $idx => $value)
			{
				$a_values[$idx] = trim($value);
				if(!$a_values[$idx])
				{
					unset($a_values[$idx]);
				}
			}
			$a_values = array_unique($a_values);
			// sort($a_values);
		}
		$this->options = $a_values;	 
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	public function getOptions()
	{
	 	return $this->options;
	}
	
	
	//
	// definition (NOT ADT-based)
	// 
	
	protected function importFieldDefinition(array $a_def)
	{
		$this->setOptions($a_def);
	}
	
	protected function getFieldDefinition()
	{
		return $this->options;
	}
	
	public function getFieldDefinitionForTableGUI()
	{
		global $lng;
		
		return array($lng->txt("options") => implode(",", $this->getOptions()));		
	}
	
	/**
	 * Add input elements to definition form
	 *
	 * @param ilPropertyFormGUI $a_form
	 * @param bool $a_disabled
	 */
	public function addCustomFieldToDefinitionForm(ilPropertyFormGUI $a_form, $a_disabled = false)
	{
		global $lng;
		
		$field = new ilTextInputGUI($lng->txt("options"), "opts");			
		$field->setRequired(true);
		$field->setMulti(true);
		$field->setMaxLength(255); // :TODO:
		$a_form->addItem($field);
		
		$options = $this->getOptions();
		if($options)
		{						
			$field->setMultiValues($options);
			$field->setValue(array_shift($options));
		}
		
		if($a_disabled)
		{
			$field->setDisabled(true);
		}
	}
	
	/**
	 * Import custom post values from definition form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form)
	{		
		$old = $this->getOptions();
		$new = $a_form->getInput("opts");
		
		$missing = array_diff($old, $new);
		if(sizeof($missing))
		{			
			$this->confirmed_objects = $a_form->getInput("conf");
			$this->confirmed_objects = $this->confirmed_objects[$this->getFieldId()];
			if(!is_array($this->confirmed_objects))
			{					
				ilADTFactory::initActiveRecordByType();
				$primary = array(
					"field_id" => array("integer", $this->getFieldId()),
					ilADTActiveRecordByType::SINGLE_COLUMN_NAME => array("text", $missing)
				);
				$in_use = ilADTActiveRecordByType::readByPrimary("adv_md_values", $primary, "Enum");				
				if($in_use)
				{
					$this->confirm_objects = array();
					foreach($in_use as $item)
					{
						$this->confirm_objects[$item[ilADTActiveRecordByType::SINGLE_COLUMN_NAME]][] = array($item["obj_id"], $item["sub_type"], $item["sub_id"]);
					}
				}
			}
		}
		
		$this->setOptions($new);	
	}	
	
	public function importDefinitionFormPostValuesNeedsConfirmation()
	{
		return sizeof($this->confirm_objects);
	}
	
	public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form)
	{
		global $lng, $objDefinition;
		
		$a_form->getItemByPostVar("opts")->setDisabled(true);
		
		if(sizeof($this->confirm_objects))
		{			
			$new_options = $a_form->getInput("opts");		
			
			$sec = new ilFormSectionHeaderGUI();
			$sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
			$a_form->addItem($sec);
			
			foreach($this->confirm_objects as $option => $items)
			{
				$opt = new ilNonEditableValueGUI($lng->txt("md_adv_confirm_definition_select_option").': "'.$option.'"');
				$a_form->addItem($opt);
				
				foreach($items as $item)
				{
					$obj_id = $item[0];
					$sub_type = $item[1];
					$sub_id = $item[2];
					
					$item_id = $obj_id."_".$sub_type."_".$sub_id;
					
					$type = ilObject::_lookupType($obj_id);
					$type_title = $lng->txt("obj_".$type);
					$title = ' "'.ilObject::_lookupTitle($obj_id).'"';
					
					if($sub_id)
					{					
						$class = "ilObj".$objDefinition->getClassName($type);						
						$class_path = $objDefinition->getLocation($type);
						include_once $class_path."/class.".$class.".php";
						if(class_implements($class, ilAdvancedMetaDataSubItem))
						{
							$sub_title = $class::getAdvMDSubItemTitle($obj_id, $sub_type, $sub_id);
							if($sub_title)
							{
									$title .= ' ('.$sub_title.')';
							}
						}											
					}
					
					$sel = new ilSelectInputGUI($type_title.' '.$title, 
						"conf[".$this->getFieldId()."][".$option."][".$item_id."]");					
					$options = array(""=>$lng->txt("md_adv_confirm_definition_select_option_remove"));
					foreach($new_options as $new_option)
					{
						$options[$new_option] = $lng->txt("md_adv_confirm_definition_select_option_overwrite").': "'.$new_option.'"';
					}
					$sel->setOptions($options);
					
					$opt->addSubItem($sel);
				}
				
				
			}
		}		
	}
	
	
	//
	// definition CRUD 
	//
	
	public function update()
	{
		parent::update();
		
		if(sizeof($this->confirmed_objects))
		{
			ilADTFactory::initActiveRecordByType();
			foreach($this->confirmed_objects as $old_option => $item_ids)
			{
				foreach($item_ids as $item => $new_option)
				{
					$item = explode("_", $item);
					$obj_id = $item[0];
					$sub_type = $item[1];
					$sub_id = $item[2];
					
					if(!$new_option)
					{
						// remove existing value
						$primary = array(
							"obj_id" => array("integer", $obj_id),
							"sub_type" => array("text", $sub_type),
							"sub_id" => array("integer", $sub_id),
							"field_id" => array("integer", $this->getFieldId())
						);
						ilADTActiveRecordByType::deleteByPrimary("adv_md_values", $primary, "Enum");
					}
					else
					{
						// update existing value
						$primary = array(
							"obj_id" => array("integer", $obj_id),
							"sub_type" => array("text", $sub_type),
							"sub_id" => array("integer", $sub_id),
							"field_id" => array("integer", $this->getFieldId())
						);
						ilADTActiveRecordByType::writeByPrimary("adv_md_values", $primary, "Enum", $new_option);
					}
				}
			}			
		}		
	}
	
	
	//
	// export/import
	// 
	
	protected function addPropertiesToXML(ilXmlWriter $a_writer)
	{
		foreach($this->getOptions() as $value)
		{			
			$a_writer->xmlElement('FieldValue',null,$value);			
		}
	}	
	
	public function importXMLProperty($a_key, $a_value)
	{
		$this->options[] = $a_value;
	}	
	
	
	// 
	// import/export
	//
	
	public function getValueForXML(ilADT $element)
	{
		return $element->getSelection();
	}
	
	public function importValueFromXML($a_cdata)
	{		
		$this->getADT()->setSelection($a_cdata);			
	}	
}

?>