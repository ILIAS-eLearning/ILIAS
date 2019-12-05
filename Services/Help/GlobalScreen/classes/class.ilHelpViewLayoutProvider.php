<?php

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory as MMFactory;

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
        return $this->globalScreen()
            ->layout()
            ->factory()
            ->mainbar()
            ->withModification(function (MainBar $current = null) : ?MainBar {
                global $DIC;

                // we do this "late" in the processing to have all mm items available

                $mmc = $DIC->globalScreen()->collector()->mainmenu();
                $global_screen = $DIC->globalScreen();

                $raw_items = $mmc->getRawItems();
                foreach ($raw_items as $item) {
                    if ($item instanceof MMFactory\Item\LinkList) {
                        /* since we are searching by content, this currently results in conflicts with admin entries
                        foreach ($item->getLinks() as $link) {
                            $p = $link->getProviderIdentification();
                            $global_screen->layout()->meta()->addOnloadCode(
                            'il.Tooltip.addBySelector("span:contains(\''.
                            $link->getTitle().
                            '\')", { context:"", my:"bottom center", at:"top center", text:"'.
                            $p->getInternalIdentifier().'" } );');
                            //var_dump($link->getTitle()); exit;
                        }*/
                    } else  if (
                        $item instanceof MMFactory\TopItem\TopLinkItem ||
                        $item instanceof MMFactory\TopItem\TopParentItem ||
                        $item instanceof MMFactory\Item\Link
                    ) {
                        $p = $item->getProviderIdentification();

                        $tt_text = ilHelp::getMainMenuTooltip($p->getInternalIdentifier());
                        $tt_text = htmlspecialchars(str_replace(array("\n", "\r"), "", $tt_text));
                        if ($tt_text != "") {
                            $global_screen->layout()->meta()->addOnloadCode(
                                'il.Tooltip.addBySelector("span:contains(\'' .
                                $item->getTitle() .
                                '\')", { context:"", my:"bottom center", at:"top center", text:"' .
                                $tt_text . '" } );');
                        }
                    }
                }
                ilTooltipGUI::init();
                return $current;
            })->withHighPriority();
    }


}