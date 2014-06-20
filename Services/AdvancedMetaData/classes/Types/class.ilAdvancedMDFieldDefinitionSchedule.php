<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/** 
 * AMD select for schedules at a course.
 * 
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */

class ilAdvancedMDFieldDefinitionSchedule extends ilAdvancedMDFieldDefinition
{		
	//
	// generic types
	// 
	
	public function getType()
	{
		return self::TYPE_SCHEDULE;
	}
	
	
	//
	// ADT
	//
	
	protected function initADTDefinition()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Schedule");
		
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
		return false;
	}	
}

?>