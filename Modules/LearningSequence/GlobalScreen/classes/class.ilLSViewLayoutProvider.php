<?php
//namespace ILIAS\LTI\Screen;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilLSViewLayoutProvider
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    /**
     * @var Collection | null
     */
    protected $data_collection;

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        //return $this->context_collection->kiosk();
        return $this->context_collection->main();
        return $this->context_collection->repository();
    }

    /**
     * @param CalledContexts $calledContexts
     *
     * @return bool
     */
    protected function isKioskModeEnabled(CalledContexts $screen_context_stack) : bool
    {
        $this->data_collection = $screen_context_stack->current()->getAdditionalData();
        return $this->data_collection->is(\ilLSPlayer::GS_DATA_LS_KIOSK_MODE, true);
    }

    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        if($this->isKioskModeEnabled($screen_context_stack)) {
            return $this->globalScreen()->layout()->factory()->mainbar()
                ->withModification(
                    function(MainBar $mainbar) : ?MainBar {
                        $mainbar = $mainbar->withClearedEntries();
                        foreach ($this->data_collection->get(\ilLSPlayer::GS_DATA_LS_MAINBARCONTROLS) as $key => $entry) {
                            $mainbar = $mainbar->withAdditionalEntry($key, $entry);
                        }
                        return $mainbar;
                    }
                )->withHighPriority();
        }
        return null;
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {

        if($this->isKioskModeEnabled($screen_context_stack)) {
            return $this->globalScreen()->layout()->factory()->metabar()
                ->withModification(
                    function(MetaBar $metabar) {
                        $metabar = $metabar->withClearedEntries();
                        foreach ($this->data_collection->get(\ilLSPlayer::GS_DATA_LS_METABARCONTROLS) as $key => $entry) {
                            $metabar = $metabar->withAdditionalEntry($key, $entry);
                        }
                        return $metabar;
                    }
                )
                ->withHighPriority();
        }
        return null;
    }

    public function getFooterModification(CalledContexts $screen_context_stack) : ?FooterModification
    {
        if($this->isKioskModeEnabled($screen_context_stack)) {
            return $this->globalScreen()->layout()->factory()->footer()
                ->withModification(
                    function(Footer $current) {
                        return null;
                    }
                )->withHighPriority();
        }
        return null;
    }

    public function getBreadCrumbsModification(CalledContexts $screen_context_stack) : ?BreadCrumbsModification
    {
        if($this->isKioskModeEnabled($screen_context_stack)) {
            return $this->globalScreen()->layout()->factory()->breadcrumbs()
                ->withModification(
                    function(Breadcrumbs $current) {
                        return null;
                    }
                )
                ->withHighPriority();
        }
        return null;
    }

    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        if($this->isKioskModeEnabled($screen_context_stack)) {
            $html = $this->data_collection->get(\ilLSPlayer::GS_DATA_LS_CONTENT);
            return $this->globalScreen()->layout()->factory()->content()
                ->withModification(
                    function (Legacy $content) use ($html): Legacy {
                        $ui = $this->dic->ui();
                        return $ui->factory()->legacy($html);
                    }
                )
                ->withHighPriority();
        }
        return null;
    }
}
