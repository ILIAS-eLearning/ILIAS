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
* QTI objectives class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIObjectives
{
    public $materials;
    public $view;
    
    public function __construct()
    {
        $this->materials = array();
        $this->view = "All";
    }
    
    public function addMaterial($a_material)
    {
        array_push($this->materials, $a_material);
    }
    
    public function setView($a_view)
    {
        switch ($a_view) {
            case "Administrator":
            case "AdminAuthority":
            case "Assessor":
            case "Author":
            case "Candidate":
            case "InvigilatorProctor":
            case "Psychometrician":
            case "Scorer":
            case "Tutor":
                $this->view = $a_view;
                break;
            default:
                $this->view = "All";
                break;
        }
    }
    
    public function getView()
    {
        return $this->view;
    }
}
