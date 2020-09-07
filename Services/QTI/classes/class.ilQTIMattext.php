<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

define("SPACE_PRESERVE", "1");
define("SPACE_DEFAULT", "2");

/**
* QTI mattext class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMattext
{
    public $texttype;
    public $label;
    public $charset;
    public $uri;
    public $xmlspace;
    public $xmllang;
    public $entityref;
    public $width;
    public $height;
    public $x0;
    public $y0;
    public $content;
    
    public function __construct()
    {
    }

    public function setTexttype($a_texttype)
    {
        $this->texttype = $a_texttype;
    }
    
    public function getTexttype()
    {
        return $this->texttype;
    }
    
    public function setLabel($a_label)
    {
        $this->label = $a_label;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setHeight($a_height)
    {
        $this->height = $a_height;
    }
    
    public function getHeight()
    {
        return $this->height;
    }
    
    public function setWidth($a_width)
    {
        $this->width = $a_width;
    }
    
    public function getWidth()
    {
        return $this->width;
    }
    
    public function setCharset($a_charset)
    {
        $this->charset = $a_charset;
    }
    
    public function getCharset()
    {
        return $this->charset;
    }
    
    public function setUri($a_uri)
    {
        $this->uri = $a_uri;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function setXmllang($a_xmllang)
    {
        $this->xmllang = $a_xmllang;
    }
    
    public function getXmllang()
    {
        return $this->xmllang;
    }
    
    public function setXmlspace($a_xmlspace)
    {
        switch (strtolower($a_xmlspace)) {
            case "preserve":
            case "1":
                $this->xmlspace = SPACE_PRESERVE;
                break;
            case "default":
            case "2":
                $this->xmlspace = SPACE_DEFAULT;
                break;
        }
    }
    
    public function getXmlspace()
    {
        return $this->xmlspace;
    }
    
    public function setX0($a_x0)
    {
        $this->x0 = $a_x0;
    }
    
    public function getX0()
    {
        return $this->x0;
    }
    
    public function setY0($a_y0)
    {
        $this->y0 = $a_y0;
    }
    
    public function getY0()
    {
        return $this->y0;
    }
    
    public function setEntityref($a_entityref)
    {
        $this->entityref = $a_entityref;
    }
    
    public function getEntityref()
    {
        return $this->entityref;
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
}
