<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Breadcrumbs;

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

        $tpl = $this->getTemplate("tpl.breadcrumbs.html", true, true);

        foreach ($component->getItems() as $crumb) {
            $tpl->setCurrentBlock("crumbs");
            $tpl->setVariable("CRUMB", $default_renderer->render($crumb));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Breadcrumbs\Breadcrumbs::class
        );
    }
}
