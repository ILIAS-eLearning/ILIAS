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
* QTI response variable class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponseVar
{
    public const RESPONSEVAR_EQUAL = "1";
    public const RESPONSEVAR_LT = "2";
    public const RESPONSEVAR_LTE = "3";
    public const RESPONSEVAR_GT = "4";
    public const RESPONSEVAR_GTE = "5";
    public const RESPONSEVAR_SUBSET = "6";
    public const RESPONSEVAR_INSIDE = "7";
    public const RESPONSEVAR_SUBSTRING = "8";

    public const CASE_YES = "1";
    public const CASE_NO = "2";

    public const SETMATCH_PARTIAL = "1";
    public const SETMATCH_EXACT = "2";

    public const AREATYPE_ELLIPSE = "1";
    public const AREATYPE_RECTANGLE = "2";
    public const AREATYPE_BOUNDED = "3";

    public string $vartype = '';
    public ?string $case = null;
    public ?string $respident = null;
    public ?string $index = null;
    public ?string $setmatch = null;
    public ?string $areatype = null;
    public ?string $content = null;
    
    public function __construct(string $a_vartype)
    {
        $this->setVartype($a_vartype);
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
                $this->case = self::CASE_YES;
                break;
            case "2":
            case "no":
                $this->case = self::CASE_NO;
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
                $this->setmatch = self::SETMATCH_PARTIAL;
                break;
            case "2":
            case "exact":
                $this->setmatch = self::SETMATCH_EXACT;
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
                $this->areatype = self::AREATYPE_ELLIPSE;
                break;
            case "2":
            case "rectangle":
                $this->areatype = self::AREATYPE_RECTANGLE;
                break;
            case "3":
            case "bounded":
                $this->areatype = self::AREATYPE_BOUNDED;
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
