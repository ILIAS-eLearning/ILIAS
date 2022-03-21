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

const PROMPT_BOX = "1";
const PROMPT_DASHLINE = "2";
const PROMPT_ASTERISK = "3";
const PROMPT_UNDERLINE = "4";

const FIBTYPE_STRING = "1";
const FIBTYPE_INTEGER = "2";
const FIBTYPE_DECIMAL = "3";
const FIBTYPE_SCIENTIFIC = "4";

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
    /** @var string|null */
    public $minnumber;

    /** @var string|null */
    public $maxnumber;

    /** @var ilQTIResponseLabel[] */
    public $response_labels;

    /** @var ilQTIMaterial[] */
    public $material;

    /** @var string|null */
    public $prompt;

    /** @var string */
    public $encoding;

    /** @var string|null */
    public $fibtype;

    /** @var string|null */
    public $rows;

    /** @var string|null */
    public $maxchars;

    /** @var string|null */
    public $columns;

    /** @var string|null */
    public $charset;

    public function __construct()
    {
        $this->response_labels = array();
        $this->material = array();
        $this->encoding = "UTF-8";
    }

    /**
     * @param string $a_prompt
     */
    public function setPrompt($a_prompt) : void
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

    /**
     * @return string|null
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * @param string $a_fibtype
     */
    public function setFibtype($a_fibtype) : void
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

    /**
     * @return string|null
     */
    public function getFibtype()
    {
        return $this->fibtype;
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

    /**
     * @param string $a_encoding
     */
    public function setEncoding($a_encoding) : void
    {
        $this->encoding = $a_encoding;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $a_rows
     */
    public function setRows($a_rows) : void
    {
        $this->rows = $a_rows;
    }

    /**
     * @return string|null
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param string $a_maxchars
     */
    public function setMaxchars($a_maxchars) : void
    {
        $this->maxchars = $a_maxchars;
    }

    /**
     * @return string|null
     */
    public function getMaxchars()
    {
        return $this->maxchars;
    }

    /**
     * @param string $a_columns
     */
    public function setColumns($a_columns) : void
    {
        $this->columns = $a_columns;
    }

    /**
     * @return string|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $a_charset
     */
    public function setCharset($a_charset) : void
    {
        $this->charset = $a_charset;
    }

    /**
     * @return string|null
     */
    public function getCharset()
    {
        return $this->charset;
    }
}
