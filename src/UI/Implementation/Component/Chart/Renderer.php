<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart;

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

        $tpl = $this->getTemplate("tpl.scale_bar.html", true, true);

        $items = $component->getItems();
        if (count($items) == 0) {
            return "";
        }

        $width = str_replace(",", ".", round(100 / count($items), 5));

        foreach ($component->getItems() as $txt => $active) {
            if ($active) {
                $tpl->setCurrentBlock("active");
                $tpl->setVariable("TXT_ACTIVE", $this->txt("active"));
                $tpl->parseCurrentBlock();
                $tpl->touchBlock("active_class");
            }

            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM_TEXT", $txt);
            $tpl->setVariable("ITEM_WIDTH", $width);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Chart\ScaleBar::class);
    }
}
