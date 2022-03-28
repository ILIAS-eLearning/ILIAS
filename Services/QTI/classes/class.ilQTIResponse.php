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
const RT_RESPONSE_LID = "1";
const RT_RESPONSE_XY = "2";
const RT_RESPONSE_STR = "3";
const RT_RESPONSE_NUM = "4";
const RT_RESPONSE_GRP = "5";
const RT_RESPONSE_EXTENSION = "6";

const R_CARDINALITY_SINGLE = "1";
const R_CARDINALITY_MULTIPLE = "2";
const R_CARDINALITY_ORDERED = "3";

const RTIMING_NO = "1";
const RTIMING_YES = "2";

const NUMTYPE_INTEGER = "1";
const NUMTYPE_DECIMAL = "2";
const NUMTYPE_SCIENTIFIC = "3";
    
/**
* QTI response class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponse
{
    /** @var int */
    public $flow;

    /** @var int|string */
    public $response_type;

    /** @var string|null */
    public $ident;

    /** @var string */
    public $rcardinality;

    /**
     * @var ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public $render_type;
    public $material1;
    public $material2;

    /** @var string|null */
    public $rtiming;

    /** @var string|null */
    public $numtype;
    
    public function __construct($a_response_type = 0)
    {
        $this->flow = 0;
        $this->render_type = null;
        $this->response_type = $a_response_type;
    }

    /**
     * @param int
     */
    public function setResponsetype($a_responsetype) : void
    {
        $this->response_type = $a_responsetype;
    }

    /**
     * @return int|string
     */
    public function getResponsetype()
    {
        return $this->response_type;
    }

    /**
     * @param string $a_ident
     */
    public function setIdent($a_ident) : void
    {
        $this->ident = $a_ident;
    }

    /**
     * @return string|null
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param string
     */
    public function setRCardinality($a_rcardinality) : void
    {
        switch (strtolower($a_rcardinality)) {
            case "single":
            case "1":
                $this->rcardinality = R_CARDINALITY_SINGLE;
                break;
            case "multiple":
            case "2":
                $this->rcardinality = R_CARDINALITY_MULTIPLE;
                break;
            case "ordered":
            case "3":
                $this->rcardinality = R_CARDINALITY_ORDERED;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getRCardinality()
    {
        return $this->rcardinality;
    }

    /**
     * @param string
     */
    public function setRTiming($a_rtiming) : void
    {
        switch (strtolower($a_rtiming)) {
            case "no":
            case "1":
                $this->rtiming = RTIMING_NO;
                break;
            case "yes":
            case "2":
                $this->rtiming = RTIMING_YES;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getRTiming()
    {
        return $this->rtiming;
    }

    /**
     * @param string $a_numtype
     */
    public function setNumtype($a_numtype) : void
    {
        switch (strtolower($a_numtype)) {
            case "integer":
            case "1":
                $this->numtype = NUMTYPE_INTEGER;
                break;
            case "decimal":
            case "2":
                $this->numtype = NUMTYPE_DECIMAL;
                break;
            case "scientific":
            case "3":
                $this->numtype = NUMTYPE_SCIENTIFIC;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getNumtype()
    {
        return $this->numtype;
    }

    /**
     * @param ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib $a_render_type
     */
    public function setRenderType($a_render_type) : void
    {
        $this->render_type = $a_render_type;
    }

    /**
     * @return ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public function getRenderType()
    {
        return $this->render_type;
    }

    /**
     * @param int $a_flow
     */
    public function setFlow($a_flow) : void
    {
        $this->flow = $a_flow;
    }

    /**
     * @return int
     */
    public function getFlow()
    {
        return $this->flow;
    }
    
    public function setMaterial1($a_material) : void
    {
        $this->material1 = $a_material;
    }
    
    public function getMaterial1()
    {
        return $this->material1;
    }

    public function setMaterial2($a_material) : void
    {
        $this->material2 = $a_material;
    }
    
    public function getMaterial2()
    {
        return $this->material2;
    }
    
    public function hasRendering() : bool
    {
        return $this->render_type != null;
    }
}
