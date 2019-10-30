<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

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

        if ($component instanceof Component\Input\Container\Form\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new \LogicException("Cannot render: " . get_class($component));
    }


    protected function renderStandard(Component\Input\Container\Form\Standard $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        $tpl->setVariable("URL", $component->getPostURL());

        $f = $this->getUIFactory();
        $submit_button = $f->button()->standard($this->txt("save"), "#")// TODO: replace this with proper 'submit'-signal of form.
                           ->withOnLoadCode(function ($id) {
                               return "$('#{$id}').on('click', function(ev) {" . "	$('#{$id}').parents('form').submit();" . "   ev.preventDefault();" . "});";
                           });

        $tpl->setVariable("BUTTONS_TOP", $default_renderer->render($submit_button));
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));

        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputGroup()));

        return $tpl->get();
    }


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Input\Container\Form\Standard::class,
        );
    }
}
