<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/** 
 * AMD field type date
 * 
 * Stefan Meyer <smeyer.ilias@gmx.de>
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionInternalLink extends ilAdvancedMDFieldDefinition
{
	/**
	 * Get type
	 * @return int
	 */
	public function getType()
	{
		return self::TYPE_INTERNAL_LINK;
	}
	
	
	/**
	 * Init ADT definition
	 * @return ilADTDefinition
	 */
	protected function initADTDefinition()
	{		
		return ilADTFactory::getInstance()->getDefinitionInstanceByType("InternalLink");
	}

	/**
	 * Get value for XML
	 * @param \ilADT $element
	 */
	public function getValueForXML(\ilADT $element)
	{
		$type = ilObject::_lookupType($element->getTargetRefId(), true);

		if($element->getTargetRefId() && strlen($type))
		{
			return 'il_'.IL_INST_ID.'_'.$type.'_'.$element->getTargetRefId();
		}
		return '';
	}

	/**
	 * Import value from xml
	 * @param string $a_cdata
	 */
	public function importValueFromXML($a_cdata)
	{
		$parsed_import_id = ilUtil::parseImportId($a_cdata);
		
		if(
			(strcmp($parsed_import_id['inst_id'], IL_INST_ID) == 0) &&
			ilObject::_exists($parsed_import_id['id'], true, $parsed_import_id['type'])
		)
		{
			$this->getADT()->setTargetRefId($parsed_import_id['id']);
		}
	}

}

?>