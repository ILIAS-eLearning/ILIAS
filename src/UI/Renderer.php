<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

use ILIAS\UI\Component\Component;

/**
 * An entity that renders components to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Renderer {

	/**
	 * Render given component. If an array of components is passed, this method returns a concatenated output of
	 * each rendered component, in the same order as given in the array
	 *
	 * @param Component\Component|Component\Component[] $component
	 *
	 * @return string
	 */
	public function render($component);

	/**
	 * Get a new renderer with an additional context.
	 *
	 * A context makes it possible to use another renderer for (some) components when
	 * they are renderer as subcomponents of a certain components. The use case that
	 * spawned this functionality is the observation, that e.g. items representing
	 * repository objects are renderer in different lists, where the individual items
	 * look different every time but are morally the same item. Another use case could
	 * be a kiosk mode for replaying tests or using ILIAS a LTI tool provider.
	 *
	 * Consider you have two components: Container and Item, where Items can be
	 * rendered inside containers or on their own, but should look differently in
	 * both situations. You could implement a standard renderer for the Item, that
	 * renders the item without taking the container into account. This would go
	 * in the standard Renderer-class in the Item-namespace.
	 * To take care of the rendering in the container, a second renderer Renderer_Container
	 * can be implemented in the Item-namespace, that takes care of the rendering
	 * in the container context.
	 *
	 * $item = $ui_factory->item();
	 * $container = $ui_factory->container();
	 *
	 * $default_renderer->render($item); // will use standard renderer
	 *
	 * $default_renderer_with_context = $default_renderer->withAdditionalContext($container);
	 * $default_renderer_with_context->render($item); // will use Renderer_Container if available
	 *
	 * @param  Component	$context
	 * @return Renderer
	 */
	public function withAdditionalContext(Component $context);
}
