<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use \ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Implementation\Component\Dropdown\Dropdown;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        /**
         * @var $component Dropdown
         */
        return $this->renderDropdown($component, $default_renderer);
    }

    protected function renderDropdown(Dropdown $component, RendererInterface $default_renderer)
    {

        // get template
        $tpl_name = "tpl.standard.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        // render items
        $items = $component->getItems();
        if (is_array($items) && count($items) == 0) {
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

        // ensure that a) a separate aria label may be provided and
        // b) that an empty label and empty aria-label will use the "actions" fallback
        if ($component->getLabel() == "" || $component->getAriaLabel() != "") {
            $aria_label = ($component->getAriaLabel() != "")
                ? $component->getAriaLabel()
                : $this->txt("actions");
            $tpl->setCurrentBlock("aria_label");
            $tpl->setVariable("ARIA_LABEL", $aria_label);
            $tpl->parseCurrentBlock();
        }

        $this->maybeRenderId($component, $tpl, "with_id", "ID");

        return $tpl->get();
    }

    protected function renderItems(array $items, Template $tpl, RendererInterface $default_renderer)
    {
        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }
    }


    protected function maybeRenderId(JavaScriptBindable $component, Template $tpl, $block, $template_var)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock($block);
            $tpl->setVariable($template_var, $id);
            $tpl->parseCurrentBlock();
        }
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
