<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
     * @param	resource	$a_xml_parser		xml parser
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void;

    /**
     * End element handler
     *
     * @param	resource	$a_xml_parser		xml parser
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void;

    /**
     * Character data handler
     * @param	resource	$a_xml_parser		xml parser
     */
    public function handlerCharacterData($a_xml_parser, string $a_name) : void;
}
