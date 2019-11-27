<?php
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
        return $this->context_collection->main();
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
        if(! $this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function(MainBar $mainbar) : ?MainBar {
                    $mainbar = $mainbar->withClearedEntries();
                    foreach ($this->data_collection->get(\ilLSPlayer::GS_DATA_LS_MAINBARCONTROLS) as $key => $entry) {
                        $mainbar = $mainbar->withAdditionalEntry($key, $entry);
                    }
                    return $mainbar;
                }
            )
			->withHighPriority();
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        if(! $this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->metabar()
            ->withModification(
                function(MetaBar $metabar) : ?Metabar{
                    $metabar = $metabar->withClearedEntries();
                    foreach ($this->data_collection->get(\ilLSPlayer::GS_DATA_LS_METABARCONTROLS) as $key => $entry) {
                        $metabar = $metabar->withAdditionalEntry($key, $entry);
                    }
                    return $metabar;
                }
            )
            ->withHighPriority();
    }

    public function getBreadCrumbsModification(CalledContexts $screen_context_stack) : ?BreadCrumbsModification
    {
        if(! $this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->breadcrumbs()
            ->withModification(
                function(Breadcrumbs $current) : ?Breadcrumbs {
                    return null;
                }
            )
            ->withHighPriority();
    }

    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        if(! $this->isKioskModeEnabled($screen_context_stack)) {
            return null;
        }
        $html = $this->data_collection->get(\ilLSPlayer::GS_DATA_LS_CONTENT);
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
}
