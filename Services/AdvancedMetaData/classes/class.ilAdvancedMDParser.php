<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Xml/classes/class.ilSaxParser.php");
include_once("Services/Utilities/classes/class.ilSaxController.php");
include_once("Services/Utilities/interfaces/interface.ilSaxSubsetParser.php");
include_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php");

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
	protected $current_value; // [ilAdvancedMDValue]
	protected $cdata; // [string]
	protected $field_ids; // [array]
	
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
		
		/* the value parser does not support sub-types and object-specific record selection
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValueParser.php');
		$this->sax_controller->setElementHandler(
			$this->adv_md_handler = new ilAdvancedMDValueParser($this->obj_id),
			'AdvancedMetaData');		
		*/
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
				break;
				
			case 'Value':
				$value = trim($this->cdata);
				if(is_object($this->current_value) && $value != "")
				{
					$this->current_value->setValue($value);
					$this->current_value->save();
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
		
	 	if($field_id = ilAdvancedMDFieldDefinition::_lookupFieldId($a_import_id))
	 	{
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
				$md_value = new ilAdvancedMDValue($field_id, $new_parent_id, $a_sub_type, $new_sub_id);
			}
			else
			{
				$md_value = new ilAdvancedMDValue($field_id, $new_parent_id);
			}			
			$this->current_value = $md_value;
			
			$this->field_ids[$new_parent_id][$a_sub_type][] = $field_id;
	 	} 	
	}
	
	public function getFieldIds()
	{
		return $this->field_ids;
	}
}