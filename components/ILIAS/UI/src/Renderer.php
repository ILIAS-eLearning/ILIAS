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
}
