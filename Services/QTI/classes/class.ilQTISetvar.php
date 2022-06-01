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
* QTI setvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTISetvar
{
    public const ACTION_SET = "1";
    public const ACTION_ADD = "2";
    public const ACTION_SUBTRACT = "3";
    public const ACTION_MULTIPLY = "4";
    public const ACTION_DIVIDE = "5";

    public ?string $varname = null;
    public ?string $action = null;
    public ?string $content = null;

    public function setVarname(string $a_varname) : void
    {
        $this->varname = $a_varname;
    }

    public function getVarname() : ?string
    {
        return $this->varname;
    }

    public function setAction(string $a_action) : void
    {
        switch (strtolower($a_action)) {
            case "set":
            case "1":
                $this->action = self::ACTION_SET;
                break;
            case "add":
            case "2":
                $this->action = self::ACTION_ADD;
                break;
            case "subtract":
            case "3":
                $this->action = self::ACTION_SUBTRACT;
                break;
            case "multiply":
            case "4":
                $this->action = self::ACTION_MULTIPLY;
                break;
            case "divide":
            case "5":
                $this->action = self::ACTION_DIVIDE;
                break;
        }
    }

    public function getAction() : ?string
    {
        return $this->action;
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
