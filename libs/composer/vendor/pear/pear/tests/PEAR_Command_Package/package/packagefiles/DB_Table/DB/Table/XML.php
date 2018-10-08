<?php

/**
 * A few simple static methods for writing XML
 * 
 * @category Database
 * @package DB_Table
 * @author David Morse <morse@php.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: XML.php,v 1.1 2007/06/13 04:53:40 morse Exp $
 */

/**
 * class DB_Table_XML contains a few simple static methods for writing XML
 * 
 * @category Database
 * @package DB_Table
 * @author David Morse <morse@php.net>
 * 
 * @version @package_version@
 *
 */

class DB_Table_XML
{
    
    /**
     * Returns XML closing tag <tag>, increases $indent by 3 spaces
     *
     * @static
     * @param string $tag    XML element tag name
     * @param string $indent current indentation, string of spaces
     * @return string XML opening tag
     * @access public
     */
    function openTag($tag, &$indent)
    {
        $old_indent = $indent;
        $indent = $indent . '   ';
        return $old_indent . "<$tag>";
    }


    /**
     * Returns XML closing tag </tag>, decreases $indent by 3 spaces
     *
     * @static
     * @param string $tag    XML element tag name
     * @param string $indent current indentation, string of spaces
     * @return string XML closing tag
     * @access public
     */
    function closeTag($tag, &$indent)
    {
        $indent = substr($indent, 0, -3);
        return $indent . "</$tag>";
    }


    /**
     * Returns string single line XML element <tag>text</tag>
     *
     * @static
     * @param string $tag    XML element tag name
     * @param string $text   element contents
     * @param string $indent current indentation, string of spaces
     * @return string single-line XML element
     * @access public
     */
    function lineElement($tag, $text, $indent)
    {
        return $indent . "<$tag>$text</$tag>";
    }

}
?>
