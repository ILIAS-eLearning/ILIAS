<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionGroupBased.php";

/** 
 * AMD field type address
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionAddress extends ilAdvancedMDFieldDefinitionGroupBased
{				
	public function getType()
	{
		return self::TYPE_ADDRESS;
	}
	
	public function getADTGroup()
	{		
		$def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Group");
		
		$street = ilADTFactory::getInstance()->getDefinitionInstanceByType("Text");
		$def->AddElement("street", $street);
		
		$city = ilADTFactory::getInstance()->getDefinitionInstanceByType("Text");
		$def->AddElement("city", $city);
		
		$loc = ilADTFactory::getInstance()->getDefinitionInstanceByType("Location");
		$def->AddElement("location", $loc);
					
		return $def;
	}
	
	public function getTitles()
	{
		global $lng;
		
		return array(
			"street" => $lng->txt("street")			
			,"city" => $lng->txt("city")
			,"location" => $lng->txt("location")
		);
	}
}

?>