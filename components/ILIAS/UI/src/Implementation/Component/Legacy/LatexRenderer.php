<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\LatexResources;

/**
 * Renderer for content with enabled latex processing
 * This is implemented as a separate renderer to register the resources only when specific LatexContent is used
 *
 * The MathJax js library is invasive:
 *  - it scans the whole page for elements with "tex2jax_process"
 *  - it modifies their content
 *  - it adds additional assets dynamically
 * Therefore Mathjax should not be included if simple legacy content is used
 */
class LatexRenderer extends Renderer
{
    public const string MATHJAX_ENABLING_CLASS = 'tex2jax_process';

    protected ?LatexResources $latex_resources = null;

    public function withLatexResources(?LatexResources $resources) : self
    {
        $clone = clone($this);
        $clone->latex_resources = $resources;
        return $clone;
    }

    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Component\Legacy\LatexContent) {
            $this->cannotHandleComponent($component);
        }

        return $this->enableLatex(parent::render($component, $default_renderer));
    }

    protected function enableLatex(string $content)
    {
        return '<div style="display: inherit;" class="' . self::MATHJAX_ENABLING_CLASS . '">' . $content . '</div>';
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        foreach ((array) $this->latex_resources?->toRegister() as $resource) {
            $registry->register($resource);
        }
    }
}