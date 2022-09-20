<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Form\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderStandard(Form\Standard $component, RendererInterface $default_renderer): string
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
    protected function getComponentInterfaceName(): array
    {
        return array(Component\Input\Container\Form\Standard::class);
    }
}
