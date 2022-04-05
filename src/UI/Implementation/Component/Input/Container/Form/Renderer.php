<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Form\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderStandard(Form\Standard $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        if ($component->getPostURL() != "") {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("URL", $component->getPostURL());
            $tpl->parseCurrentBlock();
        }

        $f = $this->getUIFactory();
        $submit_button = $f->button()->standard($component->getSubmitCaption() ?? $this->txt("save"), "");

        $tpl->setVariable("BUTTONS_TOP", $default_renderer->render($submit_button));
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));

        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputGroup()));

        $error = $component->getError();
        if (!is_null($error)) {
            $tpl->setVariable("ERROR", $error);
        }
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return array(Component\Input\Container\Form\Standard::class);
    }
}
