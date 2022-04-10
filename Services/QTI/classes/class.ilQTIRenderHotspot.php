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

const SHOWDRAW_NO = "1";
const SHOWDRAW_YES = "2";

/**
* QTI render hotspot class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderHotspot
{
    public string $showdraw;
    public ?string $minnumber;
    public ?string $maxnumber;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels;
    /** @var ilQTIMaterial[] */
    public array $material;

    public function __construct()
    {
        $this->showdraw = SHOWDRAW_NO;
        $this->minnumber = null;
        $this->maxnumber = null;
        $this->response_labels = [];
        $this->material = [];
    }

    public function setShowdraw(string $a_showdraw) : void
    {
        switch (strtolower($a_showdraw)) {
            case "1":
            case "no":
                $this->showdraw = SHOWDRAW_NO;
                break;
            case "2":
            case "yes":
                $this->showdraw = SHOWDRAW_YES;
                break;
        }
    }

    public function getShowdraw() : string
    {
        return $this->showdraw;
    }

    public function setMinnumber(string $a_minnumber) : void
    {
        $this->minnumber = $a_minnumber;
    }

    public function getMinnumber() : ?string
    {
        return $this->minnumber;
    }

    public function setMaxnumber(string $a_maxnumber) : void
    {
        $this->maxnumber = $a_maxnumber;
    }

    public function getMaxnumber() : ?string
    {
        return $this->maxnumber;
    }
    
    public function addResponseLabel(ilQTIResponseLabel $a_response_label) : void
    {
        $this->response_labels[] = $a_response_label;
    }

    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $this->material[] = $a_material;
    }
}
