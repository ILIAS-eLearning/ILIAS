<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

use ILIAS\UI\Component\Component;

/**
 * An entity that renders components to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Renderer
{

    /**
     * Render given component. If an array of components is passed, this method returns a concatenated output of
     * each rendered component, in the same order as given in the array
     *
     * @param Component|Component[] $component
     *
     * @return string
     */
    public function render($component);

    /**
     * Same as render, except that this version also returns any javascript code bound to the on load event,
     * wrapped in a script tag. All javascript code stored for rendering will be removed after this output
     * so it will not be rendered twice if render async is called multiple times.
     *
     * @param Component|Component[] $component
     * @return string
     */
    public function renderAsync($component);

    /**
     * Get a new renderer with an additional context.
     *
     * A context makes it possible to use another renderer for (some) components when
     * they are renderer as subcomponents of a certain components. The use case that
     * spawned this functionality is the observation, that e.g. items representing
     * repository objects are renderer in different lists, where the individual items
     * look different every time but are morally the same item. Another use case could
     * be a special rendering of input fields in filters over tables.
     *
     * If a component wants to render itself differently in different contexts, it must
     * implement a RendererFactory. The class \ILIAS\UI\Implementation\Render\FSLoader
     * contains directions how to do that.
     *
     * @param  Component	$context
     * @return Renderer
     */
    public function withAdditionalContext(Component $context);
}
