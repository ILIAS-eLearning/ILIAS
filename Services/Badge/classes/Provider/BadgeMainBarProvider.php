<?php namespace ILIAS\Badge\Provider;

use ilBadgeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class BadgeMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BadgeMainBarProvider extends AbstractStaticMainMenuProvider
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
            $this->mainmenu->link($this->if->identifier('mm_pd_badges'))
                ->withTitle($this->dic->language()->txt("mm_badges"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBadges")
                ->withPosition(40)
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        return (bool) (ilBadgeHandler::getInstance()->isActive());
                    }
                ),
        ];
    }
}
