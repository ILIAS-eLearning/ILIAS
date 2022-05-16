<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;

abstract class DecoratedRenderer implements Renderer
{
    private $default;

    public function __construct(Renderer $default)
    {
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalContext(Component $context) : DecoratedRenderer
    {
        $clone = clone $this;
        $clone->default = $clone->default->withAdditionalContext($context);
        return $clone;
    }

    /**
     * Manipulates the rendering of one or multiple components by appending, prepending or exchanging their rendered
     * content with custom adjustments.
     *
     * @return string|null Return the manipulated rendering of the component or NULL if the component should be
     * rendered native
     */
    abstract protected function manipulateRendering($component, Renderer $root) : ?string;

    /**
     * Manipulates the async Rendering separately if needed.
     * @see manipulateRendering
     */
    protected function manipulateAsyncRendering($component, Renderer $root) : ?string
    {
        return null;
    }

    /**
     * Renders the component by default. Can be used for appending and prepending manipulations.
     * @see manipulateRendering
     */
    final protected function renderDefault($component, ?Renderer $root = null) : string
    {
        $root = $root ?? $this;
        return $this->default->render($component, $root);
    }

    /**
     * @inheritdoc
     */
    final public function render($component, ?Renderer $root = null) : string
    {
        $root = $root ?? $this;
        return $this->manipulateRendering($component, $root) ?? $this->default->render($component, $root);
    }

    /**
     * @inheritdoc
     */
    final public function renderAsync($component, ?Renderer $root = null) : string
    {
        $root = $root ?? $this;
        return $this->manipulateAsyncRendering($component, $root) ?? $this->default->renderAsync($component, $root);
    }
}
