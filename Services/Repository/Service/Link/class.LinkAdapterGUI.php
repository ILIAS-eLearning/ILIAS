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
    protected \ilToolbarGUI $toolbar;
    protected \ILIAS\DI\UIServices $ui;
    protected string $caption = "";
    protected string $href = "";

    public function __construct(
        string $caption,
        string $href
    ) {
        global $DIC;

        $this->caption = $caption;
        $this->href = $href;
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();
    }


    protected function getStandardLink(): \ILIAS\UI\Component\Link\Link
    {
        return $this->ui->factory()->link()->standard(
            $this->caption,
            $this->href
        );
    }

    protected function getLink(): \ILIAS\UI\Component\Link\Link
    {
        return $this->getStandardLink();
    }

    public function toToolbar(\ilToolbarGUI $toolbar = null): void
    {
        $link = $this->getLink();
        if (is_null($toolbar)) {
            $toolbar = $this->toolbar;
        }
        $toolbar->addComponent($link);
    }

    public function render(): string
    {
        return $this->ui->renderer()->render($this->getLink());
    }
}
