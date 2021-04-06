<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface definition for sax subset parsers
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
interface ilSaxSubsetParser
{
    /**
     * Start element handler
     *
     * @access public
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_name				element name
     * @param	array		$a_attribs			element attributes array
     *
     */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs);

    /**
     * End element handler
     *
     * @access public
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_name				element name
     *
     */
    public function handlerEndTag($a_xml_parser, $a_name);
    
    /**
     * Character data handler
     *
     * @access public
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_data				character data
     */
    public function handlerCharacterData($a_xml_parser, $a_name);
}
