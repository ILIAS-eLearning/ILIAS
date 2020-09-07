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
* QTI flow class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $$
*
* @package assessment
*/
class ilQTIFlow
{
    public $comment;
    public $flow;
    public $material;
    public $material_ref;
    public $response;
    
    public function __construct()
    {
        $this->flow = array();
        $this->material = array();
        $this->material_ref = array();
        $this->response = array();
    }

    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function addFlow($a_flow, $a_index)
    {
        $this->flow[$a_index] = $a_flow;
    }
    
    public function addMaterial($a_material, $a_index)
    {
        $this->material[$a_index] = $a_material;
    }
    
    public function addMaterial_ref($a_material_ref, $a_index)
    {
        $this->material_ref[$a_index] = $a_material_ref;
    }
    
    public function addResponse($a_response, $a_index)
    {
        $this->response[$a_index] = $a_response;
    }
}
