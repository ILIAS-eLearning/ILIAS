<?php

namespace ILIAS\Badge\Provider;

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

use ilBadgeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BadgeMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        $title = $this->dic->language()->txt("mm_badges");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("bdga", $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_badges'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToBadges")
                ->withPosition(40)
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active')))
                ->withAvailableCallable(
                    function () {
                        return ilBadgeHandler::getInstance()->isActive();
                    }
                ),
        ];
    }
}
