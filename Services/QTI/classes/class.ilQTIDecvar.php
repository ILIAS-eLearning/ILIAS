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
* QTI decvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIDecvar
{
    public const VARTYPE_INTEGER = "1";
    public const VARTYPE_STRING = "2";
    public const VARTYPE_DECIMAL = "3";
    public const VARTYPE_SCIENTIFIC = "4";
    public const VARTYPE_BOOLEAN = "5";
    public const VARTYPE_ENUMERATED = "6";
    public const VARTYPE_SET = "7";

    public ?string $varname = null;
    public ?string $vartype = null;
    public ?string $defaultval = null;
    public ?string $minvalue = null;
    public ?string $maxvalue = null;
    public ?string $members = null;
    public ?string $cutvalue = null;
    public ?string $content = null;

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
                $this->vartype = self::VARTYPE_INTEGER;
                break;
            case "string":
            case "2":
                $this->vartype = self::VARTYPE_STRING;
                break;
            case "decimal":
            case "3":
                $this->vartype = self::VARTYPE_DECIMAL;
                break;
            case "scientific":
            case "4":
                $this->vartype = self::VARTYPE_SCIENTIFIC;
                break;
            case "boolean":
            case "5":
                $this->vartype = self::VARTYPE_BOOLEAN;
                break;
            case "enumerated":
            case "6":
                $this->vartype = self::VARTYPE_ENUMERATED;
                break;
            case "set":
            case "7":
                $this->vartype = self::VARTYPE_SET;
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
