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

namespace ILIAS\User\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ilUtil;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserMetaBarProvider extends AbstractStaticMetaBarProvider
{
    /**
     * @inheritcoc
     */
    public function getMetaBarItems(): array
    {
        $access_checks = BasicAccessCheckClosuresSingleton::getInstance();
        $f = $this->dic->ui()->factory();
        $txt = function (string $id): string {
            return $this->dic->language()->txt($id);
        };
        $mb = $this->globalScreen()->metaBar();
        $id = function (string $id): IdentificationInterface {
            return $this->if->identifier($id);
        };

        $children = array();
        $children[] = $mb->linkItem($id('personal_profile'))
            ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToProfile")
            ->withTitle($txt("personal_profile"))
            ->withPosition(1)
            ->withSymbol($f->symbol()->icon()->custom(ilUtil::getImagePath("icon_profile.svg"), $txt("personal_profile")));

        $children[] = $mb->linkItem($id('personal_settings'))
            ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToSettings")
            ->withTitle($txt("personal_settings"))
            ->withPosition(2)
            ->withSymbol($f->symbol()->icon()->custom(ilUtil::getImagePath("icon_personal_settings.svg"), $txt("personal_settings")));

        $children[] = $mb->linkItem($id('logout'))
            ->withAction("logout.php?lang=" . $this->dic->user()->getCurrentLanguage())
            ->withPosition(3)
            ->withTitle($txt("logout"))
            ->withSymbol($f->symbol()->glyph()->logout());

        // "User"-Menu
        $item[] = $mb->topParentItem($id('user'))
            ->withSymbol($this->dic->user()->getAvatar())
            ->withTitle($this->dic->language()->txt("info_view_of_user"))
            ->withPosition(4)
            ->withVisibilityCallable($access_checks->isUserLoggedIn())
            ->withChildren($children);

        return $item;
    }
}
