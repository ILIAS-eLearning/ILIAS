<?php

declare(strict_types=1);

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

namespace ILIAS\Repository\Button;

use ILIAS\UI\Component\Button\Standard;
use ILIAS\UI\Component\Button\Button;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ButtonAdapterGUI
{
    protected const TYPE_STD = 0;
    protected const TYPE_SUBMIT = 1;
    protected const TYPE_STD_PRIMARY = 2;
    protected string $on_click = "";
    protected int $type;

    protected Button $button;
    protected \ilToolbarGUI $toolbar;
    protected \ILIAS\DI\UIServices $ui;
    protected string $caption = "";
    protected string $cmd = "";
    protected bool $disabled = false;

    public function __construct(
        string $caption,
        string $cmd
    ) {
        global $DIC;

        $this->caption = $caption;
        $this->cmd = $cmd;
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();
        $this->type = self::TYPE_STD;
        $this->on_click = "";
    }

    public function submit(): self
    {
        $this->type = self::TYPE_SUBMIT;
        return $this;
    }

    public function primary(): self
    {
        $this->type = self::TYPE_STD_PRIMARY;
        return $this;
    }

    public function onClick(string $on_click): self
    {
        $this->on_click = $on_click;
        return $this;
    }

    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    protected function getSubmitButton(): \ILIAS\UI\Component\Button\Button
    {
        $cmd = $this->cmd;
        return $this->ui->factory()->button()->standard(
            $this->caption,
            ""
        )->withOnLoadCode(function ($id) use ($cmd) {
            $code = <<<EOT
(function() {
    const el = document.getElementById('$id');
    el.name = "cmd[$cmd]";
    el.type = "submit";
}());
EOT;
            return $code;
        });
    }

    protected function getStandardButton(): \ILIAS\UI\Component\Button\Button
    {
        return $this->ui->factory()->button()->standard(
            $this->caption,
            $this->cmd
        );
    }

    protected function getStdPrimaryButton(): \ILIAS\UI\Component\Button\Button
    {
        return $this->ui->factory()->button()->primary(
            $this->caption,
            $this->cmd
        );
    }

    protected function getButton(): \ILIAS\UI\Component\Button\Button
    {
        switch ($this->type) {
            case self::TYPE_SUBMIT:
                $button = $this->getSubmitButton();
                break;

            case self::TYPE_STD_PRIMARY:
                $button = $this->getStdPrimaryButton();
                break;

            default:
                $button = $this->getStandardButton();
                break;
        }
        if ($this->on_click !== "") {
            $click = $this->on_click;
            $button = $button->withOnLoadCode(function ($id) use ($click) {
                $code = <<<EOT
(function() {
    const el = document.getElementById('$id').addEventListener('click', () => { $click });
}());
EOT;
                return $code;
            });
        }

        if ($this->disabled) {
            $button = $button->withUnavailableAction();
        }

        return $button;
    }

    public function toToolbar(bool $sticky = false, \ilToolbarGUI $toolbar = null): void
    {
        $button = $this->getButton();
        if (is_null($toolbar)) {
            $toolbar = $this->toolbar;
        }
        if ($sticky) {
            $toolbar->addStickyItem($button);
        } else {
            $toolbar->addComponent($button);
        }
    }

    public function render(): string
    {
        return $this->ui->renderer()->render($this->getButton());
    }
}
