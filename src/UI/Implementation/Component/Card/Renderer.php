<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Icon\Standard as StandardIcon;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Card\Card $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.card.html", true, true);

        if ($component->getImage()) {
            if ($component->getImageAction()) {
                $tpl->setCurrentBlock("image_action_begin");
                $tpl->setVariable("IMG_HREF", $component->getImageAction());
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable("IMAGE", $default_renderer->render($component->getImage(), $default_renderer));

            if ($component->getImageAction()) {
                $tpl->touchBlock("image_action_end");
            }
        }

        if ($component->isHighlighted()) {
            $tpl->touchBlock("highlight");
        } else {
            $tpl->touchBlock("no_highlight");
        }

        $title = $component->getTitle();
        if ($component->getTitleAction()) {
            $tpl->setCurrentBlock("title_action_begin");
            $tpl->setVariable("HREF", $component->getTitleAction());
            $tpl->parseCurrentBlock();
        } else {
            if ($title instanceof \ILIAS\UI\Component\Button\Shy) {
                $title = $default_renderer->render($title);
            }
        }

        $tpl->setVariable("TITLE", $title);

        if ($component->getTitleAction()) {
            $tpl->touchBlock("title_action_end");
        }

        if (is_array($component->getSections())) {
            foreach ($component->getSections() as $section) {
                $tpl->setCurrentBlock("section");
                $tpl->setVariable("SECTION", $default_renderer->render($section, $default_renderer));
                $tpl->parseCurrentBlock();
            }
        }

        if ($component instanceof Component\Card\RepositoryObject) {
            $tpl->setCurrentBlock("action");

            $obj_icon = $component->getObjectIcon();
            if ($obj_icon !== null) {
                $tpl->setVariable("OBJECT_ICON", $default_renderer->render($obj_icon, $default_renderer));
            }

            $progress = $component->getProgress();
            if ($progress !== null) {
                $tpl->setVariable("PROGRESS_STATUS", $default_renderer->render($progress));
            }

            $certificate = $component->getCertificateIcon();
            if ($certificate !== null) {
                $certificate_icon = new StandardIcon("cert", "Certificate", "medium", false);
                $certificate_icon = $certificate_icon->withIsOutlined(true);
                $tpl->setVariable("PROGRESS_STATUS", $default_renderer->render($certificate_icon));
            }

            $dropdown = $component->getActions();
            if ($dropdown !== null) {
                $tpl->setVariable("DROPDOWN", $default_renderer->render($dropdown));
            }

            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return array(Component\Card\Card::class);
    }
}
