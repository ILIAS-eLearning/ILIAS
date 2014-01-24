<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Xml/classes/class.ilSaxParser.php");
include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * Export file parser
 *
 * @author Aleex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilExportFileParser extends ilSaxParser
{
	protected $item_xml = "";
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_file, $a_callback_obj, $a_callback_func)
	{
		$this->callback_obj = $a_callback_obj;
		$this->callback_func = $a_callback_func;

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
		if ($this->in_export_item)
		{
			$this->export_item_writer->xmlStartTag($a_name, $a_attribs);
		}

		switch ($a_name)
		{
			case "exp:Export":
				$this->entity = $a_attribs["Entity"];
				$this->install_id = $a_attribs["InstallationId"];
				$this->install_url = $a_attribs["InstallationUrl"];
				$this->schema_version = $a_attribs["SchemaVersion"];
				break;

			case "exp:ExportItem":
				$this->in_export_item = true;
				$this->current_id = $a_attribs["Id"];

				$this->export_item_writer = new ilXmlWriter();

				$this->item_xml = "";
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
		switch ($a_name)
		{
			case "exp:ExportItem":
				$this->in_export_item = false;
				$cf = $this->callback_func;
				$this->callback_obj->$cf($this->entity, $this->schema_version, $this->current_id,
					$this->export_item_writer->xmlDumpMem(false), $this->install_id,
					$this->install_url);
				break;

		}

		if ($this->in_export_item)
		{
			$this->export_item_writer->xmlEndTag($a_name);
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

		if ($this->in_export_item)
		{
			$this->export_item_writer->xmlData($a_data);	
		}
	}
	
}
?>