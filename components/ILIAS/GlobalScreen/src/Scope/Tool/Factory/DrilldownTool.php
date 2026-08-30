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

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\UI\Component\Menu\Drilldown;
use ILIAS\UI\Component\Component;

class DrilldownTool extends Tool implements isTopItem, hasContent, supportsTerminating
{
    public function withContent(Component $ui_component): hasContent
    {
        if (! $ui_component instanceof Drilldown) {
            throw new \InvalidArgumentException('content must be ' . Drilldown::class);
        }
        return parent::withContent($ui_component);
    }
}
