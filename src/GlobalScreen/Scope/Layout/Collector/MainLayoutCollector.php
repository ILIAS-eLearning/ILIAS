<?php namespace ILIAS\GlobalScreen\Scope\Layout\Collector;

use ILIAS\GlobalScreen\Client\Client;
use ILIAS\GlobalScreen\Client\ClientSettings;
use ILIAS\GlobalScreen\Client\ItemState;
use ILIAS\GlobalScreen\Client\ModeToggle;
use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LayoutModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\NullModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ShortTitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\TitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ViewTitleModification;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\ModificationHandler;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Layout\Page\Page;
use LogicException;

/**
 * Class MainLayoutCollector
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainLayoutCollector extends AbstractBaseCollector
{

    /**
     * @var ModificationHandler
     */
    private $modification_handler;
    /**
     * @var ModificationProvider[]
     */
    private $providers = [];


    /**
     * MainLayoutCollector constructor.
     *
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
        $this->modification_handler = new ModificationHandler();
    }


    public function collectStructure() : void
    {
        // Client
        $settings = new ClientSettings();
        $settings->setHashing(true);
        $settings->setLogging(false);

        $client = new Client($settings);
        $client->init($this->getMetaContent());

        $called_contexts = $this->getContextStack();

        $final_content_modification = new NullModification();
        $final_logo_modification = new NullModification();
        $final_breadcrumbs_modification = new NullModification();
        $final_main_bar_modification = new NullModification();
        $final_meta_bar_modification = new NullModification();
        $final_page_modification = new NullModification();
        $final_footer_modification = new NullModification();
        $final_title_modification = new NullModification();
        $final_short_title_modification = new NullModification();
        $final_view_title_modification = new NullModification();

        foreach ($this->providers as $provider) {
            $context_collection = $provider->isInterestedInContexts();
            if (!$context_collection->hasMatch($called_contexts)) {
                continue;
            }

            // CONTENT
            $content_modification = $provider->getContentModification($called_contexts);
            $this->replaceModification($final_content_modification, $content_modification, ContentModification::class);
            // LOGO
            $logo_modification = $provider->getLogoModification($called_contexts);
            $this->replaceModification($final_logo_modification, $logo_modification, LogoModification::class);
            // BREADCRUMBS
            $breadcrumbs_modification = $provider->getBreadCrumbsModification($called_contexts);
            $this->replaceModification($final_breadcrumbs_modification, $breadcrumbs_modification, BreadCrumbsModification::class);
            // MAINBAR
            $main_bar_modification = $provider->getMainBarModification($called_contexts);
            $this->replaceModification($final_main_bar_modification, $main_bar_modification, MainBarModification::class);
            // METABAR
            $meta_bar_modification = $provider->getMetaBarModification($called_contexts);
            $this->replaceModification($final_meta_bar_modification, $meta_bar_modification, MetaBarModification::class);
            // FOOTER
            $footer_modification = $provider->getFooterModification($called_contexts);
            $this->replaceModification($final_footer_modification, $footer_modification, FooterModification::class);
            // PAGE
            $page_modification = $provider->getPageBuilderDecorator($called_contexts);
            $this->replaceModification($final_page_modification, $page_modification, PageBuilderModification::class);
            // Pagetitle
            $title_modification = $provider->getTitleModification($called_contexts);
            $this->replaceModification($final_title_modification, $title_modification, TitleModification::class);

            $short_title_modification = $provider->getShortTitleModification($called_contexts);
            $this->replaceModification($final_short_title_modification, $short_title_modification, ShortTitleModification::class);

            $view_title_modification = $provider->getViewTitleModification($called_contexts);
            $this->replaceModification($final_view_title_modification, $view_title_modification, ViewTitleModification::class);
        }

        if ($final_content_modification->hasValidModification()) {
            $this->modification_handler->modifyContentWithClosure($final_content_modification->getModification());
        }
        if ($final_logo_modification->hasValidModification()) {
            $this->modification_handler->modifyLogoWithClosure($final_logo_modification->getModification());
        }
        if ($final_breadcrumbs_modification->hasValidModification()) {
            $this->modification_handler->modifyBreadCrumbsWithClosure($final_breadcrumbs_modification->getModification());
        }
        if ($final_main_bar_modification->hasValidModification()) {
            $this->modification_handler->modifyMainBarWithClosure($final_main_bar_modification->getModification());
        }
        if ($final_meta_bar_modification->hasValidModification()) {
            $this->modification_handler->modifyMetaBarWithClosure($final_meta_bar_modification->getModification());
        }
        if ($final_footer_modification->hasValidModification()) {
            $this->modification_handler->modifyFooterWithClosure($final_footer_modification->getModification());
        }
        if ($final_page_modification->hasValidModification()) {
            $this->modification_handler->modifyPageBuilderWithClosure($final_page_modification->getModification());
        }
        if ($final_title_modification->hasValidModification()) {
            $this->modification_handler->modifyTitleWithClosure($final_title_modification->getModification());
        }
        if ($final_short_title_modification->hasValidModification()) {
            $this->modification_handler->modifyShortTitleWithClosure($final_short_title_modification->getModification());
        }
        if ($final_view_title_modification->hasValidModification()) {
            $this->modification_handler->modifyViewTitleWithClosure($final_view_title_modification->getModification());
        }
    }


    public function filterItemsByVisibilty(bool $skip_async = false) : void
    {
        // TODO: Implement filterItemsByVisibilty() method.
    }


    public function prepareItemsForUIRepresentation() : void
    {
        // TODO: Implement prepareItemsForUIRepresentation() method.
    }

    public function cleanupItemsForUIRepresentation() : void
    {
        // TODO: Implement cleanupItemsForUIRepresentation() method.
    }

    public function sortItemsForUIRepresentation() : void
    {
        // TODO: Implement sortItemsForUIRepresentation() method.
    }


    /**
     * @inheritDoc
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        // TODO: Implement getItemsForUIRepresentation() method.
    }


    /**
     * @inheritDoc
     */
    public function hasItems() : bool
    {
        return true;
    }


    /**
     * @param LayoutModification      $current_modification
     * @param LayoutModification|null $candicate
     * @param string                  $type
     */
    private function replaceModification(LayoutModification &$current_modification, ?LayoutModification $candicate, string $type)
    {
        if (is_a($candicate, $type) && $candicate->hasValidModification()) {
            if ($candicate->getPriority() === $current_modification->getPriority()) {
                throw new LogicException("There are competing Modifications for $type with the same priority ({$candicate->getPriority()})");
            } elseif ($candicate->getPriority() > $current_modification->getPriority()) {
                $current_modification = $candicate;
            }
        }
    }


    /**
     * @return Page
     */
    public function getFinalPage() : Page
    {
        $this->collectOnce();

        return $this->modification_handler->getPageWithPagePartProviders();
    }


    /**
     * @return CalledContexts
     */
    private function getContextStack() : CalledContexts
    {
        global $DIC;
        $called_contexts = $DIC->globalScreen()->tool()->context()->stack();

        return $called_contexts;
    }


    /**
     * @return MetaContent
     */
    private function getMetaContent() : MetaContent
    {
        global $DIC;

        return $DIC->globalScreen()->layout()->meta();
    }
}
