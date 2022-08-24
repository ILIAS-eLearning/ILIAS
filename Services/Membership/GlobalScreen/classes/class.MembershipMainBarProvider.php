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

namespace ILIAS\Membership\GlobalScreen;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Main menu entry for derived tasks
 * @author <killing@leifos.de>
 */
class MembershipMainBarProvider extends AbstractStaticMainMenuProvider
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
        $dic = $this->dic;
        $access_helper = BasicAccessCheckClosuresSingleton::getInstance();

        $title = $this->dic->language()->txt("my_courses_groups");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(
            \ilUtil::getImagePath("icon_crgr.svg"),
            $title
        );

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_memberships'))
                                    ->withTitle($title)
                                    ->withPosition(40)
                                    ->withSymbol($icon)
                                    ->withAction($dic->ctrl()->getLinkTargetByClass(["ilMembershipOverviewGUI"], ""))
                                    ->withParent(StandardTopItemsProvider::getInstance()->getRepositoryIdentification())
                                    ->withVisibilityCallable($access_helper->isUserLoggedIn($access_helper->isUserLoggedIn($access_helper->isRepositoryReadable())));

        return $entries;
    }
}
