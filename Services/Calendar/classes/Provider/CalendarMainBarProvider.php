<?php namespace ILIAS\Calendar\Provider;

use ilCalendarSettings;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class CalendarMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CalendarMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_cal'))
                ->withTitle($this->dic->language()->txt("mm_calendar"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToCalendar")
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(30)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        $settings = ilCalendarSettings::_getInstance();

                        return (bool) ($settings->isEnabled());
                    }
                ),
        ];
    }
}
