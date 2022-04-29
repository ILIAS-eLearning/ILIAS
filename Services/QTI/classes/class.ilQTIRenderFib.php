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
    public ?string $minnumber;
    public ?string $maxnumber;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels;
    /** @var ilQTIMaterial[] */
    public array $material;
    public ?string $prompt;
    public string $encoding;
    public ?string $fibtype;
    public ?string $rows;
    public ?string $maxchars;
    public ?string $columns;
    public ?string $charset;

    public function __construct()
    {
        $this->minnumber = null;
        $this->maxnumber = null;
        $this->response_labels = [];
        $this->material = [];
        $this->prompt = null;
        $this->encoding = "UTF-8";
        $this->fibtype = null;
        $this->rows = null;
        $this->maxchars = null;
        $this->columns = null;
        $this->charset = null;
    }

    public function setPrompt(string $a_prompt) : void
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

    public function getPrompt() : ?string
    {
        return $this->prompt;
    }

    public function setFibtype(string $a_fibtype) : void
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

    public function getFibtype() : ?string
    {
        return $this->fibtype;
    }

    public function setMinnumber(string $a_minnumber) : void
    {
        $this->minnumber = $a_minnumber;
    }

    public function getMinnumber() : ?string
    {
        return $this->minnumber;
    }

    public function setMaxnumber(string $a_maxnumber) : void
    {
        $this->maxnumber = $a_maxnumber;
    }

    public function getMaxnumber() : ?string
    {
        return $this->maxnumber;
    }
    
    public function addResponseLabel(ilQTIResponseLabel $a_response_label) : void
    {
        $this->response_labels[] = $a_response_label;
    }

    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $this->material[] = $a_material;
    }

    public function setEncoding(string $a_encoding) : void
    {
        $this->encoding = $a_encoding;
    }

    public function getEncoding() : string
    {
        return $this->encoding;
    }

    public function setRows(string $a_rows) : void
    {
        $this->rows = $a_rows;
    }

    public function getRows() : ?string
    {
        return $this->rows;
    }

    public function setMaxchars(string $a_maxchars) : void
    {
        $this->maxchars = $a_maxchars;
    }

    public function getMaxchars() : ?string
    {
        return $this->maxchars;
    }

    public function setColumns(string $a_columns) : void
    {
        $this->columns = $a_columns;
    }

    public function getColumns() : ?string
    {
        return $this->columns;
    }

    public function setCharset(string $a_charset) : void
    {
        $this->charset = $a_charset;
    }

    public function getCharset() : ?string
    {
        return $this->charset;
    }
}
