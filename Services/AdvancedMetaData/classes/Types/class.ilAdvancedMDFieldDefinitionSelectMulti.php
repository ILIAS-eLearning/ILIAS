<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionSelect.php";

/** 
 * AMD field type select
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelectMulti extends ilAdvancedMDFieldDefinitionSelect
{				
	const XML_SEPARATOR = "~|~";
	
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_SELECT_MULTI;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("MultiEnum");
		$def->setNumeric(false);
		
		$options = $this->getOptions();
		$def->setOptions(array_combine($options, $options));
		
		// see ilAdvancedMDValues::getActiveRecord()
		// using ilADTMultiEnumDBBridge::setFakeSingle()
		
		return $def;
	}	
	
	
	//
	// definition (NOT ADT-based)
	// 
	
	public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form)
	{	
		$old = $this->getOptions();
		$new = $a_form->getInput("opts");
		
		$missing = array_diff($old, $new);
		if(sizeof($missing))
		{		
			$this->confirmed_objects = $this->buildConfirmedObjects($a_form);
			if(!is_array($this->confirmed_objects))
			{									
				$search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($this->getADTDefinition(), false, false);							
				ilADTFactory::initActiveRecordByType();
				
				foreach($missing as $missing_value)
				{					
					$in_use = $this->findBySingleValue($search, $missing_value);				
					if(sizeof($in_use))
					{
						foreach($in_use as $item)
						{
							$this->confirm_objects[$missing_value][] = $item;
						}
					}					
				}				
			}
		}
		
		$this->old_options = $old;
		$this->setOptions($new);	
	}
	
	protected function findBySingleValue(ilADTEnumSearchBridgeMulti $a_search, $a_value)
	{
		$res = array();
		
		$a_search->getADT()->setSelections(array($a_value));	
		$condition = $a_search->getSQLCondition(ilADTActiveRecordByType::SINGLE_COLUMN_NAME);
					
		$in_use = ilADTActiveRecordByType::find(
			"adv_md_values", 
			"Enum", 
			$this->getFieldId(),  
			$condition
		);	
		if($in_use)
		{
			foreach($in_use as $item)
			{
				$res[] = array($item["obj_id"], $item["sub_type"], $item["sub_id"], $item["value"]);
			}
		}	
		
		return $res;
	}
	
	
	//
	// definition CRUD 
	//
	
	public function update()
	{		
		if(sizeof($this->confirmed_objects))
		{
			// we need the "old" options for the search
			$def = $this->getADTDefinition();
			$def = clone($def);
			$def->setOptions(array_combine($this->old_options, $this->old_options));			
			$search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def, false, false);			
			ilADTFactory::initActiveRecordByType();
			
			foreach($this->confirmed_objects as $old_option => $item_ids)
			{				
				// get complete old values
				$old_values = array();
				foreach($this->findBySingleValue($search, $old_option) as $item)
				{
					$old_values[$item[0]."_".$item[1]."_".$item[2]] = $item[3];
				}
				
				foreach($item_ids as $item => $new_option)
				{
					$parts = explode("_", $item);
					$obj_id = $parts[0];
					$sub_type = $parts[1];
					$sub_id = $parts[2];
					
					if(!$new_option)
					{
						// remove existing value					
						$primary = array(
							"obj_id" => array("integer", $obj_id),
							"sub_type" => array("text", $sub_type),
							"sub_id" => array("integer", $sub_id),
							"field_id" => array("integer", $this->getFieldId())
						);
						ilADTActiveRecordByType::deleteByPrimary("adv_md_values", $primary, "MultiEnum");
					}
					else
					{		
						// update existing value (with changed option)						
						if(isset($old_values[$item]))
						{
							// find changed option in old value
							$old_value = explode(ilADTMultiEnumDBBridge::SEPARATOR, $old_values[$item]);
							$old_idx = array_keys($old_value, $old_option);
							if(sizeof($old_idx))
							{
								// switch option
								$old_idx = array_pop($old_idx);						
								$old_value[$old_idx] = $new_option;			
								$new_value = array_unique($old_value);
								$new_value = implode(ilADTMultiEnumDBBridge::SEPARATOR, $new_value);
								
								$primary = array(
									"obj_id" => array("integer", $obj_id),
									"sub_type" => array("text", $sub_type),
									"sub_id" => array("integer", $sub_id),
									"field_id" => array("integer", $this->getFieldId())
								);
								ilADTActiveRecordByType::writeByPrimary("adv_md_values", $primary, "MultiEnum", $new_value);
							}
						}			
					}
				}
			}			
			
			$this->confirmed_objects = array();
		}	
				
		parent::update();		
	}
	
	
	// 
	// import/export
	//
	
	public function getValueForXML(ilADT $element)
	{					
		return self::XML_SEPARATOR.
			implode(self::XML_SEPARATOR, $element->getSelections()).
			self::XML_SEPARATOR;
	}
	
	public function importValueFromXML($a_cdata)
	{			
		$this->getADT()->setSelections(explode(self::XML_SEPARATOR, $a_cdata));			
	}	
}