<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

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

        if ($component instanceof Component\Panel\Listing\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
    }

    protected function renderStandard(Component\Panel\Listing\Listing $component, RendererInterface $default_renderer)
    {
        global $DIC;


        $tpl = $this->getTemplate("tpl.listing_standard.html", true, true);

        foreach ($component->getItemGroups() as $group) {
            if ($group instanceof \ILIAS\UI\Component\Item\Group) {
                $tpl->setCurrentBlock("group");
                $tpl->setVariable("ITEM_GROUP", $default_renderer->render($group));
                $tpl->parseCurrentBlock();
            }
        }


        $title = $component->getTitle();
        $tpl->setVariable("LIST_TITLE", $title);

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Panel\Listing\Standard::class
        );
    }
}
