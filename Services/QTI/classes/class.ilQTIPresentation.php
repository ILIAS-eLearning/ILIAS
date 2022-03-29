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
    /** @var string|null */
    public $label;

    /** @var string|null */
    public $xmllang;

    /** @var string|null */
    public $x0;

    /** @var string|null */
    public $y0;

    /** @var string|null */
    public $width;

    /** @var string|null */
    public $height;

    /**
     * @var array
     */
    public $material;

    /**
     * @var ilQTIResponse[]
     */
    public $response;

    /**
     * @var array{type: string, index: int}[]
     */
    public $order;
    
    public function __construct()
    {
        $this->response = array();
        $this->material = array();
        $this->order = array();
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
    
    public function addMaterial($a_material) : void
    {
        $count = array_push($this->material, $a_material);
        $this->order[] = array("type" => "material", "index" => $count - 1);
    }

    /**
     * @param ilQTIResponse $a_response
     */
    public function addResponse($a_response) : void
    {
        $count = array_push($this->response, $a_response);
        $this->order[] = array("type" => "response", "index" => $count - 1);
    }
}
