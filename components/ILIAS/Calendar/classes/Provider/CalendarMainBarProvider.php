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

namespace ILIAS\Calendar\Provider;

use ilCalendarSettings;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class CalendarMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CalendarMainBarProvider extends AbstractStaticMainMenuProvider
{
    /**
     * @inheritDoc
     */
    public function getStaticTopItems(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems(): array
    {
        $title = $this->dic->language()->txt("mm_calendar");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("cals", $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_cal'))
                           ->withTitle($title)
                           ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToCalendar")
                           ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                           ->withPosition(30)
                           ->withSymbol($icon)
                           ->withNonAvailableReason($this->dic->ui()->factory()->legacy()->content("{$this->dic->language()->txt('component_not_active')}"))
                           ->withAvailableCallable(
                               function () {
                                   $settings = ilCalendarSettings::_getInstance();

                                   return $settings->isEnabled();
                               }
                           ),
        ];
    }
}
