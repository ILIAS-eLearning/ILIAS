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
 * Controller class for sax element handlers.
 * Could be used to split the xml handling in different responsible classes.
 * Use the methods addElementHandler, addDefaultElementHandler
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSaxController
{
    protected ?ilSaxSubsetParser $default_handler = null;
    protected array $element_handlers = [];
    protected array $handlers_in_use = [];
    /**
     * @var resource
     */
    protected $handler_in_use = null;
    protected ?ilSaxSubsetParser $current_handler;

    /**
     * Set handlers
     *
     * @param resource xml_parser instance
     */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * Set default element handler
     */
    public function setDefaultElementHandler(ilSaxSubsetParser $a_default_parser): void
    {
        $this->default_handler = $a_default_parser;
    }

    /**
     * @param ilSaxSubsetParser $a_parser object that parses the xmlsubset must implement interface ilSaxSubsetParser
     * @param string $a_element element name that triggers the handler
     */
    public function setElementHandler(ilSaxSubsetParser $a_parser, string $a_element): void
    {
        $this->element_handlers[$a_element] = $a_parser;
        $this->handlers_in_use[$a_element] = false;
    }

    /**
     * default handlerBeginTag
     *
     * @access public
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_name				element name
     * @param	array		$a_attribs			element attributes array
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        if (isset($this->element_handlers[$a_name]) or $this->handler_in_use) {
            if (!$this->handler_in_use) {
                $this->handler_in_use = $this->element_handlers[$a_name];
            }
            // Forward to handler
            $this->current_handler = $this->handler_in_use;
            $this->current_handler->handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return;
        }
        // Call default handler
        $this->handler_in_use = false;
        $this->current_handler = $this->default_handler;
        $this->default_handler->handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
    }

    /**
    * handler for end of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        if (isset($this->element_handlers[$a_name])) {
            $this->handler_in_use = false;
            $this->current_handler = $this->element_handlers[$a_name];
            $this->element_handlers[$a_name]->handlerEndTag($a_xml_parser, $a_name);
            return;
        } elseif ($this->handler_in_use) {
            $this->current_handler = $this->handler_in_use;
            $this->current_handler->handlerEndTag($a_xml_parser, $a_name);
            return;
        }
        $this->handler_in_use = false;
        $this->current_handler = $this->default_handler;
        $this->default_handler->handlerEndTag($a_xml_parser, $a_name);
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData(
        $a_xml_parser,
        string $a_data
    ): void {
        $this->current_handler->handlerCharacterData(
            $a_xml_parser,
            $a_data
        );
    }
}
