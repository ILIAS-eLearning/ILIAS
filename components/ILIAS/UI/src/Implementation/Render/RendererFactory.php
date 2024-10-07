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

/**
 * This is the interface that components should use if they want to load specific
 * renderers.
 */
interface RendererFactory
{
    /**
     * Get a renderer based on the current context.
     *
     * Context names are fully qualified component names.
     *
     * @param string[] $contexts
     */
    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer;
}
