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

namespace ILIAS\GlobalScreen\Collector\Renderer;

use ILIAS\GlobalScreen\isGlobalScreenItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\GlobalScreen\Scope\isDecorateable;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\HasHelpTopics;
use ILIAS\UI\Help\Topic;
use ILIAS\GlobalScreen\Scope;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait DecoratorApplierTrait
{
    private function applyTopics(HasHelpTopics $component, isDecorateable $item): Component
    {
        return $component->withHelpTopics(...$item->getTopics());
    }

    public function applyComponentDecorator(Component $component, isGlobalScreenItem $item): Component
    {
        $c = $item->getComponentDecorator();
        if ($c !== null) {
            return $c($component);
        }

        return $component;
    }

    public function applySymbolDecorator(Symbol $symbol, isGlobalScreenItem $item): Symbol
    {
        $c = $item->getSymbolDecorator();
        if ($c !== null) {
            return $c($symbol);
        }

        return $symbol;
    }
}
