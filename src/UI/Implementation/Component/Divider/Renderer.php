<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Divider;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Divider\Horizontal) {
            return $this->renderDividerHorizontal($component, $default_renderer);
        }
        if ($component instanceof Component\Divider\Vertical) {
            return $this->renderDividerVertical($component, $default_renderer);
        }
        return "";
    }

    protected function renderDividerHorizontal(Component\Divider\Horizontal $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.horizontal.html", true, true);

        $label = $component->getLabel();

        if ($label !== null) {
            $tpl->setCurrentBlock("label");
            $tpl->setVariable("LABEL", $label);
            $tpl->parseCurrentBlock();
            $tpl->touchBlock("with_label");
        } else {
            $tpl->touchBlock("divider");
        }

        return $tpl->get();
    }

    protected function renderDividerVertical(Component\Divider\Vertical $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.vertical.html", true, true);

        $tpl->touchBlock("divider");

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Divider\Horizontal::class
        ,Component\Divider\Vertical::class
        );
    }
}
