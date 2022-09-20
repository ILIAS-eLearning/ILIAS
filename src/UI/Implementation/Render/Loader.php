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
    public function getRendererFor(Component $component, array $contexts): ComponentRenderer;

    /**
     * Get a factory for a renderer for a certain component class.
     */
    public function getRendererFactoryFor(Component $component): RendererFactory;
}
