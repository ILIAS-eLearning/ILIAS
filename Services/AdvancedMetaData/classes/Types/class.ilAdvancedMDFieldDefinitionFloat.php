<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionInteger.php";

/** 
 * AMD field type float (based on integer)
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionFloat extends ilAdvancedMDFieldDefinitionInteger
{		
	protected $decimals; // [integer]
	
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_FLOAT;
	}
	
	protected function init()
	{
		parent::init();
		$this->setDecimals(2);
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{				
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Float");

		$def->setMin($this->getMin());
		$def->setMax($this->getMax());
		$def->setDecimals($this->getDecimals());
	
		return $def;
	}	
	
		
	// 
	// properties
	// 
	
	/**
	 * Set decimals
	 *
	 * @param int $a_value
	 */
	public function setDecimals($a_value)
	{
		$this->decimals = max(1, abs((int)$a_value));
	}

	/**
	 * Get decimals
	 *
	 * @return int
	 */
	public function getDecimals()
	{
	 	return $this->decimals;
	}	
	
	
	//
	// definition (NOT ADT-based)
	// 
	
	protected function importFieldDefinition(array $a_def)
	{
		parent::importFieldDefinition($a_def);
		$this->setDecimals($a_def["decimals"]);
	}
	
	protected function getFieldDefinition()
	{
		$def = parent::getFieldDefinition();
		$def["decimals"] = $this->getDecimals();
		return $def;
	}
	
	public function getFieldDefinitionForTableGUI()
	{
		global $lng;
	
		$res = parent::getFieldDefinitionForTableGUI();
		$res[$lng->txt("md_adv_number_decimals")] = $this->getDecimals();
		return $res;
	}
	
	/**
	 * Add input elements to definition form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addToFieldDefinitionForm(ilPropertyFormGUI $a_form)
	{
		global $lng;
		
		parent::addToFieldDefinitionForm($a_form);	
		
		$decimals = new ilNumberInputGUI($lng->txt("md_adv_number_decimals"), "dec");	
		$decimals->setRequired(true);
		$decimals->setValue($this->getDecimals());
		$decimals->setSize(5);
		$a_form->addItem($decimals);
	}
	
	/**
	 * Import post values from definition form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function importDefinitionFormPostValues(ilPropertyFormGUI $a_form)
	{
		parent::importDefinitionFormPostValues($a_form);
					
		$this->setDecimals($a_form->getInput("dec"));		
	}	
	
	
	//
	// export/import
	// 
	
	protected function addPropertiesToXML(ilXmlWriter $a_writer)
	{				
		parent::addPropertiesToXML($a_writer);
		
		$a_writer->xmlElement('FieldValue',array("id"=>"decimals"),$this->getDecimals());					
	}	
	
	public function importXMLProperty($a_key, $a_value)
	{
		if($a_key == "decimals")
		{
			$this->setDecimals($a_value != "" ? $a_value : null);
		}
		
		parent::importXMLProperty($a_key, $a_value);
	}			
}

?>