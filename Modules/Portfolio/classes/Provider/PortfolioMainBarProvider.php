<?php namespace ILIAS\Portfolio\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class PortfolioMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PortfolioMainBarProvider extends AbstractStaticMainMenuProvider
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

        $title = $this->dic->language()->txt("mm_portfolio");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("prfa", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/book-open.svg"), $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_port'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio")
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(50)
	            ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return (bool) ($dic->settings()->get('user_portfolios'));
                    }
                ),
        ];
    }
}
