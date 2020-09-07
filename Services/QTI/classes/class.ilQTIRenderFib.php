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

define("PROMPT_BOX", "1");
define("PROMPT_DASHLINE", "2");
define("PROMPT_ASTERISK", "3");
define("PROMPT_UNDERLINE", "4");

define("FIBTYPE_STRING", "1");
define("FIBTYPE_INTEGER", "2");
define("FIBTYPE_DECIMAL", "3");
define("FIBTYPE_SCIENTIFIC", "4");

/**
* QTI render fib class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderFib
{
    public $minnumber;
    public $maxnumber;
    public $response_labels;
    public $material;
    public $prompt;
    public $encoding;
    public $fibtype;
    public $rows;
    public $maxchars;
    public $columns;
    public $charset;

    public function __construct()
    {
        $this->response_labels = array();
        $this->material = array();
        $this->encoding = "UTF-8";
    }
    
    public function setPrompt($a_prompt)
    {
        switch (strtolower($a_prompt)) {
            case "1":
            case "box":
                $this->prompt = PROMPT_BOX;
                break;
            case "2":
            case "dashline":
                $this->prompt = PROMPT_DASHLINE;
                break;
            case "3":
            case "asterisk":
                $this->prompt = PROMPT_ASTERISK;
                break;
            case "4":
            case "underline":
                $this->prompt = PROMPT_UNDERLINE;
                break;
        }
    }
    
    public function getPrompt()
    {
        return $this->prompt;
    }
    
    public function setFibtype($a_fibtype)
    {
        switch (strtolower($a_fibtype)) {
            case "1":
            case "string":
                $this->fibtype = FIBTYPE_STRING;
                break;
            case "2":
            case "integer":
                $this->fibtype = FIBTYPE_INTEGER;
                break;
            case "3":
            case "decimal":
                $this->fibtype = FIBTYPE_DECIMAL;
                break;
            case "4":
            case "scientific":
                $this->fibtype = FIBTYPE_SCIENTIFIC;
                break;
        }
    }
    
    public function getFibtype()
    {
        return $this->fibtype;
    }
    
    public function setMinnumber($a_minnumber)
    {
        $this->minnumber = $a_minnumber;
    }
    
    public function getMinnumber()
    {
        return $this->minnumber;
    }
    
    public function setMaxnumber($a_maxnumber)
    {
        $this->maxnumber = $a_maxnumber;
    }
    
    public function getMaxnumber()
    {
        return $this->maxnumber;
    }
    
    public function addResponseLabel($a_response_label)
    {
        array_push($this->response_labels, $a_response_label);
    }

    public function addMaterial($a_material)
    {
        array_push($this->material, $a_material);
    }
    
    public function setEncoding($a_encoding)
    {
        $this->encoding = $a_encoding;
    }
    
    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setRows($a_rows)
    {
        $this->rows = $a_rows;
    }
    
    public function getRows()
    {
        return $this->rows;
    }

    public function setMaxchars($a_maxchars)
    {
        $this->maxchars = $a_maxchars;
    }
    
    public function getMaxchars()
    {
        return $this->maxchars;
    }

    public function setColumns($a_columns)
    {
        $this->columns = $a_columns;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }

    public function setCharset($a_charset)
    {
        $this->charset = $a_charset;
    }
    
    public function getCharset()
    {
        return $this->charset;
    }
}
