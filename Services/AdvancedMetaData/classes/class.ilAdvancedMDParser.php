<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Xml/classes/class.ilSaxParser.php");
include_once("Services/Utilities/classes/class.ilSaxController.php");
include_once("Services/Utilities/interfaces/interface.ilSaxSubsetParser.php");
include_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
include_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");

/**
 * Adv MD XML Parser
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilCourseXMLParser.php 53320 2014-09-12 11:33:49Z fwolf $
 *
 * @extends ilMDSaxParser
 */
class ilAdvancedMDParser extends ilSaxParser implements ilSaxSubsetParser
{	
	protected $obj_id; // [int]
	protected $rec_id; // [int]
	protected $mapping; // [object]	
	protected $cdata; // [string]	
	protected $value_records = array(); // [array]
	protected $current_record; // [ilAdvancedMDValues]
	protected $current_value; // [ilAdvancedMDFieldDefinition]
	protected $has_values; // [bool]
	protected $record_ids = array(); // [array]
	
	function __construct($a_obj_id, $a_mapping)
	{		
		parent::__construct();
		
		$parts = explode(":", $a_obj_id);
		$this->obj_id = $parts[0];		
		$this->mapping = $a_mapping;
	}
	
	function setHandlers($a_xml_parser)
	{
		$this->sax_controller = new ilSaxController();
		$this->sax_controller->setHandlers($a_xml_parser);
		$this->sax_controller->setDefaultElementHandler($this);		
	}
	
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch($a_name)
		{
			case 'AdvancedMetaData':	
				
				
				break;
				
			case 'Value':
				$this->initValue($a_attribs['id'], $a_attribs['sub_type'], $a_attribs['sub_id']);
				break;
		}
	}
	
	function handlerEndTag($a_xml_parser,$a_name)
	{
		switch($a_name)
		{
			case 'AdvancedMetaData':
				// we need to write all records that have been created (1 for each sub-item)
				foreach($this->value_records as $record)
				{
					$record->write();
				}
				break;
				
			case 'Value':
				$value = trim($this->cdata);
				if(is_object($this->current_value) && $value != "")
				{
					$this->current_value->importValueFromXML($value);					
				}
				break;
		}
		$this->cdata = '';
	}
	
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		if($a_data != "\n")
		{
			// Replace multiple tabs with one space
			$a_data = preg_replace("/\t+/"," ",$a_data);

			$this->cdata .= $a_data;
		}
	}
	
	protected function initValue($a_import_id, $a_sub_type = "", $a_sub_id = 0)
	{
		$this->current_value = null;
		
	 	if($field = ilAdvancedMDFieldDefinition::getInstanceByImportId($a_import_id))
	 	{		
			$rec_id = $field->getRecordId();
						
			$new_parent_id = $this->mapping->getMapping("Services/AdvancedMetaData", "parent", $this->obj_id);
			if(!$new_parent_id)
			{
				return;
			}
			
			if($a_sub_type)
			{								
				$new_sub_id = $this->mapping->getMapping("Services/AdvancedMetaData", "advmd_sub_item", "advmd:".$a_sub_type.":".$a_sub_id);						
				if(!$new_sub_id)
				{
					return;
				}
							
				$rec_idx = $rec_id.";".$a_sub_type.";".$new_sub_id;
				if(!array_key_exists($rec_idx, $this->value_records))	
				{
					$this->value_records[$rec_idx] = new ilAdvancedMDValues($rec_id, $new_parent_id, $a_sub_type, $new_sub_id);
				}				
			}
			else
			{			
				$rec_idx = $rec_id.";;";
				if(!array_key_exists($rec_idx, $this->value_records))	
				{
					$this->value_records[$rec_idx] = new ilAdvancedMDValues($rec_id, $new_parent_id);
				}				
			}						
			
			// init ADTGroup before definitions to bind definitions to group
			$this->value_records[$rec_idx]->getADTGroup();

			foreach($this->value_records[$rec_idx]->getDefinitions() as $def)
			{										
				if($a_import_id == $def->getImportId())
				{
					$this->current_value = $def;
					break;
				}
			}
			
			// valid field found, record will be imported
			if($this->current_value)
			{								
				$this->record_ids[$new_parent_id][$a_sub_type][] = $rec_id;
			}
	 	} 	
	}
	
	public function getRecordIds()
	{
		return $this->record_ids;
	}
}