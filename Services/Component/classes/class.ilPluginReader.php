<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
* Class ilPluginReader
*
* This is only used in ilPlugin.
*
* @deprecate as of ILIAS 9
*
* Reads plugin information of plugin.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilPluginReader extends ilSaxParser
{
    protected $ctype;
    protected $cname;
    protected $slot_id;
    protected $pname;

    public function __construct(?string $a_path, $a_ctype, $a_cname, $a_slot_id, $a_pname)
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
    public function clearEvents(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $component = "Plugins/" . $this->pname;
        $ilDB->manipulate("DELETE FROM il_event_handling WHERE component = " . $ilDB->quote($component, 'text'));
    }


    public function startParsing(): void
    {
        if ($this->getInputType() === 'file' && !file_exists($this->xml_file)) {
            // not every plugin has a plugin.xml yet
            return;
        }
        parent::startParsing();
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start tag handler
    *
    * @param	mixed	    internal xml_parser_handler
    * @param	string		element tag name
    * @param	array		element attributes
    * @access	private
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($a_name === "event") {
            $component = "Plugins/" . $this->pname;
            $q = "INSERT INTO il_event_handling (component, type, id) VALUES (" .
                $ilDB->quote($component, "text") . "," .
                $ilDB->quote($a_attribs["type"], "text") . "," .
                $ilDB->quote($a_attribs["id"], "text") . ")";
            $ilDB->manipulate($q);
        }
    }

    /**
    * end tag handler
    *
    * @param	mixed   	internal xml_parser_handler
    * @param	string		element tag name
    * @access	private
    */
    public function handlerEndTag($a_xml_parser, $a_name): void
    {
    }

    /**
    * end tag handler
    *
    * @param	mixed   	internal xml_parser_handler
    * @param	string		data
    * @access	private
    */
    public function handlerCharacterData($a_xml_parser, $a_data): void
    {
    }
}
