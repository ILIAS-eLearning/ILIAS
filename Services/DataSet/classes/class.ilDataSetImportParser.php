<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./classes/class.ilSaxParser.php");

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
	function __construct($a_import_obj, $a_file, $a_ds_component)
	{
		$this->import = $a_import_obj;
		$this->ds_component = $a_ds_component;
		parent::ilSaxParser($a_file, true);
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
			case "dataset":
				$this->import->setInstallId($a_attribs["install_id"]);
				$this->import->setInstallUrl($a_attribs["install_url"]);
				$this->import->initDataset($this->ds_component, $a_attribs["top_entity"]);
				break;
				
			case "types":
				$this->current_entity = $a_attribs["entity"];
				$this->current_version = $a_attribs["version"];
				break;
				
			case "ftype":
				$this->current_ftypes[$a_attribs["name"]] =
					$a_attribs["type"];
				break;
				
			case "rec":
				if (!$this->entities_sent)
				{
					$this->import->setEntityTypes($this->entities);
					$this->import->afterEntityTypes();
				}
				$this->current_entity = $a_attribs["entity"];
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
			case "types":
				$this->entities[$this->current_entity] =
					array(
						"version" => $this->current_version,
						"types" => $this->current_ftypes 
						);
				$this->current_ftypes = array();
				$this->current_entity = "";
				$this->current_version = "";
				break;
				
			case "rec":
				$this->import->importRecord($this->current_entity,
					$this->entities[$this->current_entity]["types"],
					$this->current_field_values);
				$this->in_record = false;
				$this->current_entity = "";
				$this->current_field_values = array();
				break;
				
			default:
				if ($this->in_record)
				{
					$this->current_field_values[$this->current_field] = 
						$this->chr_data;
				}
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