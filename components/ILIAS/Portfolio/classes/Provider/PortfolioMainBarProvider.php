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

namespace ILIAS\Portfolio\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class PortfolioMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PortfolioMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        $dic = $this->dic;

        $title = $this->dic->language()->txt("mm_portfolio");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("prfa", $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_port'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio")
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(50)
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy(($this->dic->language()->txt('component_not_active'))))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return (bool) ($dic->settings()->get('user_portfolios'));
                    }
                ),
        ];
    }
}
