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

declare(strict_types=1);

namespace ILIAS\Help\GuidedTour\GlobalScreen;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\isDecorateable;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;

class LayoutProvider extends AbstractModificationProvider
{
    use Hasher;

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    public function getMainBarModification(
        CalledContexts $screen_context_stack
    ): ?MainBarModification {
        /** @var \ilGuidedTourGUI $gui */
        $gui = $this->dic->help()->internal()->gui()->guidedTour()->guidedTourGUI();
        $tm = $this->dic->help()->internal()->domain()->guidedTour()->tour();
        $gui->init();
        $this->globalScreen()->collector()->mainmenu()->collectOnce();

        if (!$tm->anyActive()) {
            return null;
        }

        // add id mapping of all main menu items to gui
        foreach ($this->globalScreen()->collector()->mainmenu()->getRawItems() as $item) {
            if ($item instanceof isDecorateable) {
                $name = $item->getProviderIdentification()->getInternalIdentifier();
                $item->addComponentDecorator(static function (Component $c) use ($name): ?Component {
                    return $c->withAdditionalOnLoadCode(static function (string $id) use ($name): string {
                        return "il.guidedTour.addMapping('$name', '$id');";
                    });
                });
            }
        }
        return null;
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack): ?MetaBarModification
    {
        $tm = $this->dic->help()->internal()->domain()->guidedTour()->tour();
        if (!$tm->anyActive()) {
            return null;
        }
        // add id mapping of all main menu items to gui
        $this->globalScreen()->collector()->metaBar()->collectOnce();
        foreach ($this->globalScreen()->collector()->metaBar()->getRawItems() as $item) {
            if ($item instanceof isDecorateable) {
                $name = $item->getProviderIdentification()->getInternalIdentifier();
                $item->addComponentDecorator(static function (Component $c) use ($name): ?Component {
                    return $c->withAdditionalOnLoadCode(static function (string $id) use ($name): string {
                        return "il.guidedTour.addMapping('$name', '$id');";
                    });
                });
            }
        }
        return null;
    }
}
