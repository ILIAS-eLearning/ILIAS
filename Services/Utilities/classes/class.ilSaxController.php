<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Controller class for sax element handlers.
* Could be used to split the xml handling in different responsible classes.
* Use the methods addElementHandler, addDefaultElementHandler
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup
*/
class ilSaxController
{
    protected $default_handler = null;
    protected $element_handlers = array();
    protected $handlers_in_use = array();
        
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
    }
    
    /**
     * Set handlers
     *
     * @access public
     * @param resource xml_parser instance
     *
     */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
        return;
    }
    
    /**
     * Set default element handler
     *
     * @access public
     * @param object object that parses the xmlsubset must implement interface ilSaxSubsetParser
     *
     */
    public function setDefaultElementHandler(ilSaxSubsetParser $a_default_parser)
    {
        $this->default_handler = $a_default_parser;
    }
    
    /**
     * Set element handler by element name
     *
     * @param string element name that triggers the handler
     * @param object object that parses the xmlsubset must implement interface ilSaxSubsetParser
     * @access public
     *
     */
    public function setElementHandler(ilSaxSubsetParser $a_parser, $a_element)
    {
        $this->element_handlers[$a_element] = $a_parser;
        $this->handlers_in_use[$a_element] = false;
    }
    
    /**
     * Set default element handler
     *
     * @access public
     * @param string xml element that triggers the handler call
     * @param object object that parses the xmlsubset
     * @param string method name
     *
     */

    /**
     * default handlerBeginTag
     *
     * @access public
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_name				element name
     * @param	array		$a_attribs			element attributes array
     */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        if (isset($this->element_handlers[$a_name]) or $this->handler_in_use) {
            if (!$this->handler_in_use) {
                $this->handler_in_use = $this->element_handlers[$a_name];
            }
            // Forward to handler
            $this->current_handler = $this->handler_in_use;
            return $this->current_handler->handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
        }
        // Call default handler
        $this->handler_in_use = false;
        $this->current_handler = $this->default_handler;
        return $this->default_handler->handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
    }
    
    /**
    * handler for end of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        if (isset($this->element_handlers[$a_name])) {
            $this->handler_in_use = false;
            $this->current_handler = $this->element_handlers[$a_name];
            return $this->element_handlers[$a_name]->handlerEndTag($a_xml_parser, $a_name);
        } elseif ($this->handler_in_use) {
            $this->current_handler = $this->handler_in_use;
            return $this->current_handler->handlerEndTag($a_xml_parser, $a_name);
        }
        $this->handler_in_use = false;
        $this->current_handler = $this->default_handler;
        return $this->default_handler->handlerEndTag($a_xml_parser, $a_name);
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        return $this->current_handler->handlerCharacterData($a_xml_parser, $a_data);
    }
}
