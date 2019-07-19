<?php namespace ILIAS\User\Screen;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

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
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile")
            ->withTitle($txt("personal_profile"))
            ->withPosition(1)
            ->withSymbol($f->symbol()->glyph()->user());

        $children[] = $mb->linkItem($id('personal_settings'))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings")
            ->withTitle($txt("personal_settings"))
            ->withPosition(2)
            ->withSymbol($f->symbol()->glyph()->settings());

        $children[] = $mb->linkItem($id('logout'))
            ->withAction("logout.php?lang=" . $this->dic->user()->getCurrentLanguage())
            ->withPosition(3)
            ->withTitle($txt("logout"))
            ->withSymbol($f->symbol()->glyph()->remove());

        // "User"-Menu
        $item[] = $mb->topParentItem($id('user'))
            ->withSymbol($f->symbol()->glyph()->user())
            ->withTitle("User")
            ->withPosition(4)
            ->withVisibilityCallable(
                function () {
                    return !$this->dic->user()->isAnonymous();
                }
            )
            ->withChildren($children);

        // Login
        $item[] = $mb->topLinkItem($id('login'))
            ->withVisibilityCallable(
                function () {
                    return $this->dic->user()->isAnonymous();
                }
            )
            ->withAction("login.php?client_id=" . rawurlencode(CLIENT_ID) . "&cmd=force_login&lang=" . $this->dic->user()->getCurrentLanguage())
            ->withSymbol($f->symbol()->glyph()->user())
            ->withTitle("Login")
            ->withPosition(999);

        return $item;
    }
}
