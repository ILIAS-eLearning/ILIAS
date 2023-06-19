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
 *
 * @author <killing@leifos.de>
 */
class ilHTMLExportViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    const HTML_EXPORT_RENDERING = 'html_export_rendering';

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * No meta bar in HTML exports
     */
    public function getMetaBarModification(CalledContexts $called_contexts) : ?MetaBarModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                ->layout()
                ->factory()
                ->metabar()
                ->withModification(function (?MetaBar $current = null) : ?MetaBar {
                    return null;
                })->withHighPriority();
        }
        return null;
    }

    /**
     * No main bar in HTML exports
     */
    public function getMainBarModification(CalledContexts $called_contexts) : ?MainBarModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                ->layout()
                ->factory()
                ->mainbar()
                ->withModification(function (?MainBar $current = null) : ?MainBar {
                    return null;
                })->withHighPriority();
        } else {
            return null;
        }
    }

    /**
     * No breadcrumbs in HTML exports
     */
    public function getBreadCrumbsModification(CalledContexts $called_contexts) : ?BreadCrumbsModification
    {
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::HTML_EXPORT_RENDERING, true)) {
            return $this->globalScreen()
                ->layout()
                ->factory()
                ->breadcrumbs()
                ->withModification(function (?Breadcrumbs $current = null) : ?Breadcrumbs {
                    return null;
                })->withHighPriority();
        } else {
            return null;
        }
    }
}
