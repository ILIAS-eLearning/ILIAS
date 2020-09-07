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

require_once 'Services/QTI/interfaces/interface.ilQTIMaterialAware.php';

/**
* QTI flow_mat class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIFlowMat implements ilQTIMaterialAware
{
    public $comment;
    public $flow_mat;
    public $material;
    public $material_ref;
    
    public function __construct()
    {
        $this->flow_mat = array();
        $this->material = array();
        $this->material_ref = array();
    }

    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function addFlow_mat($a_flow_mat)
    {
        array_push($this->flow_mat, $a_flow_mat);
    }

    /**
     * {@inheritdoc}
     */
    public function addMaterial(ilQTIMaterial $a_material)
    {
        $this->material[] = $a_material;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaterial($index)
    {
        if (isset($this->material[$index])) {
            return $this->material[$index];
        }

        return null;
    }

    public function addMaterial_ref($a_material_ref)
    {
        array_push($this->material_ref, $a_material_ref);
    }
}
