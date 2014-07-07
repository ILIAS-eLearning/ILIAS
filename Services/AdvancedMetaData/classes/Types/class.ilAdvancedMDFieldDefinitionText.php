<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/** 
 * AMD field type text
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionText extends ilAdvancedMDFieldDefinition
{		
	protected $max_length; // [int]
	protected $multi; // [bool]
	
	
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_TEXT;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Text");
				
		$max = $this->getMaxLength();
		if(is_numeric($max))
		{
			$def->setMaxLength($max);
		}
		
		// multi-line is presentation property
		
		return $def;
	}			
	
	
	//
	// properties
	//
	
	/**
	 * Set max length
	 *
	 * @param int $a_value
	 */
	public function setMaxLength($a_value)
	{
		if($a_value !== null)
		{
			$a_value = (int)$a_value;
		}
	 	$this->max_length = $a_value;
	}

	/**
	 * Get max length
	 *
	 * @return int
	 */
	public function getMaxLength()
	{
	 	return $this->max_length;
	}
	
	/**
	 * Set multi-line
	 *
	 * @param string $a_value
	 */
	public function setMulti($a_value)
	{		
	 	$this->multi = (bool)$a_value;
	}

	/**
	 * Is multi-line?
	 *
	 * @return bool
	 */
	public function isMulti()
	{
	 	return $this->multi;
	}
	
	
	//
	// definition (NOT ADT-based)
	// 
	
	protected function importFieldDefinition(array $a_def)
	{		
		$this->setMaxLength($a_def["max"]);		
		$this->setMulti($a_def["multi"]);
	}
	
	protected function getFieldDefinition()
	{
		return array(			
			"max" => $this->getMaxLength(),
			"multi" => $this->isMulti()
		);
	}
	
	public function getFieldDefinitionForTableGUI()
	{
		global $lng;
	
		$res = array();
		
		if($this->getMaxLength() !== null)
		{
			$res[$lng->txt("md_adv_text_max_length")] = $this->getMaxLength();
		}
		if($this->isMulti())
		{
			$res[$lng->txt("md_adv_text_multi")] = $lng->txt("yes");
		}
		
		return $res;
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
		
		$max = new ilNumberInputGUI($lng->txt("md_adv_text_max_length"), "max");
		$max->setValue($this->getMaxLength());
		$max->setSize(10);		
		$max->setMinValue(1);
		$max->setMaxValue(4000); // DB limit
		$a_form->addItem($max);
		
		$multi = new ilCheckboxInputGUI($lng->txt("md_adv_text_multi"), "multi");
		$multi->setValue(1);
		$multi->setChecked($this->isMulti());
		$a_form->addItem($multi);		
				
		if($a_disabled)
		{			
			$max->setDisabled(true);
			$multi->setDisabled(true);
		}						
	}
	
	/**
	 * Import custom post values from definition form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form)
	{						
		$max = $a_form->getInput("max");
		$this->setMaxLength(($max !== "") ? $max : null);	
		
		$this->setMulti($a_form->getInput("multi"));
	}	
	
	// 
	// import/export
	//
	
	protected function addPropertiesToXML(ilXmlWriter $a_writer)
	{								
		$a_writer->xmlElement('FieldValue',array("id"=>"max"),$this->getMaxLength());					
		$a_writer->xmlElement('FieldValue',array("id"=>"multi"),$this->isMulti());					
	}	
	
	public function importXMLProperty($a_key, $a_value)
	{		
		if($a_key == "max")
		{
			$this->setMaxLength($a_value != "" ? $a_value : null);
		}
		if($a_key == "multi")
		{
			$this->setMulti($a_value != "" ? $a_value : null);
		}
	}		
	
	public function getValueForXML(ilADT $element)
	{
		return $element->getText();
	}
	
	public function importValueFromXML($a_cdata)
	{
		$this->getADT()->setText($a_cdata);
	}
	
	public function importFromECS($a_ecs_type, $a_value, $a_sub_id)
	{
		switch($a_ecs_type)
		{
			case ilECSUtils::TYPE_ARRAY:
				$value = implode(',', (array)$a_value);
				break;

			case ilECSUtils::TYPE_INT:
				$value = (int)$a_value;
				break;

			case ilECSUtils::TYPE_STRING:
				$value = (string)$a_value;
				break;

			case ilECSUtils::TYPE_TIMEPLACE:	
				if($a_value instanceof ilECSTimePlace)				
				{
					$value = $a_value->{'get'.ucfirst($a_sub_id)}();										
				}
				break;
		}
		
		if(trim($value))
		{
			$this->getADT()->setText($value);
			return true;
		}		
		return false;
	}	
	
	//
	// presentation
	//
	
	public function prepareElementForEditor(ilADTTextFormBridge $a_text)
	{
		// seems to be default in course info editor
		$a_text->setMulti($this->isMulti(), 80, 6);		
	}
}

?>