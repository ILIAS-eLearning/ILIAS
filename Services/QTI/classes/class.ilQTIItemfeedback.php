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

const VIEW_ALL = "1";
const VIEW_ADMINISTRATOR = "2";
const VIEW_ADMINAUTHORITY = "3";
const VIEW_ASSESSOR = "4";
const VIEW_AUTHOR = "5";
const VIEW_CANDIDATE = "6";
const VIEW_INVIGILATORPROCTOR = "7";
const VIEW_PSYCHOMETRICIAN = "8";
const VIEW_SCORER = "9";
const VIEW_TUTOR = "10";

/**
* QTI itemfeedback class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIItemfeedback
{
    /** @var string|null */
    public $view;
    public $ident;

    /** @var string|null @todo check */
    public $title;

    /** @var array */
    public $flow_mat;

    /** @var array */
    public $material;

    /** @var array */
    public $solution;

    /** @var array */
    public $hint;
    
    public function __construct()
    {
        $this->flow_mat = array();
        $this->material = array();
        $this->solution = array();
        $this->hint = array();
    }

    /**
     * @param string
     */
    public function setView($a_view) : void
    {
        switch (strtolower($a_view)) {
            case "1":
            case "all":
                $this->view = VIEW_ALL;
                break;
            case "2":
            case "administrator":
                $this->view = VIEW_ADMINISTRATOR;
                break;
            case "3":
            case "adminauthority":
                $this->view = VIEW_ADMINAUTHORITY;
                break;
            case "4":
            case "assessor":
                $this->view = VIEW_ASSESSOR;
                break;
            case "5":
            case "author":
                $this->view = VIEW_AUTHOR;
                break;
            case "6":
            case "candidate":
                $this->view = VIEW_CANDIDATE;
                break;
            case "7":
            case "invigilatorproctor":
                $this->view = VIEW_INVIGILATORPROCTOR;
                break;
            case "8":
            case "psychometrician":
                $this->view = VIEW_PSYCHOMETRICIAN;
                break;
            case "9":
            case "scorer":
                $this->view = VIEW_SCORER;
                break;
            case "10":
            case "tutor":
                $this->view = VIEW_TUTOR;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getView()
    {
        return $this->view;
    }
    
    public function setIdent($a_ident) : void
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param string|null $a_title
     */
    public function setTitle($a_title) : void
    {
        $this->title = $a_title;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    public function addFlow_mat($a_flow_mat) : void
    {
        $this->flow_mat[] = $a_flow_mat;
    }
    
    public function addMaterial($a_material) : void
    {
        $this->material[] = $a_material;
    }
    
    public function addSolution($a_solution) : void
    {
        $this->solution[] = $a_solution;
    }
    
    public function addHint($a_hint) : void
    {
        $this->hint[] = $a_hint;
    }
}
