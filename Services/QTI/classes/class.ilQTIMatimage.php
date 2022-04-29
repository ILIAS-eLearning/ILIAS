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

/**
* QTI matimage class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMatimage
{
    public const EMBEDDED_BASE64 = 'base64';

    public ?string $imagetype;
    public ?string $label;
    public ?string $height;
    public ?string $width;
    public ?string $uri;
    public ?string $embedded;
    public ?string $x0;
    public ?string $y0;
    public ?string $entityref;
    public ?string $content;
    
    public function __construct()
    {
        $this->imagetype = null;
        $this->label = null;
        $this->height = null;
        $this->width = null;
        $this->uri = null;
        $this->embedded = null;
        $this->x0 = null;
        $this->y0 = null;
        $this->entityref = null;
        $this->content = null;
    }

    public function setImagetype(string $a_imagetype) : void
    {
        $this->imagetype = $a_imagetype;
    }

    public function getImagetype() : ?string
    {
        return $this->imagetype;
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

    public function setEmbedded(string $a_embedded) : void
    {
        $this->embedded = $a_embedded;
    }

    public function getEmbedded() : ?string
    {
        return $this->embedded;
    }

    public function setUri(string $a_uri) : void
    {
        $this->uri = $a_uri;
    }

    public function getUri() : ?string
    {
        return $this->uri;
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

    public function setContent(?string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }

    /**
     * @return string|null|false
     */
    public function getRawContent()
    {
        switch ($this->getEmbedded()) {
            case self::EMBEDDED_BASE64:
                
                return base64_decode($this->getContent());
        }
        
        return $this->getContent();
    }
}
