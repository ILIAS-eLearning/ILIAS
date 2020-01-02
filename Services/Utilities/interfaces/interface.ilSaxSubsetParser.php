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
* Interface definition for sax subset parsers
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
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
