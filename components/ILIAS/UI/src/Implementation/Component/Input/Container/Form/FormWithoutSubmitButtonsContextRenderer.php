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

class FormWithoutSubmitButtonsContextRenderer extends Renderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Form\Standard) {
            return $this->renderFormWithoutSubmitButtons($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderFormWithoutSubmitButtons(
        Form\Standard $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.without_submit_buttons.html", true, true);

        $this->maybeAddDedicatedName($component, $tpl);
        $this->maybeAddRequired($component, $tpl);
        $this->addPostURL($component, $tpl);
        $this->maybeAddError($component, $tpl);

        $tpl->setVariable("INPUTS", $default_renderer->render($component->getInputGroup()));

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
}
