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
class ilAdvancedMDFieldDefinitionLongText extends ilAdvancedMDFieldDefinition
{		
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_LONG_TEXT;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("LongText");
		
		// :TODO: ?
		// $def->setMaxLength(4000);
		
		return $def;
	}			
	
	
	// 
	// import/export
	//
	
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
}

?>