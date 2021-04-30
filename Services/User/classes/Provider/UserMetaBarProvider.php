<?php namespace ILIAS\User\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ilUtil;

/**
 * Class UserMetaBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserMetaBarProvider extends AbstractStaticMetaBarProvider
{

    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        $f = $this->dic->ui()->factory();
        $txt = function ($id) {
            return $this->dic->language()->txt($id);
        };
        $mb = $this->globalScreen()->metaBar();
        $id = function ($id) : IdentificationInterface {
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
            ->withVisibilityCallable(
                function () {
                    return $this->isUserLoggedIn();
                }
            )
            ->withChildren($children);

        return $item;
    }


    private function isUserLoggedIn() : bool
    {
        return (!$this->dic->user()->isAnonymous() && $this->dic->user()->getId() != 0);
    }
}
