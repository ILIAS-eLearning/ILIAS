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
    public ?string $texttype;
    public ?string $label;
    public ?string $charset;
    public ?string $uri;
    public ?string $xmlspace;
    public ?string $xmllang;
    public ?string $entityref;
    public ?string $width;
    public ?string $height;
    public ?string $x0;
    public ?string $y0;
    public ?string $content;
    
    public function __construct()
    {
        $this->texttype = null;
        $this->label = null;
        $this->charset = null;
        $this->uri = null;
        $this->xmlspace = null;
        $this->xmllang = null;
        $this->entityref = null;
        $this->width = null;
        $this->height = null;
        $this->x0 = null;
        $this->y0 = null;
        $this->content = null;
    }

    public function setTexttype(string $a_texttype) : void
    {
        $this->texttype = $a_texttype;
    }

    public function getTexttype() : ?string
    {
        return $this->texttype;
    }

    public function setLabel(string $a_label) : void
    {
        $this->label = $a_label;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setHeight(string $a_height) : void
    {
        $this->height = $a_height;
    }

    public function getHeight() : ?string
    {
        return $this->height;
    }

    public function setWidth(string $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getWidth() : ?string
    {
        return $this->width;
    }

    public function setCharset(string $a_charset) : void
    {
        $this->charset = $a_charset;
    }

    public function getCharset() : ?string
    {
        return $this->charset;
    }

    public function setUri(string $a_uri) : void
    {
        $this->uri = $a_uri;
    }

    public function getUri() : ?string
    {
        return $this->uri;
    }

    public function setXmllang(string $a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang() : ?string
    {
        return $this->xmllang;
    }

    public function setXmlspace(string $a_xmlspace) : void
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

    public function getXmlspace() : ?string
    {
        return $this->xmlspace;
    }
    
    public function setX0(string $a_x0) : void
    {
        $this->x0 = $a_x0;
    }
    
    public function getX0() : ?string
    {
        return $this->x0;
    }
    
    public function setY0(string $a_y0) : void
    {
        $this->y0 = $a_y0;
    }
    
    public function getY0() : ?string
    {
        return $this->y0;
    }

    public function setEntityref(string $a_entityref) : void
    {
        $this->entityref = $a_entityref;
    }

    public function getEntityref() : ?string
    {
        return $this->entityref;
    }

    public function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }
}
