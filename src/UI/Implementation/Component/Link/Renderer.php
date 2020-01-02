<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

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

        if ($component instanceof Component\Link\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        return "";
    }

    protected function renderStandard(Component\Link\Standard $component, RendererInterface $default_renderer)
    {
        $tpl_name = "tpl.standard.html";

        $tpl = $this->getTemplate($tpl_name, true, true);
        $action = $component->getAction();
        $label = $component->getLabel();
        if ($component->getOpenInNewViewport()) {
            $tpl->touchBlock("open_in_new_viewport");
        }
        $tpl->setVariable("LABEL", $label);
        $tpl->setVariable("HREF", $action);

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Link\Standard::class
        );
    }
}
