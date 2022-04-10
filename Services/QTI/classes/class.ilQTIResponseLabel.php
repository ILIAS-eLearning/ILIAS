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

const RSHUFFLE_NO = "1";
const RSHUFFLE_YES = "2";

const RAREA_ELLIPSE = "1";
const RAREA_RECTANGLE = "2";
const RAREA_BOUNDED = "3";

const RRANGE_EXACT = "1";
const RRANGE_RANGE = "2";

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
    public ?string $rshuffle;
    public ?string $rarea;
    public ?string $rrange;
    public ?string $labelrefid;
    public ?string $ident;
    public ?string $match_group;
    public ?string $match_max;
    /** @var ilQTIMaterial[] */
    public array $material;
    /** @var ilQTIFlowMat[] */
    public array $flow_mat;
    public ?string $content;

    public function __construct()
    {
        $this->rshuffle = null;
        $this->rarea = null;
        $this->rrange = null;
        $this->labelrefid = null;
        $this->ident = null;
        $this->match_group = null;
        $this->match_max = null;
        $this->material = [];
        $this->flow_mat = [];
        $this->content = null;
    }

    public function setRshuffle(string $a_rshuffle) : void
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

    public function getRshuffle() : ?string
    {
        return $this->rshuffle;
    }

    public function setRarea(string $a_rarea) : void
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

    public function getRarea() : ?string
    {
        return $this->rarea;
    }

    public function setRrange(string $a_rrange) : void
    {
        switch (strtolower($a_rrange)) {
            case "1":
            case "excact":
                $this->rrange = RRANGE_EXACT;
                break;
            case "2":
            case "range":
                $this->rrange = RRANGE_RANGE;
                break;
        }
    }

    public function getRrange() : ?string
    {
        return $this->rrange;
    }

    public function setLabelrefid(string $a_labelrefid) : void
    {
        $this->labelrefid = $a_labelrefid;
    }

    public function getLabelrefid() : ?string
    {
        return $this->labelrefid;
    }

    public function setIdent(string $a_ident) : void
    {
        $this->ident = $a_ident;
    }

    public function getIdent() : ?string
    {
        return $this->ident;
    }
    
    public function setMatchGroup(string $a_match_group) : void
    {
        $this->match_group = $a_match_group;
    }
    
    public function getMatchGroup() : ?string
    {
        return $this->match_group;
    }
    
    public function setMatchMax(string $a_match_max) : void
    {
        $this->match_max = $a_match_max;
    }
    
    public function getMatchMax() : ?string
    {
        return $this->match_max;
    }
    
    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $this->material[] = $a_material;
    }
    
    public function addFlow_mat(ilQTIFlowMat $a_flow_mat) : void
    {
        $this->flow_mat[] = $a_flow_mat;
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
