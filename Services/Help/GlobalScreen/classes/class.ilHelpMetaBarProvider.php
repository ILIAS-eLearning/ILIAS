<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Help meta bar provider
 *
 * @author <killing@leifos.de>
 */
class ilHelpMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @return IdentificationInterface
     */
    private function getId() : IdentificationInterface
    {
        return $this->if->identifier('help');
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        global $DIC;

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $title = $DIC->language()->txt("help");

        if ($this->showHelpItem()) {
            // position should be 0, see bug #26794
            $item = $mb->topLinkItem($this->getId())
                // ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) : ILIAS\UI\Component\Component {
                //     if ($c instanceof JavaScriptBindable) {
                //         return $c->withAdditionalOnLoadCode(static function (string $id) : string {
                //             return "$('#$id').on('click', function(){
                //                 alert();
                //             })";
                //         });
                //     }
                // })
                ->withAction($this->dic->ctrl()->getLinkTargetByClass(ilDashboardGUI::class, "toggleHelp"))
                ->withSymbol($f->symbol()->glyph()->help())
                ->withTitle($title)
                ->withPosition(0);

            return [$item];
        }

        return [];
    }


    /**
     * Show help tool?
     *
     * @param
     *
     * @return
     */
    protected function showHelpItem() : bool
    {
        global $DIC;

        $user = $DIC->user();
        $settings = $DIC->settings();

        if ($user->getLanguage() != "de") {
            return false;
        }

        if ($settings->get("help_mode") == "2") {
            return false;
        }

        if ((defined("OH_REF_ID") && OH_REF_ID > 0)) {
            true;
        } else {
            $module = (int) $settings->get("help_module");
            if ($module == 0) {
                return false;
            }
        }

        return true;
    }
}
