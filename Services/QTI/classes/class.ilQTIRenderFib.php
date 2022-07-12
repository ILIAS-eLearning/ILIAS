<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    public const PROMPT_BOX = "1";
    public const PROMPT_DASHLINE = "2";
    public const PROMPT_ASTERISK = "3";
    public const PROMPT_UNDERLINE = "4";

    public const FIBTYPE_STRING = "1";
    public const FIBTYPE_INTEGER = "2";
    public const FIBTYPE_DECIMAL = "3";
    public const FIBTYPE_SCIENTIFIC = "4";

    public ?string $minnumber = null;
    public ?string $maxnumber = null;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels = [];
    /** @var ilQTIMaterial[] */
    public array $material = [];
    public ?string $prompt = null;
    public string $encoding = "UTF-8";
    public ?string $fibtype = null;
    public ?string $rows = null;
    public ?string $maxchars = null;
    public ?string $columns = null;
    public ?string $charset = null;

    public function setPrompt(string $a_prompt) : void
    {
        switch (strtolower($a_prompt)) {
            case "1":
            case "box":
                $this->prompt = self::PROMPT_BOX;
                break;
            case "2":
            case "dashline":
                $this->prompt = self::PROMPT_DASHLINE;
                break;
            case "3":
            case "asterisk":
                $this->prompt = self::PROMPT_ASTERISK;
                break;
            case "4":
            case "underline":
                $this->prompt = self::PROMPT_UNDERLINE;
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
                $this->fibtype = self::FIBTYPE_STRING;
                break;
            case "2":
            case "integer":
                $this->fibtype = self::FIBTYPE_INTEGER;
                break;
            case "3":
            case "decimal":
                $this->fibtype = self::FIBTYPE_DECIMAL;
                break;
            case "4":
            case "scientific":
                $this->fibtype = self::FIBTYPE_SCIENTIFIC;
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
