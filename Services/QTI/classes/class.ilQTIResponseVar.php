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
    public ?string $vartype;
    public ?string $case;
    public ?string $respident;
    public ?string $index;
    public ?string $setmatch;
    public ?string $areatype;
    public ?string $content;
    
    public function __construct(string $a_vartype)
    {
        $this->setVartype($a_vartype);
        $this->case = null;
        $this->respident = null;
        $this->index = null;
        $this->setmatch = null;
        $this->areatype = null;
        $this->content = null;
    }

    public function setVartype(string $a_vartype) : void
    {
        $this->vartype = $a_vartype;
    }

    public function getVartype() : ?string
    {
        return $this->vartype;
    }

    public function setCase(string $a_case) : void
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

    public function getCase() : ?string
    {
        return $this->case;
    }

    public function setRespident(string $a_respident) : void
    {
        $this->respident = $a_respident;
    }

    public function getRespident() : ?string
    {
        return $this->respident;
    }

    public function setIndex(string $a_index) : void
    {
        $this->index = $a_index;
    }

    public function getIndex() : ?string
    {
        return $this->index;
    }

    public function setSetmatch(string $a_setmatch) : void
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

    public function getSetmatch() : ?string
    {
        return $this->setmatch;
    }

    public function setAreatype(string $a_areatype) : void
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

    public function getAreatype() : ?string
    {
        return $this->areatype;
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
