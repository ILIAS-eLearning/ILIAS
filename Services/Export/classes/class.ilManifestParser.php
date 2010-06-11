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
class ilManifestParser extends ilSaxParser
{
	protected $expfiles = array();
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_file)
	{
		parent::ilSaxParser($a_file, true);
		$this->startParsing();
	}
	
	/**
	 * Get xml files
	 *
	 * @return	array of strings	xml file pathes
	 */
	function getExportFiles()
	{
		return $this->expfiles;
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
			case "ExportFile":
				$this->expfiles[] = array("component" => $a_attribs["Component"],
					"path" => $a_attribs["Path"]);
				break;
		}
	}
	
	/**
	 * End Tag
	 */
	function handleEndTag($a_xml_parser, $a_name)
	{
	
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