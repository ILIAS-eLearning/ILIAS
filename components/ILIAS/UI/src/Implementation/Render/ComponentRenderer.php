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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;

/**
 * An entity that renders components to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface ComponentRenderer
{
    /**
     * Render the component if possible and delegate additional rendering to the
     * default_renderer.
     *
     * @throws \RuntimeException if renderer is called with a component it can't render
     */
    public function render(Component $component, Renderer $default_renderer): string;

    /**
     * Announce resources this renderer requires.
     */
    public function registerResources(ResourceRegistry $registry): void;
}
