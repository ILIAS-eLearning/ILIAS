<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

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

        if ($component instanceof Component\Item\Group) {
            return $this->renderGroup($component, $default_renderer);
        }
        if ($component instanceof Component\Item\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
    }

    protected function renderGroup(Component\Item\Group $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.group.html", true, true);
        $title = $component->getTitle();
        $items = $component->getItems();

        // items
        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }


        $tpl->setVariable("TITLE", $title);

        return $tpl->get();
    }

    protected function renderStandard(Component\Item\Item $component, RendererInterface $default_renderer)
    {
        global $DIC;

        $tpl = $this->getTemplate("tpl.item_standard.html", true, true);

        // color
        $color = $component->getColor();
        if ($color !== null) {
            $tpl->setCurrentBlock("color");
            $tpl->setVariable("COLOR", $color->asHex());
            $tpl->parseCurrentBlock();
        }

        // lead
        $lead = $component->getLead();
        if ($lead != null) {
            if (is_string($lead)) {
                $tpl->setCurrentBlock("lead_text");
                $tpl->setVariable("LEAD_TEXT", $lead);
                $tpl->parseCurrentBlock();
            }
            if ($lead instanceof Component\Image\Image) {
                $tpl->setCurrentBlock("lead_image");
                $tpl->setVariable("LEAD_IMAGE", $default_renderer->render($lead));
                $tpl->parseCurrentBlock();
            }
            if ($lead instanceof Component\Icon\Icon) {
                $tpl->setCurrentBlock("lead_icon");
                $tpl->setVariable("LEAD_ICON", $default_renderer->render($lead));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("lead_start_icon");
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("lead_start");
                $tpl->parseCurrentBlock();
            }


            $tpl->touchBlock("lead_end");
        }

        // description
        $desc = $component->getDescription();
        if (trim($desc) != "") {
            $tpl->setCurrentBlock("desc");
            $tpl->setVariable("DESC", $desc);
            $tpl->parseCurrentBlock();
        }

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        // properties
        $props = $component->getProperties();
        if (count($props) > 0) {
            $cnt = 0;
            foreach ($props as $name => $value) {
                if ($value instanceof \ILIAS\UI\Component\Button\Shy) {
                    $value = $default_renderer->render($value);
                }

                $cnt++;
                if ($cnt % 2 == 1) {
                    $tpl->setCurrentBlock("property_row");
                    $tpl->setVariable("PROP_NAME_A", $name);
                    $tpl->setVariable("PROP_VAL_A", $value);
                } else {
                    $tpl->setVariable("PROP_NAME_B", $name);
                    $tpl->setVariable("PROP_VAL_B", $value);
                    $tpl->parseCurrentBlock();
                }
            }
            if ($cnt % 2 == 1) {
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("properties");
            $tpl->parseCurrentBlock();
        }

        $title = $component->getTitle();

        if ($title instanceof \ILIAS\UI\Component\Button\Shy) {
            $title = $default_renderer->render($title);
        }

        $tpl->setVariable("TITLE", $title);

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Item\Standard::class
            , Component\Item\Group::class
        );
    }
}
