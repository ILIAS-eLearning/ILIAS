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
* QTI respcondition class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRespcondition
{
    public const CONTINUE_YES = "1";
    public const CONTINUE_NO = "2";

    public ?string $continue = null;
    public ?string $title = null;
    public ?string $comment = null;
    public ?ilQTIConditionvar $conditionvar = null;
    /** @var ilQTISetvar[] */
    public array $setvar = [];
    /** @var ilQTIDisplayfeedback[] */
    public array $displayfeedback = [];

    public function setContinue(string $a_continue) : void
    {
        switch (strtolower($a_continue)) {
            case "1":
            case "yes":
                $this->continue = self::CONTINUE_YES;
                break;
            case "2":
            case "no":
                $this->continue = self::CONTINUE_NO;
                break;
        }
    }

    public function getContinue() : ?string
    {
        return $this->continue;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setComment(string $a_comment) : void
    {
        $this->comment = $a_comment;
    }

    public function getComment() : ?string
    {
        return $this->comment;
    }

    public function setConditionvar(ilQTIConditionvar $a_conditionvar) : void
    {
        $this->conditionvar = $a_conditionvar;
    }

    public function getConditionvar() : ?ilQTIConditionvar
    {
        return $this->conditionvar;
    }

    public function addSetvar(ilQTISetvar $a_setvar) : void
    {
        $this->setvar[] = $a_setvar;
    }
    
    public function addDisplayfeedback(ilQTIDisplayfeedback $a_displayfeedback) : void
    {
        $this->displayfeedback[] = $a_displayfeedback;
    }
}
