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
    private \ILIAS\DI\UIServices $ui;

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
     * @var \ILIAS\UI\Component\Button\Standard[]
     */
    protected $buttons = array();

    /**
     * @var ilHiddenInputGUI[]
     */
    protected $parameters = array();

    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
    }

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
     * @return \ILIAS\UI\Component\Button\Standard[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param \ILIAS\UI\Component\Button\Standard|ilLinkButton $button
     */
    public function addButton(\ILIAS\UI\Component\Button\Standard|\ILIAS\UI\Implementation\Component\Button\Primary|ilLinkButton $button)
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
            if ($button instanceof \ILIAS\UI\Component\Button\Standard || $button instanceof \ILIAS\UI\Implementation\Component\Button\Primary) {
                $button_str = $this->ui->renderer()->render($button);
            } elseif ($button instanceof ilLinkButton) {
                $button_str = $button->render();
            }

            $tpl->setVariable('BUTTON', $button_str);
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
}
