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
    /** @var string|null */
    public $varname;

    /** @var string|null */
    public $vartype;

    /** @var string|null */
    public $defaultval;

    /** @var string|null */
    public $minvalue;

    /** @var string|null */
    public $maxvalue;

    /** @var string|null */
    public $members;

    /** @var string|null */
    public $cutvalue;

    /** @var string|null */
    public $content;

    /** @var array */
    public $interpretvar;
    
    public function __construct()
    {
        $this->interpretvar = array();
    }

    /**
     * @param string $a_varname
     */
    public function setVarname($a_varname) : void
    {
        $this->varname = $a_varname;
    }

    /**
     * @return string|null
     */
    public function getVarname()
    {
        return $this->varname;
    }

    /**
     * @param string $a_vartype
     */
    public function setVartype($a_vartype) : void
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

    /**
     * @return string|null
     */
    public function getVartype()
    {
        return $this->vartype;
    }

    /**
     * @param string $a_defaultval
     */
    public function setDefaultval($a_defaultval) : void
    {
        $this->defaultval = $a_defaultval;
    }

    /**
     * @return string|null
     */
    public function getDefaultval()
    {
        return $this->defaultval;
    }

    /**
     * @param string $a_minvalue
     */
    public function setMinvalue($a_minvalue) : void
    {
        $this->minvalue = $a_minvalue;
    }

    /**
     * @return string|null
     */
    public function getMinvalue()
    {
        return $this->minvalue;
    }

    /**
     * @param string a_maxvalue
     */
    public function setMaxvalue($a_maxvalue) : void
    {
        $this->maxvalue = $a_maxvalue;
    }

    /**
     * @return string|null
     */
    public function getMaxvalue()
    {
        return $this->maxvalue;
    }

    /**
     * @param string $a_members
     */
    public function setMembers($a_members) : void
    {
        $this->members = $a_members;
    }

    /**
     * @return string|null
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param string $a_cutvalue
     */
    public function setCutvalue($a_cutvalue) : void
    {
        $this->cutvalue = $a_cutvalue;
    }

    /**
     * @return string|null
     */
    public function getCutvalue()
    {
        return $this->cutvalue;
    }

    /**
     * @param string $a_content
     */
    public function setContent($a_content) : void
    {
        $this->content = $a_content;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Never used.
     */
    public function addInterpretvar($a_interpretvar) : void
    {
        $this->interpretvar[] = $a_interpretvar;
    }
}
