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

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use LogicException;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be rendered.
 */
class DefaultRenderer implements Renderer
{
    private Render\Loader $component_renderer_loader;

    /**
     * @var Component[]
     */
    private array $contexts = [];

    public function __construct(Render\Loader $component_renderer_loader)
    {
        $this->component_renderer_loader = $component_renderer_loader;
    }

    /**
     * @inheritdoc
     */
    public function render($component, ?Renderer $root = null)
    {
        $root = $root ?? $this;

        $out = '';
        if (is_array($component)) {
            foreach ($component as $_component) {
                $out .= $root->render($_component);
            }
        } else {
            $renderer = $this->getRendererFor($component);
            $out = $renderer->render($component, $root);
        }

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function renderAsync($component, ?Renderer $root = null)
    {
        $root = $root ?? $this;

        $out = '';
        if (is_array($component)) {
            foreach ($component as $_component) {
                $out .= $root->renderAsync($_component);
            }
        } else {
            $out = $this->render($component, $root) .
            $this->getJSCodeForAsyncRenderingFor($component);
        }
        return $out;
    }

    /**
     * Get a renderer for a certain Component class.
     *
     * Either initializes a new renderer or uses a cached one initialized
     * before.
     *
     * @throws LogicException if no renderer could be found for component.
     */
    protected function getRendererFor(Component $component): ComponentRenderer
    {
        return $this->component_renderer_loader->getRendererFor($component, $this->getContexts());
    }

    /**
     * Get JS-Code for asynchronous rendering of component.
     *
     * @param Component $component
     * @return string
     */
    protected function getJSCodeForAsyncRenderingFor(Component $component)
    {
        return $this->component_renderer_loader
            ->getRendererFactoryFor($component)
            ->getJSBinding()
            ->getOnLoadCodeAsync();
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalContext(Component $context): Renderer
    {
        $clone = clone $this;
        $clone->contexts[] = $context;
        return $clone;
    }

    /**
     * Get the contexts that are added via withAdditionalContext where most recently
     * added contexts come last.
     *
     * @return  Component[]
     */
    protected function getContexts(): array
    {
        return $this->contexts;
    }
}
