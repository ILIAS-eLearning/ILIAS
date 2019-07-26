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

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_news'))
                ->withTitle($this->dic->language()->txt("mm_news"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNews")
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(30)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return ($dic->settings()->get("block_activated_news"));
                    }
                ),
        ];
    }
}
