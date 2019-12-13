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
        $title = $this->dic->language()->txt("mm_badges");
        //$icon = $this->dic->ui()->factory()->symbol()->icon()->standard("bdga", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/badge.svg"), $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_badges'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToBadges")
                ->withPosition(40)
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        return (bool) (ilBadgeHandler::getInstance()->isActive());
                    }
                ),
        ];
    }
}
