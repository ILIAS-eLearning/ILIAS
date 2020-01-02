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
    public $label;
    public $xmllang;
    public $x0;
    public $y0;
    public $width;
    public $height;
    
    public $material;
    public $response;
    public $order;
    
    public function __construct()
    {
        $this->response = array();
        $this->material = array();
        $this->order = array();
    }
    
    public function setLabel($a_label)
    {
        $this->label = $a_label;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setXmllang($a_xmllang)
    {
        $this->xmllang = $a_xmllang;
    }
    
    public function getXmllang()
    {
        return $this->xmllang;
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
    
    public function setWidth($a_width)
    {
        $this->width = $a_width;
    }
    
    public function getWidth()
    {
        return $this->width;
    }
    
    public function setHeight($a_height)
    {
        $this->height = $a_height;
    }
    
    public function getHeight()
    {
        return $this->height;
    }
    
    public function addMaterial($a_material)
    {
        $count = array_push($this->material, $a_material);
        array_push($this->order, array("type" =>"material", "index" => $count-1));
    }
    
    public function addResponse($a_response)
    {
        $count = array_push($this->response, $a_response);
        array_push($this->order, array("type" =>"response", "index" => $count-1));
    }
}
