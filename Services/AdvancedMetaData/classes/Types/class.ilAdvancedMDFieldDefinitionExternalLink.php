<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/** 
 * AMD field type date
 * 
 * Stefan Meyer <smeyer.ilias@gmx.de>
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionExternalLink extends ilAdvancedMDFieldDefinition
{
	/**
	 * Get type
	 * @return int
	 */
	public function getType()
	{
		return self::TYPE_EXTERNAL_LINK;
	}
	
	
	/**
	 * Init ADT definition
	 * @return ilADTDefinition
	 */
	protected function initADTDefinition()
	{		
		return ilADTFactory::getInstance()->getDefinitionInstanceByType("ExternalLink");
	}

	/**
	 * Get value for XML
	 * @param \ilADT $element
	 */
	public function getValueForXML(\ilADT $element)
	{
		return $element->getUrl();
	}

	/**
	 * Import value from xml
	 * @param string $a_cdata
	 */
	public function importValueFromXML($a_cdata)
	{
		$this->getADT()->setUrl($a_cdata);
	}

}

?>