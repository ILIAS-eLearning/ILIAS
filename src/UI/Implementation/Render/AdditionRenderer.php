<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * An entity that renders additions on components to a string output.
 *
 * @author	Ingmar Szmais <iszmais@databay.de>
 */
interface AdditionRenderer
{
    /**
     * Render the addition.
     *
     * @param	Component 		$component
     * @param	Renderer		$default_renderer
     * @throws	\LogicException	if renderer is called with a component it can't render
     * @return	string
     */
    public function render(Component $component, Renderer $default_renderer) : string;

    /**
     * Announce resources this renderer requires.
     *
     * @param	ResourceRegistry	$registry
     * @return	null
     */
    public function registerResources(ResourceRegistry $registry);

    public function append() : bool;

    public function prepend() : bool;
}

