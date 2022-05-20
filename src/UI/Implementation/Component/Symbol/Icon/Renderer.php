<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    public const DEFAULT_ICON_NAME = 'default';
    public const ICON_NAME_PATTERN = 'icon_%s.svg';
    public const ICON_NAME_PATTERN_OUTLINED = 'outlined/icon_%s.svg';

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

        $id = $this->bindJavaScript($component);
    
        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("SIZE", $component->getSize());

        $tpl->setVariable("ALT", $component->getLabel());

        if ($component instanceof Component\Symbol\Icon\Standard) {
            $imagepath = $this->getStandardIconPath($component);
            if ($component->isOutlined()) {
                $tpl->touchBlock('outlined');
            }
        } else {
            $imagepath = $component->getIconPath();
        }

        $ab = $component->getAbbreviation();
        if ($ab) {
            $tpl->setVariable("ABBREVIATION", $ab);

            $abbreviation_tpl = $this->getTemplate("tpl.abbreviation.svg", true, true);
            $abbreviation_tpl->setVariable("ABBREVIATION", $ab);
            $abbreviation = $abbreviation_tpl->get() . '</svg>';

            $image = file_get_contents($imagepath);
            $image = substr($image, strpos($image, '<svg '));
            $image = trim(str_replace('</svg>', $abbreviation, $image));
            $imagepath = "data:image/svg+xml;base64," . base64_encode($image);
        }

        $tpl->setVariable("CUSTOMIMAGE", $imagepath);

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
        $pattern = self::ICON_NAME_PATTERN;
        if ($icon->isOutlined()) {
            $pattern = self::ICON_NAME_PATTERN_OUTLINED;
        }

        $icon_name = sprintf($pattern, $name);
        return $this->getImagePathResolver()->resolveImagePath($icon_name);
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Symbol\Icon\Icon::class);
    }
}
