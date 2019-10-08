<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Panel\Panel $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Panel\Standard) {
            /**
             * @var Component\Panel\Standard $component
             */
            return $this->renderStandard($component, $default_renderer);
        } elseif ($component instanceof Component\Panel\Sub) {
            /**
             * @var Component\Panel\Sub $component
             */
            return $this->renderSub($component, $default_renderer);
        }
        /**
         * @var Component\Panel\Report $component
         */
        return $this->renderReport($component, $default_renderer);
    }

    /**
     * @param Component\Component $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function getContentAsString(Component\Component $component, RendererInterface $default_renderer)
    {
        $content = "";
        foreach ($component->getContent() as $item) {
            $content .= $default_renderer->render($item);
        }
        return $content;
    }

    /**
     * @param Component\Panel\Standard $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderStandard(Component\Panel\Standard $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        $tpl->setVariable("TITLE", $component->getTitle());
        $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
        return $tpl->get();
    }

    /**
     * @param Component\Panel\Sub $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderSub(Component\Panel\Sub $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.sub.html", true, true);

        $actions = $component->getActions();

        if ($component->getTitle() != "" || $actions !== null) {
            $tpl->setCurrentBlock("title");

            // actions
            if ($actions !== null) {
                $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
            }

            // title
            $tpl->setVariable("TITLE", $component->getTitle());
            $tpl->parseCurrentBlock();
        }

        if ($component->getCard()) {
            $tpl->setCurrentBlock("with_card");
            $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
            $tpl->setVariable("CARD", $default_renderer->render($component->getCard()));
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock("no_card");
            $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @param Component\Panel\Report $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderReport(Component\Panel\Report $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.report.html", true, true);
        $tpl->setVariable("TITLE", $component->getTitle());
        $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Panel\Panel::class];
    }
}
