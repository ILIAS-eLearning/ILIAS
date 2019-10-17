<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Image\Image $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.image.html", true, true);

        $id = $this->bindJavaScript($component);
        if (!empty($component->getAction())) {
            $tpl->touchBlock("action_begin");

            if (is_string($component->getAction())) {
                $tpl->setCurrentBlock("with_href");
                $tpl->setVariable("HREF", $component->getAction());
                $tpl->parseCurrentBlock();
            }

            if (is_array($component->getAction())) {
                $tpl->setCurrentBlock("with_id");
                $tpl->setVariable("ID", $id);
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setCurrentBlock($component->getType());
        $tpl->setVariable("SOURCE", $component->getSource());
        $tpl->setVariable("ALT", htmlspecialchars($component->getAlt()));
        if (empty($component->getAction()) && $id !== null) {
            $tpl->setVariable("IMG_ID", " id='" . $id . "' ");
        }
        $tpl->parseCurrentBlock();

        if (!empty($component->getAction())) {
            $tpl->touchBlock("action_end");
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Image\Image::class];
    }
}
