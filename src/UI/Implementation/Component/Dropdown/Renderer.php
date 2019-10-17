<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        return $this->renderDropdown($component, $default_renderer);
    }

    protected function renderDropdown(Component\Dropdown\Dropdown $component, RendererInterface $default_renderer)
    {

        // get template
        $tpl_name = "tpl.standard.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        // render items
        $items = $component->getItems();
        if (count($items) == 0) {
            return "";
        }
        $this->renderItems($items, $tpl, $default_renderer);

        // render trigger button
        $label = $component->getLabel();
        if ($label !== null) {
            $tpl->setVariable("LABEL", $component->getLabel());
        } else {
            $tpl->setVariable("LABEL", "");
        }

        $this->maybeRenderId($component, $tpl, "with_id", "ID");

        return $tpl->get();
    }

    /**
     * @param array $items
     * @param ilTemplate $tpl
     */
    protected function renderItems($items, $tpl, $default_renderer)
    {
        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }
    }


    protected function maybeRenderId(Component\Component $component, $tpl, $block, $template_var)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock($block);
            $tpl->setVariable($template_var, $id);
            $tpl->parseCurrentBlock();
        }
    }


    /**
     * Append a block to touch during rendering and return cloned instance
     *
     * @param string 	$block
     *
     * @return Renderer
     */
    public function withBlocksToBeTouched($block)
    {
        assert(is_string($block));
        $clone = clone $this;
        $clone->touch_blocks[] = $block;
        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Dropdown/dropdown.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Dropdown\Standard::class
        );
    }
}
