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

define("VARTYPE_INTEGER", "1");
define("VARTYPE_STRING", "2");
define("VARTYPE_DECIMAL", "3");
define("VARTYPE_SCIENTIFIC", "4");
define("VARTYPE_BOOLEAN", "5");
define("VARTYPE_ENUMERATED", "6");
define("VARTYPE_SET", "7");

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
    public $varname;
    public $vartype;
    public $defaultval;
    public $minvalue;
    public $maxvalue;
    public $members;
    public $cutvalue;
    public $content;
    public $interpretvar;
    
    public function __construct()
    {
        $this->interpretvar = array();
    }
    
    public function setVarname($a_varname)
    {
        $this->varname = $a_varname;
    }
    
    public function getVarname()
    {
        return $this->varname;
    }

    public function setVartype($a_vartype)
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
    
    public function getVartype()
    {
        return $this->vartype;
    }

    public function setDefaultval($a_defaultval)
    {
        $this->defaultval = $a_defaultval;
    }
    
    public function getDefaultval()
    {
        return $this->defaultval;
    }

    public function setMinvalue($a_minvalue)
    {
        $this->minvalue = $a_minvalue;
    }
    
    public function getMinvalue()
    {
        return $this->minvalue;
    }

    public function setMaxvalue($a_maxvalue)
    {
        $this->maxvalue = $a_maxvalue;
    }
    
    public function getMaxvalue()
    {
        return $this->maxvalue;
    }

    public function setMembers($a_members)
    {
        $this->members = $a_members;
    }
    
    public function getMembers()
    {
        return $this->members;
    }

    public function setCutvalue($a_cutvalue)
    {
        $this->cutvalue = $a_cutvalue;
    }
    
    public function getCutvalue()
    {
        return $this->cutvalue;
    }

    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function addInterpretvar($a_interpretvar)
    {
        array_push($this->interpretvar, $a_interpretvar);
    }
}
