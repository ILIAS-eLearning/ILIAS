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

declare(strict_types=1);

namespace ILIAS\Badge;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;

class PresentationHeader
{
    public function __construct(
        private readonly Container $container,
        private readonly string $gui
    ) {
    }

    public function show(string $active, Component $add = null): void
    {
        $txt = [$this->container->language(), 'txt'];
        $toolbar = $this->container->toolbar();
        $view_control = $this->container->ui()->factory()->viewControl()->mode([
            $txt('tile_view') => $this->container->ctrl()->getLinkTargetByClass($this->gui, 'listBadges'),
            $txt('table_view') => $this->container->ctrl()->getLinkTargetByClass($this->gui, 'manageBadges'),
        ], 'View')->withActive($active);

        $toolbar->addStickyItem($view_control);
        if ($add) {
            $toolbar->addStickyItem($add);
        }
    }
}
