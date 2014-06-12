<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/** 
 * AMD field type date
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionDate extends ilAdvancedMDFieldDefinition
{		
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_DATE;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		return ilADTFactory::getInstance()->getDefinitionInstanceByType("Date");
	}		
	
	
	// 
	// import/export
	//
	
	public function getValueForXML(ilADT $element)
	{		
		return $element->getDate()->get(IL_CAL_DATE);
	}
	
	public function importValueFromXML($a_cdata)
	{
		$this->getADT()->setDate(new ilDate($a_cdata, IL_CAL_DATE));
	}
	
	public function importFromECS($a_ecs_type, $a_value, $a_sub_id)
	{
		switch($a_ecs_type)
		{			
			case ilECSUtils::TYPE_TIMEPLACE:	
				if($a_value instanceof ilECSTimePlace)				
				{
					$value = new ilDate($a_value->{'getUT'.ucfirst($a_sub_id)}(), IL_CAL_UNIX);								
				}
				break;
		}
		
		if($value instanceof ilDate)
		{
			$this->getADT()->setDate($value);
			return true;
		}		
		return false;
	}	
}

?>