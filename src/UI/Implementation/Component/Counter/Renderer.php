<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        $tpl = $this->getTemplate("tpl.counter.html", true, true);
        if ($component->getNumber() === 0) {
            $tpl->touchBlock("hidden_" . $component->getType());
        }
        $tpl->setCurrentBlock($component->getType());
        $tpl->setVariable("NUMBER", $component->getNumber());
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Counter/counter.js');
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Counter\Counter::class);
    }
}
