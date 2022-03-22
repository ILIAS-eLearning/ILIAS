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

const SPACE_PRESERVE = "1";
const SPACE_DEFAULT = "2";

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
    /** @var string|null */
    public $texttype;

    /** @var string|null */
    public $label;

    /** @var string|null */
    public $charset;

    /** @var string|null */
    public $uri;

    /** @var string|null */
    public $xmlspace;

    /** @var string|null */
    public $xmllang;

    /** @var string|null */
    public $entityref;

    /** @var string|null */
    public $width;

    /** @var string|null */
    public $height;

    /** @var string|null */
    public $x0;

    /** @var string|null */
    public $y0;

    /** @var string|null */
    public $content;
    
    public function __construct()
    {
    }

    /**
     * @param string $a_texttype
     */
    public function setTexttype($a_texttype) : void
    {
        $this->texttype = $a_texttype;
    }

    /**
     * @return string|null
     */
    public function getTexttype()
    {
        return $this->texttype;
    }

    /**
     * @param string $a_label
     */
    public function setLabel($a_label) : void
    {
        $this->label = $a_label;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $a_height
     */
    public function setHeight($a_height) : void
    {
        $this->height = $a_height;
    }

    /**
     * @return string|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $a_width
     */
    public function setWidth($a_width) : void
    {
        $this->width = $a_width;
    }

    /**
     * @return string|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $a_charset
     */
    public function setCharset($a_charset) : void
    {
        $this->charset = $a_charset;
    }

    /**
     * @return string|null
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $a_uri
     */
    public function setUri($a_uri) : void
    {
        $this->uri = $a_uri;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $a_xmllang
     */
    public function setXmllang($a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }

    /**
     * @return string|null
     */
    public function getXmllang()
    {
        return $this->xmllang;
    }

    /**
     * @param string $a_xmlspace
     */
    public function setXmlspace($a_xmlspace) : void
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

    /**
     * @return string|null
     */
    public function getXmlspace()
    {
        return $this->xmlspace;
    }
    
    public function setX0($a_x0) : void
    {
        $this->x0 = $a_x0;
    }
    
    public function getX0()
    {
        return $this->x0;
    }
    
    public function setY0($a_y0) : void
    {
        $this->y0 = $a_y0;
    }
    
    public function getY0()
    {
        return $this->y0;
    }

    /**
     * @param string $a_entityref
     */
    public function setEntityref($a_entityref) : void
    {
        $this->entityref = $a_entityref;
    }

    /**
     * @return string|null
     */
    public function getEntityref()
    {
        return $this->entityref;
    }

    /**
     * @param string $a_content
     */
    public function setContent($a_content) : void
    {
        $this->content = $a_content;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
