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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssQuestionPreviewToolbarGUI extends ilToolbarGUI
{
    private $resetPreviewCmd;
    private $editQuestionCmd;
    private $editPageCmd;
    private ilCtrlInterface $ilCtrl;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
        global $DIC;
        $this->ilCtrl = $DIC->ctrl();

        parent::__construct();
    }

    public function build(): void
    {
        // Edit Question
        if ($this->getEditQuestionCmd() !== null) {
            $action = $this->ilCtrl->getLinkTargetByClass(ilAssQuestionPreviewGUI::class, $this->getEditQuestionCmd());
            $button_edit_question = $this->ui->factory()->button()->primary($this->lng->txt('edit_question'), $action);
            $this->addComponent($button_edit_question);
        }

        // Edit Page
        if ($this->getEditPageCmd() !== null) {
            $action = $this->ilCtrl->getLinkTargetByClass(ilAssQuestionPreviewGUI::class, $this->getEditPageCmd());
            $button_edit_page = $this->ui->factory()->button()->standard($this->lng->txt('edit_page'), $this->getEditPageCmd());
            $this->addComponent($button_edit_page);
        }

        //Reset Preview
        $action = $this->ilCtrl->getLinkTargetByClass(ilAssQuestionPreviewGUI::class, $this->getResetPreviewCmd());
        $button = $this->ui->factory()->button()->standard($this->lng->txt('qpl_reset_preview'), $action);
        $this->addComponent($button);
    }

    public function setResetPreviewCmd($resetPreviewCmd): void
    {
        $this->resetPreviewCmd = $resetPreviewCmd;
    }

    public function getResetPreviewCmd()
    {
        return $this->resetPreviewCmd;
    }

    /**
     * @return mixed
     */
    public function getEditQuestionCmd()
    {
        return $this->editQuestionCmd;
    }

    /**
     * @param mixed $editQuestionCmd
     */
    public function setEditQuestionCmd($editQuestionCmd): void
    {
        $this->editQuestionCmd = $editQuestionCmd;
    }

    /**
     * @return mixed
     */
    public function getEditPageCmd()
    {
        return $this->editPageCmd;
    }

    /**
     * @param mixed $editPageCmd
     */
    public function setEditPageCmd($editPageCmd): void
    {
        $this->editPageCmd = $editPageCmd;
    }
}
