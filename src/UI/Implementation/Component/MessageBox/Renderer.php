<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\MessageBox
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $ui_fac = $this->getUIFactory();

        // @todo: workaround for tests... there should be a better way...
        if ($ui_fac instanceof \NoUIFactory) {
            $ui_fac = new \ILIAS\UI\Implementation\Factory();
        }

        /**
         * @var Component\MessageBox\MessageBox $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.messagebox.html", true, true);


        $buttons = $component->getButtons();
        if (count($buttons) > 0) {
            $tpl->setCurrentBlock("buttons");
            $tpl->setVariable("BUTTONS", $default_renderer->render($buttons));
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("message_box");

        $tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());
        $tpl->setVariable("ACC_TEXT", $this->txt($component->getType() . "_message"));


        $links = $component->getLinks();
        if (count($links) > 0) {
            $unordered = $ui_fac->listing()->unordered(
                $links
            );

            $tpl->setVariable("LINK_LIST", $default_renderer->render($unordered));
        }

        $tpl->touchBlock($component->getType() . "_class");

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function getComponentInterfaceName()
    {
        return array(Component\MessageBox\MessageBox::class);
    }
}
