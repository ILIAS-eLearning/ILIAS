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
 */

declare(strict_types=1);

use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Render\DecoratedRenderer;
use ILIAS\UI\Renderer;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Extend the DecoratedRenderer to align your renderer with other potential renderers in ILIAS,
 * and allow manipulations from different sources to be chained to one another.
 */
class HelloWorldRenderer extends DecoratedRenderer
{
    public function __construct(ResourceRegistry $resource_registry, Renderer $default)
    {
        $this->registerResources($resource_registry);
        parent::__construct($default);
    }

    protected function manipulateRendering($component, Renderer $root): ?string
    {
        // if the component is an instance of our custom implementation, we can
        // render it according to our business logic and pass it to the chain.
        if ($component instanceof HelloWorld) {
            return "<p>{$component->getGreeting()}</p>";
        }

        // return null to indicate you are not interested in the given component.
        return null;
    }

    protected function registerResources(ResourceRegistry $resource_registry): void
    {
        $resource_registry->register('some/asset/path');
    }
}
