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
 * Link Button GUI
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @deprecated 9 Use KS Buttons instead
 */
class ilJsLinkButton extends ilButton
{
    protected string $target;

    public static function getInstance(): self
    {
        return new self(self::TYPE_LINK);
    }

    public function setTarget(string $a_value): void
    {
        $this->target = trim($a_value);
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    protected function renderCaption(): string
    {
        return '&nbsp;' . $this->getCaption() . '&nbsp;';
    }

    public function render(): string
    {
        $this->prepareRender();

        $attr = array();

        $attr["target"] = $this->getTarget();
        $attr["name"] = $this->getName();
        $attr["onclick"] = $this->getOnClick();

        return '<a' . $this->renderAttributes($attr) . '>' .
        $this->renderCaption() . '</a>';
    }
}
