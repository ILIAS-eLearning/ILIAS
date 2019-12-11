<?php namespace ILIAS\News\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class NewsMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NewsMainBarProvider extends AbstractStaticMainMenuProvider
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
        $dic = $this->dic;

        $title = $this->dic->language()->txt("mm_news");
        //$icon = $this->dic->ui()->factory()->symbol()->icon()->standard("nwss", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/feed.svg"), $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_news'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToNews")
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(30)
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return ($dic->settings()->get("block_activated_news"));
                    }
                ),
        ];
    }
}
