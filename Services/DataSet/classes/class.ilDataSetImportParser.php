<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
 * Manifest parser for ILIAS standard export files
 *
 * @author Aleex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilDataSetImportParser extends ilSaxParser
{
	protected $import = null;				// import object
	protected $entities = array();			// types array
	protected $current_entity = "";			// current entity
	protected $current_version = "";		// current version
	protected $current_ftypes = array();	// current field types
	protected $entities_sent = false;		// sent entities to import class?
	protected $in_record = false;			// are we currently in a rec tag?
	protected $current_field = "";			// current field
	protected $current_field_values = array();	// current field values
	
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_top_entity, $a_schema_version, $a_xml, $a_ds, $a_mapping)
	{
		$this->ds = $a_ds;
		$this->mapping = $a_mapping;
		$this->top_entity = $a_top_entity;
		$this->schema_version = $a_schema_version;
		$this->dspref = ($this->ds->getDSPrefix() != "")
			? $this->ds->getDSPrefix().":"
			: "";
		
		parent::ilSaxParser();
		$this->setXMLContent($a_xml);
		$this->startParsing();


	}
		
	/**
	 * Set event handlers
	 *
	 * @param	resource	reference to the xml parser
	 * @access	private
	 */
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
		xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
	}

	
	/**
	 * Start parser
	 */
	function startParsing()
	{
		parent::startParsing();
	}
	
	/**
	 * Begin Tag
	 */
	function handleBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		switch ($a_name)
		{
			case $this->dspref."Dataset":
//				$this->import->initDataset($this->ds_component, $a_attribs["top_entity"]);
				break;
				
			case $this->dspref."Types":
				$this->current_entity = $a_attribs["Entity"];
				$this->current_version = $a_attribs["Version"];
				break;
				
			case $this->dspref."FieldType":
				$this->current_ftypes[$a_attribs["Name"]] =
					$a_attribs["Type"];
				break;
				
			case $this->dspref."Rec":
				$this->current_entity = $a_attribs["Entity"];
				$this->in_record = true;
				$this->current_field_values = array();
				break;
				
			default:
				if ($this->in_record)
				{
					$field = explode(":", $a_name);		// remove namespace
					$field = $field[count($field) - 1];
					$this->current_field = $field;
				}
		}
	}
	
	/**
	 * End Tag
	 */
	function handleEndTag($a_xml_parser, $a_name)
	{
		switch ($a_name)
		{
			case $this->dspref."Types":
				$this->entities[$this->current_entity] =
					array(
						"version" => $this->current_version,
						"types" => $this->current_ftypes 
						);
				$this->current_ftypes = array();
				$this->current_entity = "";
				$this->current_version = "";
				break;
				
			case $this->dspref."Rec":
				$this->ds->importRecord($this->current_entity,
					$this->entities[$this->current_entity]["types"],
					$this->current_field_values,
					$this->mapping,
					$this->schema_version);
				$this->in_record = false;
				$this->current_entity = "";
				$this->current_field_values = array();
				break;
				
			default:
				if ($this->in_record && $this->current_field != "")
				{
					$this->current_field_values[$this->current_field] = 
						$this->chr_data;
				}
				$this->current_field = "";
				break;
		}
		
		$this->chr_data = "";
	}
	
	/**
	 * End Tag
	 */
	function handleCharacterData($a_xml_parser,$a_data)
	{
		//$a_data = str_replace("<","&lt;",$a_data);
		//$a_data = str_replace(">","&gt;",$a_data);
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		//$a_data = preg_replace("/\n/","",$a_data);
		//$a_data = preg_replace("/\t+/","",$a_data);

		$this->chr_data .= $a_data;
	}
	
}
?>