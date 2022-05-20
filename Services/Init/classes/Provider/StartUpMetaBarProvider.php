<?php

namespace ILIAS\Init\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

/**
 * Class StartUpMetaBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StartUpMetaBarProvider extends AbstractStaticMetaBarProvider
{

    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        $factory = $this->dic->ui()->factory();
        $request = $this->dic->http()->request();
        $languages = $this->dic->language()->getInstalledLanguages();

        $if = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $txt = function (string $id) : string {
            return $this->dic->language()->txt($id);
        };

        // Login-Button
        // Only visible, if not on login-page but not logged in
        $target_str = '';
        if ($ref_id = $request->getQueryParams()['ref_id']) {
            $type = \ilObject::_lookupType($ref_id, true);
            if ($type != "root") {  // see bug #30710
                $target_str = 'target=' . \ilObject::_lookupType($ref_id, true) . '_' . (int) $ref_id . '&';
            }
        } elseif ($target = $request->getQueryParams()['target']) {
            $target_str = 'target=' . $target . '&';
        }

        $login_glyph = $factory->symbol()->glyph()->login();
        $login = $this->meta_bar->topLinkItem($if('login'))
            ->withAction("login.php?" . $target_str . "client_id=" . rawurlencode(CLIENT_ID) . "&cmd=force_login&lang=" . $this->dic->user()->getCurrentLanguage())
            ->withSymbol($login_glyph)
            ->withPosition(2)
            ->withTitle($txt('log_in'))
            ->withAvailableCallable(function () {
                return !$this->isUserLoggedIn();
            })
            ->withVisibilityCallable(function () {
                return !$this->isUserOnLoginPage();
            });

        // Language-Selection
        $language_selection = $this->meta_bar->topParentItem($if('language_selection'))
            ->withSymbol($factory->symbol()->glyph()->language())
            ->withPosition(1)
            ->withAvailableCallable(function () {
                return !$this->isUserLoggedIn();
            })
            ->withVisibilityCallable(function () use ($languages) {
                return count($languages) > 1;
            })
            ->withTitle($txt('language'));

        $base = $this->getBaseURL();

        /**
         * @var $language_selection TopParentItem
         */
        foreach ($languages as $lang_key) {
            $link = $this->appendUrlParameterString($base, "lang=" . $lang_key);
            $language_name = $this->dic->language()->_lookupEntry($lang_key, "meta", "meta_l_" . $lang_key);

            $language_icon = $factory->symbol()->icon()->standard("none", $language_name)
                                     ->withAbbreviation($lang_key);

            $s = $this->meta_bar->linkItem($if($lang_key))
                ->withSymbol($language_icon)
                ->withAction($link)
                ->withTitle($language_name);

            $language_selection->appendChild($s);
        }

        return [
            $login,
            $language_selection,
        ];
    }


    private function isUserLoggedIn() : bool
    {
        return (!$this->dic->user()->isAnonymous() && $this->dic->user()->getId() != 0);
    }


    private function isUserOnLoginPage() : bool
    {
        $b = preg_match("%^.*/login.php$%", $_SERVER["SCRIPT_NAME"]) === 1;

        return $b;
    }


    private function appendUrlParameterString(string $existing_url, string $addition) : string
    {
        $url = (is_int(strpos($existing_url, "?")))
            ? $existing_url . "&" . $addition
            : $existing_url . "?" . $addition;

        $url = str_replace("?&", "?", $url);

        return $url;
    }


    private function getBaseURL() : string
    {
        $base = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);

        return preg_replace("/&*lang=[a-z]{2}&*/", "", $base);
    }
}
