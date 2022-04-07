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
* QTI presentation class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIPresentation
{
    public ?string $label;
    public ?string $xmllang;
    public ?string $x0;
    public ?string $y0;
    public ?string $width;
    public ?string $height;
    /** @var ilQTIMaterial[] */
    public array $material;

    /**
     * @var ilQTIResponse[]
     */
    public array $response;

    /**
     * @var array{type: string, index: int}[]
     */
    public array $order;
    
    public function __construct()
    {
        $this->label = null;
        $this->xmllang = null;
        $this->x0 = null;
        $this->y0 = null;
        $this->width = null;
        $this->height = null;
        $this->response = [];
        $this->material = [];
        $this->order = [];
    }

    public function setLabel(string $a_label) : void
    {
        $this->label = $a_label;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setXmllang(string $a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang() : ?string
    {
        return $this->xmllang;
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

    public function setWidth(string $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getWidth() : ?string
    {
        return $this->width;
    }

    public function setHeight(string $a_height) : void
    {
        $this->height = $a_height;
    }

    public function getHeight() : ?string
    {
        return $this->height;
    }
    
    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $count = array_push($this->material, $a_material);
        $this->order[] = array("type" => "material", "index" => $count - 1);
    }

    public function addResponse(ilQTIResponse $a_response) : void
    {
        $count = array_push($this->response, $a_response);
        $this->order[] = array("type" => "response", "index" => $count - 1);
    }
}
