<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) {
        /**
         * @var Component\Icon\Icon $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.icon.html", true, true);

        $id = $this->createId();
        $tpl->setVariable("ID",$id);
        $tpl->setVariable("CLASS",$component->cssclass());
        $tpl->setVariable("ARIA_LABEL",$component->aria());
        $tpl->setVariable("SIZE",$component->size());
        $ab = $component->abbreviation();
        if($ab) {
            $tpl->setVariable("ABBREVIATION",$ab);
        }

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() {
        return array(Component\Icon\Icon::class);
    }
}