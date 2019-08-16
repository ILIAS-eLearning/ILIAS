<?php namespace ILIAS\GlobalScreen\Scope\Layout\Collector;

use ILIAS\GlobalScreen\Client\ClientSideProvider;
use ILIAS\GlobalScreen\Client\ClientSideProviderRegistrar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LayoutModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\NullModification;
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
class MainLayoutCollector
{

    /**
     * @var ClientSideProvider[]
     */
    private $client_side_providers;
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
     * @param array $client_side_providers
     */
    public function __construct(array $providers, array $client_side_providers)
    {
        $this->providers = $providers;
        $this->client_side_providers = $client_side_providers;
        $this->modification_handler = new ModificationHandler();
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
                throw new LogicException("There are competing Modifications for $type with the same priority");
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
        $called_contexts = $this->getContextStack();

        $final_content_modification = new NullModification();
        $final_logo_modification = new NullModification();
        $final_breadcrumbs_modification = new NullModification();
        $final_main_bar_modification = new NullModification();
        $final_meta_bar_modification = new NullModification();

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

        //
        // POC Notification ClientSide
        //

        $registrar = new ClientSideProviderRegistrar($this->getMetaContent());
        foreach ($this->client_side_providers as $client_side_provider) {
            $registrar->registerProviderClientSideCode($client_side_provider);
        }

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
