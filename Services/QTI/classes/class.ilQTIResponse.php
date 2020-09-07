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
    define("RT_RESPONSE_LID", "1");
    define("RT_RESPONSE_XY", "2");
    define("RT_RESPONSE_STR", "3");
    define("RT_RESPONSE_NUM", "4");
    define("RT_RESPONSE_GRP", "5");
    define("RT_RESPONSE_EXTENSION", "6");
    
    define("R_CARDINALITY_SINGLE", "1");
    define("R_CARDINALITY_MULTIPLE", "2");
    define("R_CARDINALITY_ORDERED", "3");

    define("RTIMING_NO", "1");
    define("RTIMING_YES", "2");
    
    define("NUMTYPE_INTEGER", "1");
    define("NUMTYPE_DECIMAL", "2");
    define("NUMTYPE_SCIENTIFIC", "3");
    
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
    public $flow;
    public $response_type;
    public $ident;
    public $rcardinality;
    public $render_type;
    public $material1;
    public $material2;
    public $rtiming;
    public $numtype;
    
    public function __construct($a_response_type = 0)
    {
        $this->flow = 0;
        $this->render_type = null;
        $this->response_type = $a_response_type;
    }
    
    public function setResponsetype($a_responsetype)
    {
        $this->response_type = $a_responsetype;
    }
    
    public function getResponsetype()
    {
        return $this->response_type;
    }
    
    public function setIdent($a_ident)
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }
    
    public function setRCardinality($a_rcardinality)
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
    
    public function getRCardinality()
    {
        return $this->rcardinality;
    }
    
    public function setRTiming($a_rtiming)
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
    
    public function getRTiming()
    {
        return $this->rtiming;
    }
    
    public function setNumtype($a_numtype)
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
    
    public function getNumtype()
    {
        return $this->numtype;
    }
    
    public function setRenderType($a_render_type)
    {
        $this->render_type = $a_render_type;
    }
    
    public function getRenderType()
    {
        return $this->render_type;
    }
    
    public function setFlow($a_flow)
    {
        $this->flow = $a_flow;
    }
    
    public function getFlow()
    {
        return $this->flow;
    }
    
    public function setMaterial1($a_material)
    {
        $this->material1 = $a_material;
    }
    
    public function getMaterial1()
    {
        return $this->material1;
    }

    public function setMaterial2($a_material)
    {
        $this->material2 = $a_material;
    }
    
    public function getMaterial2()
    {
        return $this->material2;
    }
    
    public function hasRendering()
    {
        if ($this->render_type != null) {
            return true;
        } else {
            return false;
        }
    }
}
