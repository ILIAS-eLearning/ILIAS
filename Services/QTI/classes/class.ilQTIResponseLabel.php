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

define("RSHUFFLE_NO", "1");
define("RSHUFFLE_YES", "2");

define("RAREA_ELLIPSE", "1");
define("RAREA_RECTANGLE", "2");
define("RAREA_BOUNDED", "3");

define("RRANGE_EXACT", "1");
define("RRANGE_RANGE", "2");

/**
* QTI response label class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponseLabel
{
    public $rshuffle;
    public $rarea;
    public $rrange;
    public $labelrefid;
    public $ident;
    public $match_group;
    public $match_max;
    public $material;
    public $flow_mat;
    public $content;

    public function __construct()
    {
        $this->material = array();
        $this->flow_mat = array();
    }
    
    public function setRshuffle($a_rshuffle)
    {
        switch (strtolower($a_rshuffle)) {
            case "1":
            case "no":
                $this->rshuffle = RSHUFFLE_NO;
                break;
            case "2":
            case "yes":
                $this->rshuffle = RSHUFFLE_YES;
                break;
        }
    }
    
    public function getRshuffle()
    {
        return $this->rshuffle;
    }
    
    public function setRarea($a_rarea)
    {
        switch (strtolower($a_rarea)) {
            case "1":
            case "ellipse":
                $this->rarea = RAREA_ELLIPSE;
                break;
            case "2":
            case "rectangle":
                $this->rarea = RAREA_RECTANGLE;
                break;
            case "3":
            case "bounded":
                $this->rarea = RAREA_BOUNDED;
                break;
        }
    }
    
    public function getRarea()
    {
        return $this->rarea;
    }
    
    public function setRrange($a_rrange)
    {
        switch (strtolower($a_rrange)) {
            case "1":
            case "excact":
                $this->rshuffle = RRANGE_EXACT;
                break;
            case "2":
            case "range":
                $this->rshuffle = RRANGE_RANGE;
                break;
        }
    }
    
    public function getRrange()
    {
        return $this->rrange;
    }
    
    public function setLabelrefid($a_labelrefid)
    {
        $this->labelrefid = $a_labelrefid;
    }
    
    public function getLabelrefid()
    {
        return $this->labelrefid;
    }
    
    public function setIdent($a_ident)
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }
    
    public function setMatchGroup($a_match_group)
    {
        $this->match_group = $a_match_group;
    }
    
    public function getMatchGroup()
    {
        return $this->match_group;
    }
    
    public function setMatchMax($a_match_max)
    {
        $this->match_max = $a_match_max;
    }
    
    public function getMatchMax()
    {
        return $this->match_max;
    }
    
    public function addMaterial($a_material)
    {
        array_push($this->material, $a_material);
    }
    
    public function addFlow_mat($a_flow_mat)
    {
        array_push($this->flow_mat, $a_flow_mat);
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
}
