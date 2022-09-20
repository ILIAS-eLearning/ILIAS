<?php

declare(strict_types=1);

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

namespace ILIAS\Certificate\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class CertificateMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CertificateMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        global $DIC;

        $title = $this->dic->language()->txt("mm_certificates");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("cert", $title);

        $access_helper = BasicAccessCheckClosuresSingleton::getInstance();

        $ctrl = $DIC->ctrl();
        return [
            $this->mainmenu
                ->link($this->if->identifier('mm_cert'))
                ->withTitle($title)
                ->withAction(
                    $ctrl->getLinkTargetByClass(
                        [
                            \ilDashboardGUI::class,
                            \ilAchievementsGUI::class,
                            \ilUserCertificateGUI::class
                        ]
                    )
                )
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withVisibilityCallable($access_helper->isUserLoggedIn())
                ->withSymbol($icon)
                ->withPosition(50),
        ];
    }
}
