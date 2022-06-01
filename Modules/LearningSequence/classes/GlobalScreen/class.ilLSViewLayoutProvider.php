<?php declare(strict_types=1);

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
 
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\Data\URI;

/**
 * Class ilLSViewLayoutProvider
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    protected ?Collection $data_collection = null;

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }

    protected function isKioskModeEnabled(CalledContexts $screen_context_stack) : bool
    {
        $this->data_collection = $screen_context_stack->current()->getAdditionalData();
        return $this->data_collection->is(ilLSPlayer::GS_DATA_LS_KIOSK_MODE, true);
    }

    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        if (!$this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function (MainBar $mainbar) : ?MainBar {
                    $entries = $this->data_collection->get(ilLSPlayer::GS_DATA_LS_MAINBARCONTROLS);
                    $tools = $mainbar->getToolEntries();
                    $mainbar = $mainbar->withClearedEntries();

                    foreach ($entries as $key => $entry) {
                        $mainbar = $mainbar->withAdditionalEntry($key, $entry);
                    }
                    foreach ($tools as $key => $entry) {
                        $mainbar = $mainbar->withAdditionalToolEntry($key, $entry);
                    }
                    return $mainbar;
                }
            )
            ->withHighPriority();
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        if (!$this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->metabar()
            ->withModification(
                fn (MetaBar $metabar) : ?Metabar => $metabar->withClearedEntries()
            )
            ->withHighPriority();
    }

    public function getBreadCrumbsModification(CalledContexts $screen_context_stack) : ?BreadCrumbsModification
    {
        if (!$this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->breadcrumbs()
            ->withModification(
                fn (Breadcrumbs $current) : ?Breadcrumbs => null
            )
            ->withHighPriority();
    }

    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        if (!$this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        $html = $this->data_collection->get(ilLSPlayer::GS_DATA_LS_CONTENT);
        // TODO: Once we have more control over the content, we could just setContent
        // in ilObjLearningSequenceLearnerGUI like any other object and later strip
        // away the header here.
        return $this->globalScreen()->layout()->factory()->content()
            ->withModification(
                function (Legacy $content) use ($html) : Legacy {
                    $ui = $this->dic->ui();
                    return $ui->factory()->legacy($html);
                }
            )
            ->withHighPriority();
    }

    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        if (!$this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }

        $exit = $this->data_collection->get(\ilLSPlayer::GS_DATA_LS_METABARCONTROLS)['exit'];
        $label = $this->dic['lng']->txt('lso_player_viewmodelabel');

        $lnk = new URI($exit->getAction());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factory->page()->withModification(
            function (PagePartProvider $parts) use ($label, $lnk) : Page {
                $p = new StandardPageBuilder();
                $f = $this->dic['ui.factory'];
                $page = $p->build($parts);
                $modeinfo = $f->mainControls()->modeInfo($label, $lnk);
                return $page->withModeInfo($modeinfo);
            }
        )->withHighPriority();
    }
}
