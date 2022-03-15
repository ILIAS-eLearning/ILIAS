<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;

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
 * Class PluginRenderer
 *
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class PluginRenderer extends DefaultRenderer
{
    protected $registry;
    protected $default;

    public function __construct(Loader $component_renderer_loader, ResourceRegistry $registry, DefaultRenderer $default)
    {
        $this->registry = $registry;
        $this->default = $default;
        parent::__construct($component_renderer_loader);
    }


    /**
     * @inheritdoc
     */
    public function withAdditionalContext(Component $context)
    {
        $clone = parent::withAdditionalContext($context);
        $clone->default = $clone->default->withAdditionalContext($context);
        return $clone;
    }

    protected function getRendererFor(Component $component, $renderer = null) : ComponentRenderer
    {
        if ($renderer) {
            if ($renderer instanceof ComponentRenderer) {
                $default = $this->default->getRendererFor($component);
                if ($default instanceof WrappingRenderer) {
                    $default->exchangeRenderer($renderer);
                } else {
                    $default = $renderer;
                }
                $default->registerResources($this->registry);
                return $default;
            }

            if ($renderer instanceof AdditionRenderer) {
                $wrapper = new WrappingRenderer($this->default->getRendererFor($component));
                if ($renderer->prepend() === true) {
                    $wrapper->prependRenderer($renderer);
                }
                if ($renderer->append() === true) {
                    $wrapper->appendRenderer($renderer);
                }
                $wrapper->registerResources($this->registry);
                return $wrapper;
            }
        }
        return $this->default->getRendererFor($component);
    }
}
