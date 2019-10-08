<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Loads renderers for components.
 */
interface Loader
{
    /**
     * Get a renderer for a certain Component class.
     *
     * @param	Component	$component
     * @param	Component[]	$contexts
     * @throws	\LogicException		if no renderer could be found for component.
     * @return	ComponentRenderer
     */
    public function getRendererFor(Component $component, array $contexts);

    /**
     * Get a factory for a renderer for a certain component class.
     *
     * @param	Component	$component
     * @return	RendererFactory
     */
    public function getRendererFactoryFor(Component $component);
}
