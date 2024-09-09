<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $component = $component->withAdditionalOnLoadCode(
            fn($id) => "il.UI.Input.Container.init('{$id}');"
        );

        if ($component instanceof Form\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderStandard(Form\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        $this->maybeAddDedicatedName($component, $tpl);
        $this->maybeAddRequired($component, $tpl);
        $this->addPostURL($component, $tpl);
        $this->maybeAddError($component, $tpl);

        $submit_button = $this->getUIFactory()->button()->standard(
            $component->getSubmitLabel() ?? $this->txt("save"),
            ""
        );

        $tpl->setVariable("BUTTONS_TOP", $default_renderer->render($submit_button));
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));
        $tpl->setVariable(
            "INPUTS",
            $default_renderer
            //->withAdditionalContext($component)
            ->render($component->getInputGroup())
        );



        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        return $tpl->get();
    }

    protected function maybeAddDedicatedName(Form\Form $component, Template $tpl): void
    {
        if ($component->getDedicatedName() !== null) {
            $tpl->setVariable("NAME", 'name="' . $component->getDedicatedName() . '"');
        }
    }

    protected function addPostURL(Component\Input\Container\Form\FormWithPostURL $component, Template $tpl): void
    {
        if ('' !== ($url = $component->getPostURL())) {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("URL", $url);
            $tpl->parseCurrentBlock();
        }
    }

    protected function maybeAddError(Form\Form $component, Template $tpl): void
    {
        if (null !== ($error = $component->getError())) {
            $tpl->setVariable("ERROR", $error);
            $tpl->setVariable("ERROR_LABEL", $this->txt("ui_error"));
        }
    }

    protected function maybeAddRequired(Form\Form $component, Template $tpl): void
    {
        if ($component->hasRequiredInputs()) {
            $tpl->setVariable("TXT_REQUIRED_TOP", $this->txt("required_field"));
            $tpl->setVariable("TXT_REQUIRED", $this->txt("required_field"));
        }
    }


    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./assets/js/container.min.js');
    }
}
