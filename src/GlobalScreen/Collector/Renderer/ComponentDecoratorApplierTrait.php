<?php

namespace ILIAS\GlobalScreen\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\isGlobalScreenItem;
use ILIAS\UI\Component\Component;

/**
 * Trait ComponentDecoratorApplierTrait
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ComponentDecoratorApplierTrait
{
    public function applyDecorator(Component $component, isGlobalScreenItem $item) : Component
    {
        $c = $item->getComponentDecorator();
        if ($c !== null) {
            return $c($component);
        }

        return $component;
    }
}
