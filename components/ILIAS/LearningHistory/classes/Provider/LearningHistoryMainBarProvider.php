<?php

namespace ILIAS\LearningHistory;

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

use ilAchievementsGUI;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilLearningHistoryGUI;
use ilDashboardGUI;

/**
 * Class LearningHistoryMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LearningHistoryMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        global $DIC;

        $entries = [];

        $settings = $DIC->settings();

        $title = $this->dic->language()->txt("mm_learning_history");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::LHTS, $title);

        $entries[] = $this->mainmenu->link($this->if->identifier('learning_history'))
            ->withTitle($title)
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilAchievementsGUI::class,
                ilLearningHistoryGUI::class,
            ], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
            ->withPosition(10)
            ->withSymbol($icon)
            ->withAvailableCallable(
                static function () use ($settings): bool {
                    return (bool) $settings->get("enable_learning_history");
                }
            );

        return $entries;
    }
}
