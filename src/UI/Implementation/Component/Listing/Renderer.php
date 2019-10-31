<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Listing\Listing $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Listing\Descriptive) {
            return $this->render_descriptive($component, $default_renderer);
        } else {
            return $this->render_simple($component, $default_renderer);
        }
    }

    /**
     * @param Component\Listing\descriptive $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function render_descriptive(Component\Listing\Descriptive $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.descriptive.html", true, true);

        foreach ($component->getItems() as $key => $item) {
            if (is_string($item)) {
                $content = $item;
            } else {
                $content = $default_renderer->render($item);
            }

            if (trim($content) != "") {
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("DESCRIPTION", $key);
                $tpl->setVariable("CONTENT", $content);
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    /**
     * @param Component\Listing\Listing $component
     * @param RendererInterface $default_renderer
     * @return mixed
     */
    protected function render_simple(Component\Listing\Listing $component, RendererInterface $default_renderer)
    {
        $tpl_name = "";

        if ($component instanceof Component\Listing\Ordered) {
            $tpl_name = "tpl.ordered.html";
        }
        if ($component instanceof Component\Listing\Unordered) {
            $tpl_name = "tpl.unordered.html";
        }

        $tpl = $this->getTemplate($tpl_name, true, true);

        if (count($component->getItems()) > 0) {
            foreach ($component->getItems() as $item) {
                $tpl->setCurrentBlock("item");
                if (is_string($item)) {
                    $tpl->setVariable("ITEM", $item);
                } else {
                    $tpl->setVariable("ITEM", $default_renderer->render($item));
                }
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }


    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Listing\Listing::class];
    }
}
