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
use ILIAS\UI\Implementation\Render\Template;
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

        if ($component instanceof Form\FormWithoutSubmitButton) {
            return $this->renderNoSubmit($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderStandard(Form\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        $this->maybeAddRequired($component, $tpl);
        $this->addPostURL($component, $tpl);
        $this->maybeAddError($component, $tpl);

        $submit_button = $this->getUIFactory()->button()->standard(
            $component->getSubmitCaption() ?? $this->txt("save"), ""
        );

        $tpl->setVariable("BUTTONS_TOP", $default_renderer->render($submit_button));
        $tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));
        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputGroup()));

        return $tpl->get();
    }

    protected function renderNoSubmit(Form\FormWithoutSubmitButton $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.no_submit.html", true, true);

        $this->maybeAddRequired($component, $tpl);
        $this->addPostURL($component, $tpl);
        $this->maybeAddError($component, $tpl);

        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputGroup()));

        /** @var $component Form\FormWithoutSubmitButton */
        $enriched_component = $component->withAdditionalOnLoadCode(
            static function (string $id) use ($component): string {
                return "
                    // @TODO: we need to refactor the signal-management to prevent using jQuery here.
                    $(document).on('{$component->getSubmitSignal()}', function () {
                        let form = document.getElementById('$id');
                        if (!form instanceof HTMLFormElement) {
                            throw new Error(`Element '$id' is not an instance of HTMLFormElement.`);
                        }
                        
                        // @TODO: we should use the triggering button as an emitter here. When doing
                        // so, please also change file.js processFormSubmissionHook().
                        form.requestSubmit();
                    });
                ";
            }
        );

        $id = $this->bindJavaScript($enriched_component) ?? $this->createId();
        $tpl->setVariable("ID", $id);

        return $tpl->get();
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
            $tpl->setCurrentBlock("error");
            $tpl->setVariable("ERROR", $error);
            $tpl->parseCurrentBlock();
        }
    }

    protected function maybeAddRequired(Form\Form $component, Template $tpl): void
    {
        if ($component->hasRequiredInputs()) {
            $tpl->setVariable("TXT_REQUIRED", $this->txt("required_field"));
        }
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Input\Container\Form\Standard::class,
            FormWithoutSubmitButton::class,
        ];
    }
}
