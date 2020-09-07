<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
* Class ilPluginReader
*
* Reads plugin information of plugin.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilPluginReader extends ilSaxParser
{
    public function __construct($a_path, $a_ctype, $a_cname, $a_slot_id, $a_pname)
    {
        parent::__construct($a_path);

        $this->ctype = $a_ctype;
        $this->cname = $a_cname;
        $this->slot_id = $a_slot_id;
        $this->pname = $a_pname;
    }

    /**
     * Delete the event listeneing information
     */
    public function clearEvents()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $component = "Plugins/" . $this->pname;
        $ilDB->manipulate("DELETE FROM il_event_handling WHERE component = " . $ilDB->quote($component, 'text'));
    }

    
    public function startParsing()
    {
        if ($this->getInputType() == 'file') {
            if (!file_exists($this->xml_file)) {
                // not every plugin has a plugin.xml yet
                return;
            }
        }
        parent::startParsing();
    }
    
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @param	array		element attributes
    * @access	private
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        global $DIC;
        $ilDB = $DIC->database();

        switch ($a_name) {
            // base plugin info is still read from the plugin.php
            case 'plugin_tag_analyzed_in_future':
                
                // check whether record exists
                $q = "SELECT * FROM il_plugin WHERE " .
                    " component_type = " . $ilDB->quote($this->ctype, "text") .
                    " AND component_name = " . $ilDB->quote($this->cname, "text") .
                    " AND slot_id = " . $ilDB->quote($this->slot_id, "text") .
                    " AND name = " . $ilDB->quote($this->pname, "text");
                $set = $ilDB->query($q);
                if ($ilDB->numRows($set) == 0) {
                    $q = "INSERT INTO il_plugin (component_type,component_name,slot_id," .
                        "name, id, last_update_version, current_version, ilias_min_version," .
                        " ilias_max_version, active) VALUES " .
                        "(" . $ilDB->quote($this->ctype, "text") . "," .
                        $ilDB->quote($this->cname, "text") . "," .
                        $ilDB->quote($this->slot_id, "text") . "," .
                        $ilDB->quote($this->pname, "text") . "," .
                        $ilDB->quote($a_attribs["id"], "text") . "," .
                        $ilDB->quote("0.0.0", "text") . "," .
                        $ilDB->quote($a_attribs["version"], "text") . "," .
                        $ilDB->quote($a_attribs["ilias_min_version"], "text") . "," .
                        $ilDB->quote($a_attribs["ilias_max_version"], "text") . "," .
                        $ilDB->quote(0, "integer") . ")";
                    $ilDB->manipulate($q);
                } else {
                    $q = "UPDATE il_plugin SET " .
                        " id = " . $ilDB->quote($a_attribs["id"], "text") . "," .
                        " current_version = " . $ilDB->quote($a_attribs["version"], "text") . "," .
                        " ilias_min_version = " . $ilDB->quote($a_attribs["ilias_min_version"], "text") . "," .
                        " ilias_max_version = " . $ilDB->quote($a_attribs["ilias_max_version"], "text") .
                        " WHERE " .
                        " component_type = " . $ilDB->quote($this->ctype, "text") .
                        " AND component_name = " . $ilDB->quote($this->cname, "text") .
                        " AND slot_id = " . $ilDB->quote($this->slot_id, "text") .
                        " AND name = " . $ilDB->quote($this->pname, "text");
                    $ilDB->manipulate($q);
                }
                break;

            case "event":
                $component = "Plugins/" . $this->pname;
                $q = "INSERT INTO il_event_handling (component, type, id) VALUES (" .
                    $ilDB->quote($component, "text") . "," .
                    $ilDB->quote($a_attribs["type"], "text") . "," .
                    $ilDB->quote($a_attribs["id"], "text") . ")";
                $ilDB->manipulate($q);
                break;
        }
    }
            
    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @access	private
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
    }

            
    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		data
    * @access	private
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);

        if (!empty($a_data)) {
            switch ($this->current_tag) {
                case '':
            }
        }
    }
}
