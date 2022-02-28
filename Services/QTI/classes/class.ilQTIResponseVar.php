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

const RESPONSEVAR_EQUAL = "1";
const RESPONSEVAR_LT = "2";
const RESPONSEVAR_LTE = "3";
const RESPONSEVAR_GT = "4";
const RESPONSEVAR_GTE = "5";
const RESPONSEVAR_SUBSET = "6";
const RESPONSEVAR_INSIDE = "7";
const RESPONSEVAR_SUBSTRING = "8";

const CASE_YES = "1";
const CASE_NO = "2";

const SETMATCH_PARTIAL = "1";
const SETMATCH_EXACT = "2";

const AREATYPE_ELLIPSE = "1";
const AREATYPE_RECTANGLE = "2";
const AREATYPE_BOUNDED = "3";

/**
* QTI response variable class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponseVar
{
    /** @var string|null */
    public $vartype;

    /** @var string|null */
    public $case;

    /** @var string|null */
    public $respident;

    /** @var string|null */
    public $index;

    /** @var string|null */
    public $setmatch;

    /** @var string|null */
    public $areatype;

    /** @var string|null */
    public $content;
    
    public function __construct($a_vartype)
    {
        $this->setVartype($a_vartype);
    }
    
    public function setVartype($a_vartype) : void
    {
        $this->vartype = $a_vartype;
    }
    
    public function getVartype()
    {
        return $this->vartype;
    }

    /**
     * @param string $a_case
     */
    public function setCase($a_case) : void
    {
        switch (strtolower($a_case)) {
            case "1":
            case "yes":
                $this->case = CASE_YES;
                break;
            case "2":
            case "no":
                $this->case = CASE_NO;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getCase()
    {
        return $this->case;
    }
    
    public function setRespident($a_respident) : void
    {
        $this->respident = $a_respident;
    }
    
    public function getRespident()
    {
        return $this->respident;
    }
    
    public function setIndex($a_index) : void
    {
        $this->index = $a_index;
    }
    
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $a_setmatch
     */
    public function setSetmatch($a_setmatch) : void
    {
        switch (strtolower($a_setmatch)) {
            case "1":
            case "partial":
                $this->setmatch = SETMATCH_PARTIAL;
                break;
            case "2":
            case "exact":
                $this->setmatch = SETMATCH_EXACT;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getSetmatch()
    {
        return $this->setmatch;
    }

    /**
     * @param string $a_areatype
     */
    public function setAreatype($a_areatype) : void
    {
        switch (strtolower($a_areatype)) {
            case "1":
            case "ellipse":
                $this->areatype = AREATYPE_ELLIPSE;
                break;
            case "2":
            case "rectangle":
                $this->areatype = AREATYPE_RECTANGLE;
                break;
            case "3":
            case "bounded":
                $this->areatype = AREATYPE_BOUNDED;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getAreatype()
    {
        return $this->areatype;
    }
    
    public function setContent($a_content) : void
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
}
