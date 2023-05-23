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

namespace ILIAS\Repository\Link;

use ILIAS\UI\Component\Link\Link;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LinkAdapterGUI
{
    protected const TYPE_STD = 0;
    protected const TYPE_EMPH = 1;
    protected const TYPE_PRIM = 2;
    protected \ilToolbarGUI $toolbar;
    protected \ILIAS\DI\UIServices $ui;
    protected string $caption = "";
    protected string $href = "";
    protected bool $new_viewport = false;
    protected int $type = self::TYPE_STD;

    public function __construct(
        string $caption,
        string $href,
        bool $new_viewport = false
    ) {
        global $DIC;

        $this->caption = $caption;
        $this->href = $href;
        $this->new_viewport = $new_viewport;
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();
    }

    public function emphasised(): self
    {
        $this->type = self::TYPE_EMPH;
        return $this;
    }

    public function primary(): self
    {
        $this->type = self::TYPE_PRIM;
        return $this;
    }

    protected function getStandardLink(): \ILIAS\UI\Component\Link\Link
    {
        $link = $this->ui->factory()->link()->standard(
            $this->caption,
            $this->href
        );
        if ($this->new_viewport) {
            $link = $link->withOpenInNewViewport(true);
        }
        return $link;
    }

    protected function getLink(): \ILIAS\UI\Component\Link\Link
    {
        return $this->getStandardLink();
    }

    public function toToolbar(bool $sticky = false, \ilToolbarGUI $toolbar = null): void
    {
        $link = $this->getLink();
        if (is_null($toolbar)) {
            $toolbar = $this->toolbar;
        }
        if ($sticky) {
            $toolbar->addStickyItem($link);
        } else {
            $toolbar->addComponent($link);
        }
    }

    public function render(): string
    {
        return $this->ui->renderer()->render($this->getLink());
    }
}
