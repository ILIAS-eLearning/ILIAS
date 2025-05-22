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

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use LogicException;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be rendered.
 */
class DefaultRenderer implements Renderer
{
    /**
     * @var Component[]
     */
    private array $contexts = [];

    public function __construct(
        private Render\Loader $component_renderer_loader,
        private JavaScriptBinding $java_script_binding,
        private \ILIAS\Language\Language $language,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function render($component, ?Renderer $root = null)
    {
        $this->language->loadLanguageModule('ui');

        $root = $root ?? $this;

        if (is_array($component)) {
            $out = '';
            foreach ($component as $_component) {
                $out .= $root->render($_component);
            }
            return $out;
        }

        try {
            $this->pushContext($component);
            $renderer = $this->getRendererFor($component);
            $out = $renderer->render($component, $root);
        } finally {
            $this->popContext();
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
            $out = $this->render($component, $root) . $this->java_script_binding->getOnLoadCodeAsync();
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
     * Returns the current context stack, where most recently added components are last.
     * E.g. ["FirstComponent", "SecondComponent", "ThirdComponent", ...];
     *
     * @return Component[]
     */
    protected function getContexts(): array
    {
        return $this->contexts;
    }

    /**
     * Adds a component to the current context stack. This mainly serves for testability.
     */
    protected function pushContext(Component $component): void
    {
        $this->contexts[] = $component;
    }

    /**
     * Removes the most recently added component from the current context stack.
     * This mainly serves for testability.
     */
    protected function popContext(): void
    {
        array_pop($this->contexts);
    }
}
