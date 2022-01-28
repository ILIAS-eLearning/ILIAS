<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use LogicException;

/**
 * Loads renderers for components.
 */
interface Loader
{
    /**
     * Get a renderer for a certain Component class.
     *
     * @param	Component[]	$contexts
     * @throws	LogicException		if no renderer could be found for component.
     */
    public function getRendererFor(Component $component, array $contexts) : ComponentRenderer;

    /**
     * Get a factory for a renderer for a certain component class.
     */
    public function getRendererFactoryFor(Component $component) : RendererFactory;
}
