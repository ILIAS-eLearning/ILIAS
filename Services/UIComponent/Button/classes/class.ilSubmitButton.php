<?php

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
 *********************************************************************/

/**
 * Submit Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @deprecated 10 Use KS Buttons instead
 */
class ilSubmitButton extends ilButtonBase
{
    protected string $cmd = "";

    public static function getInstance(): self
    {
        return new self(self::TYPE_SUBMIT);
    }


    //
    // properties
    //

    /**
     * Set submit command
     */
    public function setCommand(string $a_value): void
    {
        $this->cmd = trim($a_value);
    }

    public function getCommand(): string
    {
        return $this->cmd;
    }


    //
    // render
    //

    public function render(): string
    {
        $this->prepareRender();

        $attr = array();
        $attr["type"] = "submit";
        $attr["name"] = "cmd[" . $this->getCommand() . "]";
        $attr["value"] = $this->getCaption();

        return '<input' . $this->renderAttributes($attr) . ' />';
    }
}
