<?php

declare(strict_types=1);

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

/**
 * HTML export view layout provider, hides main and meta bar
 * @author <killing@leifos.de>
 */
class ilHTMLExportViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    public const HTML_EXPORT_RENDERING = 'html_export_rendering';

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * @inheritDoc
     * No meta bar in HTML exports
     */
    public function getMetaBarModification(CalledContexts $called_contexts): ?MetaBarModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                        ->layout()
                        ->factory()
                        ->metabar()
                        ->withModification(function (MetaBar $current = null): ?MetaBar {
                            return null;
                        })->withHighPriority();
        }
        return null;
    }

    /**
     * @inheritDoc
     * No main bar in HTML exports
     */
    public function getMainBarModification(CalledContexts $called_contexts): ?MainBarModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                        ->layout()
                        ->factory()
                        ->mainbar()
                        ->withModification(function (MainBar $current = null): ?MainBar {
                            return null;
                        })->withHighPriority();
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     * No breadcrumbs in HTML exports
     */
    public function getBreadCrumbsModification(CalledContexts $called_contexts): ?BreadCrumbsModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                        ->layout()
                        ->factory()
                        ->breadcrumbs()
                        ->withModification(function (Breadcrumbs $current = null): ?Breadcrumbs {
                            return null;
                        })->withHighPriority();
        } else {
            return null;
        }
    }
}
