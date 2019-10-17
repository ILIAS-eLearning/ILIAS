<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

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
        /**
         * @var Component\Icon\Icon $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.icon.html", true, true);

        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("ARIA_LABEL", $component->getAriaLabel());
        $tpl->setVariable("SIZE", $component->getSize());

        if ($component instanceof Component\Icon\Custom) {
            $tpl->setVariable("CUSTOMIMAGE", $component->getIconPath());
        } else {
            if ($component->isOutlined()) {
                $tpl->setVariable("OUTLINED", " outlined");
            }
        }

        $ab = $component->getAbbreviation();
        if ($ab) {
            $tpl->setVariable("ABBREVIATION", $ab);
        }

        $di = $component->isDisabled();
        if ($di) {
            $tpl->setVariable("DISABLED", " disabled");
        }

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Icon\Icon::class);
    }
}
