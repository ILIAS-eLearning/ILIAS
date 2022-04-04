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
    /** @var string */
    public $showdraw;

    /** @var string|null */
    public $minnumber;

    /** @var string|null */
    public $maxnumber;

    /** @var ilQTIResponseLabel[] */
    public $response_labels;

    /** @var ilQTIMaterial[] */
    public $material;

    public function __construct()
    {
        $this->showdraw = SHOWDRAW_NO;
        $this->response_labels = array();
        $this->material = array();
    }

    /**
     * @param string $a_showdraw
     */
    public function setShowdraw($a_showdraw) : void
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

    /**
     * @return string
     */
    public function getShowdraw()
    {
        return $this->showdraw;
    }

    /**
     * @param string $a_minnumber
     */
    public function setMinnumber($a_minnumber) : void
    {
        $this->minnumber = $a_minnumber;
    }

    /**
     * @return string|null
     */
    public function getMinnumber()
    {
        return $this->minnumber;
    }

    /**
     * @param string $a_maxnumber
     */
    public function setMaxnumber($a_maxnumber) : void
    {
        $this->maxnumber = $a_maxnumber;
    }

    /**
     * @return string|null
     */
    public function getMaxnumber()
    {
        return $this->maxnumber;
    }
    
    public function addResponseLabel($a_response_label) : void
    {
        $this->response_labels[] = $a_response_label;
    }

    public function addMaterial($a_material) : void
    {
        $this->material[] = $a_material;
    }
}
