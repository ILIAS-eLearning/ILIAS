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

const VARTYPE_INTEGER = "1";
const VARTYPE_STRING = "2";
const VARTYPE_DECIMAL = "3";
const VARTYPE_SCIENTIFIC = "4";
const VARTYPE_BOOLEAN = "5";
const VARTYPE_ENUMERATED = "6";
const VARTYPE_SET = "7";

/**
* QTI decvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIDecvar
{
    public ?string $varname;
    public ?string $vartype;
    public ?string $defaultval;
    public ?string $minvalue;
    public ?string $maxvalue;
    public ?string $members;
    public ?string $cutvalue;
    public ?string $content;

    public function __construct()
    {
        $this->varname = null;
        $this->vartype = null;
        $this->defaultval = null;
        $this->minvalue = null;
        $this->maxvalue = null;
        $this->members = null;
        $this->cutvalue = null;
        $this->content = null;
    }

    public function setVarname(string $a_varname) : void
    {
        $this->varname = $a_varname;
    }

    public function getVarname() : ?string
    {
        return $this->varname;
    }

    public function setVartype(string $a_vartype) : void
    {
        switch (strtolower($a_vartype)) {
            case "integer":
            case "1":
                $this->vartype = VARTYPE_INTEGER;
                break;
            case "string":
            case "2":
                $this->vartype = VARTYPE_STRING;
                break;
            case "decimal":
            case "3":
                $this->vartype = VARTYPE_DECIMAL;
                break;
            case "scientific":
            case "4":
                $this->vartype = VARTYPE_SCIENTIFIC;
                break;
            case "boolean":
            case "5":
                $this->vartype = VARTYPE_BOOLEAN;
                break;
            case "enumerated":
            case "6":
                $this->vartype = VARTYPE_ENUMERATED;
                break;
            case "set":
            case "7":
                $this->vartype = VARTYPE_SET;
                break;
        }
    }

    public function getVartype() : ?string
    {
        return $this->vartype;
    }

    public function setDefaultval(string $a_defaultval) : void
    {
        $this->defaultval = $a_defaultval;
    }

    public function getDefaultval() : ?string
    {
        return $this->defaultval;
    }

    public function setMinvalue(string $a_minvalue) : void
    {
        $this->minvalue = $a_minvalue;
    }

    public function getMinvalue() : ?string
    {
        return $this->minvalue;
    }

    public function setMaxvalue(string $a_maxvalue) : void
    {
        $this->maxvalue = $a_maxvalue;
    }

    public function getMaxvalue() : ?string
    {
        return $this->maxvalue;
    }

    public function setMembers(string $a_members) : void
    {
        $this->members = $a_members;
    }

    public function getMembers() : ?string
    {
        return $this->members;
    }

    public function setCutvalue(string $a_cutvalue) : void
    {
        $this->cutvalue = $a_cutvalue;
    }

    public function getCutvalue() : ?string
    {
        return $this->cutvalue;
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
