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
 * Class WrappingRenderer
 *
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class WrappingRenderer implements ComponentRenderer
{
    protected $prefix = null;
    protected $renderer;
    protected $suffix = null;


    public function __construct(ComponentRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function exchangeRenderer(ComponentRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function prependRenderer(AdditionRenderer $renderer) : void
    {
        $this->prefix = $renderer;
    }

    public function appendRenderer(AdditionRenderer $renderer) : void
    {
        $this->suffix = $renderer;
    }

    public function render(Component $component, Renderer $default_renderer)
    {
        $out = '';
        if ($this->prefix !== null) {
            $out .= $this->prefix->render($component, $default_renderer);
        }
        $out .= $this->renderer->render($component, $default_renderer);
        if ($this->suffix !== null) {
            $out .= $this->suffix->render($component, $default_renderer);
        }

        return $out;
    }

    public function registerResources(ResourceRegistry $registry)
    {
        $this->renderer->registerResources($registry);
        if ($this->prefix !== null) {
            $this->prefix->registerResources($registry);
        }
        if ($this->suffix !== null) {
            $this->suffix->registerResources($registry);
        }
    }
}
