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

define("RESPONSEVAR_EQUAL", "1");
define("RESPONSEVAR_LT", "2");
define("RESPONSEVAR_LTE", "3");
define("RESPONSEVAR_GT", "4");
define("RESPONSEVAR_GTE", "5");
define("RESPONSEVAR_SUBSET", "6");
define("RESPONSEVAR_INSIDE", "7");
define("RESPONSEVAR_SUBSTRING", "8");

define("CASE_YES", "1");
define("CASE_NO", "2");

define("SETMATCH_PARTIAL", "1");
define("SETMATCH_EXACT", "2");

define("AREATYPE_ELLIPSE", "1");
define("AREATYPE_RECTANGLE", "2");
define("AREATYPE_BOUNDED", "3");

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
    public $vartype;
    public $case;
    public $respident;
    public $index;
    public $setmatch;
    public $areatype;
    public $content;
    
    public function __construct($a_vartype)
    {
        $this->setVartype($a_vartype);
    }
    
    public function setVartype($a_vartype)
    {
        $this->vartype = $a_vartype;
    }
    
    public function getVartype()
    {
        return $this->vartype;
    }
    
    public function setCase($a_case)
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
    
    public function getCase()
    {
        return $this->case;
    }
    
    public function setRespident($a_respident)
    {
        $this->respident = $a_respident;
    }
    
    public function getRespident()
    {
        return $this->respident;
    }
    
    public function setIndex($a_index)
    {
        $this->index = $a_index;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
    
    public function setSetmatch($a_setmatch)
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
    
    public function getSetmatch()
    {
        return $this->setmatch;
    }
    
    public function setAreatype($a_areatype)
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
    
    public function getAreatype()
    {
        return $this->areatype;
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
}
