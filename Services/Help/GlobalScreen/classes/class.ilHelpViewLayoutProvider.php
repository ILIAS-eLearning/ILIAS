<?php

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * HTML export view layout provider, hides main and meta bar
 *
 * @author <killing@leifos.de>
 */
class ilHelpViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    use \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;


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
        $this->globalScreen()->collector()->mainmenu()->collectOnce();
        foreach ($this->globalScreen()->collector()->mainmenu()->getRawItems() as $item) {
            $p = $item->getProviderIdentification();

            $tt_text = ilHelp::getMainMenuTooltip($p->getInternalIdentifier());
            $tt_text = htmlspecialchars(str_replace(array("\n", "\r"), '', $tt_text));

            $item->addComponentDecorator(static function (Component $component) use ($tt_text) : Component {
                if ($component instanceof JavaScriptBindable) {
                    return $component->withAdditionalOnLoadCode(static function ($id) use ($tt_text) : string {
                        return "il.Tooltip.add('$id', { context:'', my:'bottom center', at:'top center', text:'$tt_text' });";
                    });
                }

                return $component;
            });
        }

        ilTooltipGUI::init();

        return null;
    }
}
