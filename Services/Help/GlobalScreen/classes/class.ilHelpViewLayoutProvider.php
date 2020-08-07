<?php

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * HTML export view layout provider, hides main and meta bar
 * @author <killing@leifos.de>
 */
class ilHelpViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    use \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
    use ilHelpDisplayed;

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * No main bar in HTML exports
     */
    public function getMainBarModification(CalledContexts $called_contexts) : ?MainBarModification
    {
        if (!$this->showHelpTool()) {
            return null;
        }
        $this->globalScreen()->collector()->mainmenu()->collectOnce();
        foreach ($this->globalScreen()->collector()->mainmenu()->getRawItems() as $item) {
            $p = $item->getProviderIdentification();

            $tt_text = ilHelp::getMainMenuTooltip($p->getInternalIdentifier());
            $tt_text = addslashes(str_replace(array("\n", "\r"), '', $tt_text));

            if ($tt_text != "") {
                if ($item instanceof hasSymbol && $item->hasSymbol()) {
                    $item->addSymbolDecorator(static function (Symbol $symbol) use ($tt_text) : Symbol {
                        if ($symbol instanceof JavaScriptBindable) {
                            return $symbol->withAdditionalOnLoadCode(static function ($id) use ($tt_text) : string {
                                return "il.Tooltip.addToNearest('$id', 'button,a', { context:'', my:'bottom center', at:'top center', text:'$tt_text' });";
                            });
                        }
                        return $symbol;
                    });
                }
            }
        }

        ilTooltipGUI::init();

        return null;
    }
}
