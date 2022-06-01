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

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

/**
 * HTML export view layout provider, hides main and meta bar
 * @author <killing@leifos.de>
 */
class ilHelpViewLayoutProvider extends AbstractModificationProvider
{
    use Hasher;
    use ilHelpDisplayed;

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * No main bar in HTML exports
     */
    public function getMainBarModification(
        CalledContexts $screen_context_stack
    ) : ?MainBarModification {
        if (!$this->showHelpTool()) {
            return null;
        }
        $this->globalScreen()->collector()->mainmenu()->collectOnce();
        foreach ($this->globalScreen()->collector()->mainmenu()->getRawItems() as $item) {
            $p = $item->getProviderIdentification();

            $tt_text = ilHelp::getMainMenuTooltip($p->getInternalIdentifier());
            $tt_text = addslashes(str_replace(array("\n", "\r"), '', $tt_text));

            if ($tt_text !== "" && $item instanceof hasSymbol && $item->hasSymbol()) {
                $item->addSymbolDecorator(static function (Symbol $symbol) use ($tt_text) : Symbol {
                    return $symbol->withAdditionalOnLoadCode(static function ($id) use ($tt_text) : string {
                        return "il.Tooltip.addToNearest('$id', 'button,a', { context:'', my:'bottom center', at:'top center', text:'$tt_text' });";
                    });
                });
            }
        }

        ilTooltipGUI::init();

        return null;
    }
}
