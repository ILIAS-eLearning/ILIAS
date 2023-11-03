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

use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Implementation\Component\Button\Primary as PrimaryButton;

/**
 * Class ilTestPlayerModal
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilTestPlayerConfirmationModal
{
    protected string $modal_id = '';
    protected string $header_text = '';
    protected string $confirmation_text = '';
    protected string $confirmation_checkbox_name = '';
    protected string $confirmation_checkbox_label = '';

    /**
     * @var \ILIAS\UI\Component\Button\Standard[]
     */
    protected array $buttons = [];

    /**
     * @var ilHiddenInputGUI[]
     */
    protected array $parameters = [];

    public function __construct(
        protected UIRenderer $ui_renderer
    ) {
    }

    public function getModalId(): string
    {
        return $this->modal_id;
    }

    public function setModalId(string $modal_id)
    {
        $this->modal_id = $modal_id;
    }

    public function getHeaderText(): string
    {
        return $this->header_text;
    }

    public function setHeaderText(string $header_text)
    {
        $this->header_text = $header_text;
    }

    public function getConfirmationText(): string
    {
        return $this->confirmation_text;
    }

    public function setConfirmationText(string $confirmation_text)
    {
        $this->confirmation_text = $confirmation_text;
    }

    public function getConfirmationCheckboxName(): string
    {
        return $this->confirmation_checkbox_name;
    }

    public function setConfirmationCheckboxName(string $confirmation_checkbox_name)
    {
        $this->confirmation_checkbox_name = $confirmation_checkbox_name;
    }

    public function getConfirmationCheckboxLabel(): string
    {
        return $this->confirmation_checkbox_label;
    }

    public function setConfirmationCheckboxLabel(string $confirmation_checkbox_label)
    {
        $this->confirmation_checkbox_label = $confirmation_checkbox_label;
    }

    /**
     * @return \ILIAS\UI\Component\Button\Standard[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function addButton(StandardButton|PrimaryButton|ilLinkButton $button)
    {
        $this->buttons[] = $button;
    }

    /**
     * @return ilHiddenInputGUI[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter(ilHiddenInputGUI $hidden_input_gui)
    {
        $this->parameters[] = $hidden_input_gui;
    }

    public function isConfirmationCheckboxRequired(): bool
    {
        return strlen($this->getConfirmationCheckboxName()) && strlen($this->getConfirmationCheckboxLabel());
    }

    public function buildBody(): string
    {
        $tpl = new ilTemplate('tpl.tst_player_confirmation_modal.html', true, true, 'Modules/Test');

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

        foreach ($this->getButtons() as $button) {
            $tpl->setCurrentBlock('buttons');
            if ($button instanceof StandardButton || $button instanceof PrimaryButton) {
                $button_str = $this->ui_renderer->render($button);
            } elseif ($button instanceof ilLinkButton) {
                $button_str = $button->render();
            }

            $tpl->setVariable('BUTTON', $button_str);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('CONFIRMATION_TEXT', $this->getConfirmationText());

        return $tpl->get();
    }

    public function getHTML(): string
    {
        $modal = ilModalGUI::getInstance();
        $modal->setId($this->getModalId());
        $modal->setHeading($this->getHeaderText());
        $modal->setBody($this->buildBody());
        return $modal->getHTML();
    }
}
