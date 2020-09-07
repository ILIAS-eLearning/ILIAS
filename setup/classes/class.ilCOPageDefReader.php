<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * COPage definition xml reader class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilCOPageDefReader
{
    /**
     * Clear definition tables
     *
     * @param
     * @return
     */
    public function clearTables()
    {
        global $ilDB;
        
        $ilDB->manipulate("DELETE FROM copg_pc_def");
        $ilDB->manipulate("DELETE FROM copg_pobj_def");
    }

    /**
     * Start tag handler
     *
     * @param object internal xml_parser_handler
     * @param string element tag name
     * @param array element attributes
     */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs, $a_comp)
    {
        global $ilDB;
        
        switch ($a_name) {
            case "pagecontent":
                $ilDB->manipulate("INSERT INTO copg_pc_def " .
                    "(pc_type, name, component, directory, int_links, style_classes, xsl, def_enabled) VALUES (" .
                    $ilDB->quote($a_attribs["pc_type"], "text") . "," .
                    $ilDB->quote($a_attribs["name"], "text") . "," .
                    $ilDB->quote($a_comp, "text") . "," .
                    $ilDB->quote($a_attribs["directory"], "text") . "," .
                    $ilDB->quote($a_attribs["int_links"], "integer") . "," .
                    $ilDB->quote($a_attribs["style_classes"], "integer") . "," .
                    $ilDB->quote($a_attribs["xsl"], "integer") . "," .
                    $ilDB->quote($a_attribs["def_enabled"], "integer") .
                    ")");
                break;

            case "pageobject":
                $ilDB->manipulate("INSERT INTO copg_pobj_def " .
                    "(parent_type, class_name, component, directory) VALUES (" .
                    $ilDB->quote($a_attribs["parent_type"], "text") . "," .
                    $ilDB->quote($a_attribs["class_name"], "text") . "," .
                    $ilDB->quote($a_comp, "text") . "," .
                    $ilDB->quote($a_attribs["directory"], "text") .
                    ")");
                break;
        }
    }
    
    /**
     * End tag handler
     *
     * @param object internal xml_parser_handler
     * @param string element tag name
     */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
    }
}
