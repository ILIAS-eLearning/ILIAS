<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    const DEFAULT_ICON_NAME = 'default';
    const ICON_PATH = './templates/default/images';
    const ICON_OUTLINED_PATH = './templates/default/images/outlined';
    const ICON_NAME_PATTERN = 'icon_%s.svg';

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Symbol\Icon\Icon $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.icon.html", true, true);

        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("SIZE", $component->getSize());

        $tpl->setVariable("ALT", $component->getLabel());
        
        if ($component instanceof Component\Symbol\Icon\Standard) {
            $tpl->setVariable("CUSTOMIMAGE", $this->getStandardIconPath($component));
            if ($component->isOutlined()) {
                $tpl->touchBlock('outlined');
            }
        } else {
            $tpl->setVariable("CUSTOMIMAGE", $component->getIconPath());
        }

        $ab = $component->getAbbreviation();
        if ($ab) {
            $tpl->setVariable("ABBREVIATION", $ab);
        }

        if ($component->isDisabled()) {
            $tpl->touchBlock('disabled');
            $tpl->touchBlock('aria_disabled');
        }

        return $tpl->get();
    }


    protected function getStandardIconPath(Component\Symbol\Icon\Icon $icon) : string
    {
        $name = $icon->getName();
        if (!in_array($name, $icon->getAllStandardHandles())) {
            $name = self::DEFAULT_ICON_NAME;
        }

        $path = self::ICON_PATH;
        if ($icon->isOutlined()) {
            $path = self::ICON_OUTLINED_PATH;
        }
        return $path . '/' . sprintf(self::ICON_NAME_PATTERN, $name);
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Symbol\Icon\Icon::class);
    }
}
