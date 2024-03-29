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
    /**
     * @var string
     */
    protected $modalId = '';

    /**
     * @var string
     */
    protected $headerText = '';

    /**
     * @var string
     */
    protected $confirmationText = '';

    /**
     * @var string
     */
    protected $confirmationCheckboxName = '';

    /**
     * @var string
     */
    protected $confirmationCheckboxLabel = '';

    /**
     * @var ilLinkButton[]
     */
    protected $buttons = array();

    /**
     * @var ilHiddenInputGUI[]
     */
    protected $parameters = array();

    /**
     * @return string
     */
    public function getModalId(): string
    {
        return $this->modalId;
    }

    /**
     * @param string $modalId
     */
    public function setModalId($modalId)
    {
        $this->modalId = $modalId;
    }

    /**
     * @return string
     */
    public function getHeaderText(): string
    {
        return $this->headerText;
    }

    /**
     * @param string $headerText
     */
    public function setHeaderText($headerText)
    {
        $this->headerText = $headerText;
    }

    /**
     * @return string
     */
    public function getConfirmationText(): string
    {
        return $this->confirmationText;
    }

    /**
     * @param string $confirmationText
     */
    public function setConfirmationText($confirmationText)
    {
        $this->confirmationText = $confirmationText;
    }

    /**
     * @return string
     */
    public function getConfirmationCheckboxName(): string
    {
        return $this->confirmationCheckboxName;
    }

    /**
     * @param string $confirmationCheckboxName
     */
    public function setConfirmationCheckboxName($confirmationCheckboxName)
    {
        $this->confirmationCheckboxName = $confirmationCheckboxName;
    }

    /**
     * @return string
     */
    public function getConfirmationCheckboxLabel(): string
    {
        return $this->confirmationCheckboxLabel;
    }

    /**
     * @param string $confirmationCheckboxLabel
     */
    public function setConfirmationCheckboxLabel($confirmationCheckboxLabel)
    {
        $this->confirmationCheckboxLabel = $confirmationCheckboxLabel;
    }

    /**
     * @return ilLinkButton[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param ilLinkButton $button
     */
    public function addButton(ilLinkButton $button)
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

    /**
     * @param ilHiddenInputGUI $hiddenInputGUI
     */
    public function addParameter(ilHiddenInputGUI $hiddenInputGUI)
    {
        $this->parameters[] = $hiddenInputGUI;
    }

    /**
     * @return bool
     */
    public function isConfirmationCheckboxRequired(): bool
    {
        return strlen($this->getConfirmationCheckboxName()) && strlen($this->getConfirmationCheckboxLabel());
    }

    /**
     * @return string
     */
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
            $tpl->setVariable('BUTTON', $button->render());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('CONFIRMATION_TEXT', $this->getConfirmationText());

        return $tpl->get();
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        $modal = ilModalGUI::getInstance();
        $modal->setId($this->getModalId());
        $modal->setHeading($this->getHeaderText());
        $modal->setBody($this->buildBody());
        return $modal->getHTML();
    }

    /**
     * @param string $buttonId
     * @return ilLinkButton
     */
    public function buildModalButtonInstance($buttonId): ilLinkButton
    {
        $button = ilLinkButton::getInstance();

        $button->setUrl('#');
        $button->setId($buttonId);

        return $button;
    }
}
