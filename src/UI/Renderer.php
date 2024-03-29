<?php

declare(strict_types=1);

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
     * @param ?Renderer $root of renderers in the chain to be used for rendering sub components.
     *
     * @return string
     */
    public function render($component, ?Renderer $root = null);

    /**
     * Same as render, except that this version also returns any javascript code bound to the on load event,
     * wrapped in a script tag. All javascript code stored for rendering will be removed after this output
     * so it will not be rendered twice if render async is called multiple times.
     *
     * @param Component|Component[] $component
     * @param ?Renderer $root of renderers in the chain to be used for rendering sub components.
     *
     * @return string
     */
    public function renderAsync($component, ?Renderer $root = null);

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
     */
    public function withAdditionalContext(Component $context): Renderer;
}
