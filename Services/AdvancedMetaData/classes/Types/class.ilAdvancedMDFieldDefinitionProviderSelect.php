<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionSelect.php";

/** 
 * AMD select for Venues at a seminar. Gets venues from the org-managment in a 
 * manner customized for the generali.
 * 
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionProviderSelect extends ilAdvancedMDFieldDefinitionSelect
{		
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_PROVIDER_SELECT;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
		$def->setNumeric(false);
		
		$options = $this->getOptions();
		$def->setOptions($options);
		
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
		throw new Exception("ilAdvanceMDFieldDefinitionVenue::setOptions(): not supported, options will be retreived from org-tree.");
		/*
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
		*/
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		return gevOrgUnitUtils::getProviderNames();
	}
	
	
	//
	// definition (NOT ADT-based)
	// 
	
	protected function importFieldDefinition(array $a_def)
	{
		//$this->setOptions($a_def);
	}
	
	protected function getFieldDefinition()
	{
		return $this->options;
	}
	
	public function getFieldDefinitionForTableGUI()
	{
		global $lng;
		
		return array($lng->txt("options") => implode(",", array_values($this->getOptions())));		
	}
	
	/**
	 * Add input elements to definition form
	 *
	 * @param ilPropertyFormGUI $a_form
	 * @param bool $a_disabled
	 */
	public function addCustomFieldToDefinitionForm(ilPropertyFormGUI $a_form, $a_disabled = false)
	{
		/*global $lng;
		
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
		}*/
	}
	
	/**
	 * Import custom post values from definition form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form)
	{		
/*		$old = $this->getOptions();
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
						$this->confirm_objects[$item[ilADTActiveRecordByType::SINGLE_COLUMN_NAME]][] = $item["obj_id"];
					}
				}
			}
		}
		
		$this->setOptions($new);*/
	}	
	
	public function importDefinitionFormPostValuesNeedsConfirmation()
	{
		return sizeof($this->confirm_objects);
	}
	
	public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form)
	{
		global $lng;
		
		$a_form->getItemByPostVar("opts")->setDisabled(true);
		
		if(sizeof($this->confirm_objects))
		{
			$sec = new ilFormSectionHeaderGUI();
			$sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
			$a_form->addItem($sec);
			
			foreach($this->confirm_objects as $option => $obj_ids)
			{
				$opt = new ilNonEditableValueGUI($lng->txt("md_adv_confirm_definition_select_option").': "'.$option.'"');
				$a_form->addItem($opt);
				
				foreach($obj_ids as $obj_id)
				{
					$type = ilObject::_lookupType($obj_id);
					$type_title = $lng->txt("obj_".$type);
					$title = ilObject::_lookupTitle($obj_id);
					
					$sel = new ilSelectInputGUI($type_title.' "'.$title.'"', "conf[".$this->getFieldId()."][".$option."][".$obj_id."]");					
					$options = array(""=>$lng->txt("md_adv_confirm_definition_select_option_remove"));
					foreach($this->getOptions() as $option)
					{
						$options[$option] = $lng->txt("md_adv_confirm_definition_select_option_overwrite").': "'.$option.'"';
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
			foreach($this->confirmed_objects as $old_option => $obj_ids)
			{
				foreach($obj_ids as $obj_id => $new_option)
				{
					if(!$new_option)
					{
						// remove existing value
						$primary = array(
							"obj_id" => array("integer", $obj_id),
							"field_id" => array("integer", $this->getFieldId())
						);
						ilADTActiveRecordByType::deleteByPrimary("adv_md_values", $primary, "Enum");
					}
					else
					{
						// update existing value
						$primary = array(
							"obj_id" => array("integer", $obj_id),
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
		foreach($this->getOptions() as $key => $value)
		{			
			$a_writer->xmlElement('FieldValue',$key,$value);			
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