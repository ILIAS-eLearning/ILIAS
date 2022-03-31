<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Implementation\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Implementation\Component\Link\Bulky as BulkyLink;

/**
 * Help meta bar provider
 * @author <killing@leifos.de>
 */
class ilHelpMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{
    use ilHelpDisplayed;

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

        if ($this->showHelpTool()) {
            // position should be 0, see bug #26794
            $item = $mb->topLinkItem($this->getId())
                       ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) : ILIAS\UI\Component\Component {
                           if ($c instanceof BulkyButton || $c instanceof BulkyLink) {
                               return $c->withAdditionalOnLoadCode(static function (string $id) : string {
                                   return "$('#$id').on('click', function() {
                                    console.log('trigger help slate');
                                    $('body').trigger('il-help-toggle-slate');
                                    return false;
                                })";
                               });
                           }
                           return $c;
                       })
//                       ->withAction($this->dic->ctrl()->getLinkTargetByClass(ilDashboardGUI::class, "toggleHelp"))
                       ->withSymbol($f->symbol()->glyph()->help())
                       ->withTitle($title)
                       ->withPosition(0);

            return [$item];
        }

        return [];
    }
}
