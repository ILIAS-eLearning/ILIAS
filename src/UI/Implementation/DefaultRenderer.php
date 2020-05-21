<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be renderered.
 */
class DefaultRenderer implements Renderer
{
    /**
     * @var	Render\Loader
     */
    private $component_renderer_loader;

    /**
     * @var Component[]
     */
    private $contexts = [];

    public function __construct(Render\Loader $component_renderer_loader)
    {
        $this->component_renderer_loader = $component_renderer_loader;
    }

    /**
     * @inheritdoc
     */
    public function render($component)
    {
        $out = '';
        if (is_array($component)) {
            foreach ($component as $_component) {
                $renderer = $this->getRendererFor($_component);
                $out .= $renderer->render($_component, $this);
            }
        } else {
            $renderer = $this->getRendererFor($component);
            $out = $renderer->render($component, $this);
        }

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function renderAsync($component)
    {
        $out = '';

        if (is_array($component)) {
            foreach ($component as $_component) {
                $out .= $this->render($_component) .
                $this->component_renderer_loader
                        ->getRendererFactoryFor($_component)
                        ->getJSBinding()
                        ->getOnLoadCodeAsync();
            }
        } else {
            $out = $this->render($component) .
            $this->component_renderer_loader
                    ->getRendererFactoryFor($component)
                    ->getJSBinding()
                    ->getOnLoadCodeAsync();
        }
        return $out;
    }

    /**
     * Get a renderer for a certain Component class.
     *
     * Either initializes a new renderer or uses a cached one initialized
     * before.
     *
     * @param	Component	$component
     * @throws	\LogicException		if no renderer could be found for component.
     * @return	ComponentRenderer
     */
    protected function getRendererFor(Component $component)
    {
        return $this->component_renderer_loader->getRendererFor($component, $this->getContexts());
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalContext(Component $context)
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
    protected function getContexts()
    {
        return $this->contexts;
    }
}
