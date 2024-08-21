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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Class ilTestPlayerModal
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilTestPlayerConfirmationModal
{
    protected string $header_text = '';
    protected string $confirmation_text = '';
    protected string $confirmation_checkbox_name = '';
    protected string $confirmation_checkbox_label = '';
    protected string $action_button_label = '';

    /**
     * @var ilHiddenInputGUI[]
     */
    protected array $parameters = [];

    public function __construct(
        protected UIRenderer $ui_renderer,
        protected UIFactory $ui_factory
    ) {
    }

    public function getHeaderText(): string
    {
        return $this->header_text;
    }

    public function setHeaderText(string $header_text): self
    {
        $this->header_text = $header_text;
        return $this;
    }

    public function getConfirmationText(): string
    {
        return $this->confirmation_text;
    }

    public function setConfirmationText(string $confirmation_text): self
    {
        $this->confirmation_text = $confirmation_text;
        return $this;
    }

    public function getConfirmationCheckboxName(): string
    {
        return $this->confirmation_checkbox_name;
    }

    public function setConfirmationCheckboxName(string $confirmation_checkbox_name): self
    {
        $this->confirmation_checkbox_name = $confirmation_checkbox_name;
        return $this;
    }

    public function getConfirmationCheckboxLabel(): string
    {
        return $this->confirmation_checkbox_label;
    }

    public function setConfirmationCheckboxLabel(string $confirmation_checkbox_label): self
    {
        $this->confirmation_checkbox_label = $confirmation_checkbox_label;
        return $this;
    }

    public function getActionButtonLabel(): string
    {
        return $this->action_button_label;
    }

    public function setActionButtonLabel(string $action_button_label): self
    {
        $this->action_button_label = $action_button_label;
        return $this;
    }

    /**
     * @return ilHiddenInputGUI[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter(ilHiddenInputGUI $hidden_input_gui): self
    {
        $this->parameters[] = $hidden_input_gui;
        return $this;
    }

    public function isConfirmationCheckboxRequired(): bool
    {
        return $this->getConfirmationCheckboxName() !== '' && $this->getConfirmationCheckboxLabel() !== '';
    }

    /**
     * @throws ilTemplateException
     */
    private function buildModalBody(): string
    {
        $tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'components/ILIAS/Test');

        if ($this->isConfirmationCheckboxRequired()) {
            $tpl->setCurrentBlock('checkbox');
            $tpl->setVariable('CONFIRMATION_CHECKBOX_NAME', $this->getConfirmationCheckboxName());
            $tpl->setVariable('CONFIRMATION_CHECKBOX_LABEL', $this->getConfirmationCheckboxLabel());
            $tpl->parseCurrentBlock();
        }

        foreach ($this->getParameters() as $parameter) {
            $tpl->setCurrentBlock('hidden_inputs');
            $tpl->setVariable('HIDDEN_INPUT', $parameter->getToolbarHTML());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('CONFIRMATION_TEXT', $this->getConfirmationText());

        return $tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    public function getHTML(string &$modal_id): string
    {
        $rendered_modal = $this->ui_renderer->render($this->ui_factory->modal()->interruptive(
            $this->getHeaderText(),
            $this->buildModalBody(),
            ''
        )->withActionButtonLabel($this->getActionButtonLabel()));

        $doc = new DOMDocument();
        @$doc->loadHTML($rendered_modal);
        $modal_id = $doc->getElementsByTagName('div')->item(0)->attributes->getNamedItem('id')->nodeValue ?? $modal_id;

        return $rendered_modal;
    }
}
