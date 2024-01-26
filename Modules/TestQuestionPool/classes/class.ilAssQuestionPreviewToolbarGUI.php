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

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionPreviewToolbarGUI extends ilToolbarGUI
{
    /**
     * @var ilLanguage
     */
    public $lng = null;

    private $resetPreviewCmd;
    /**
     * @var null|string
     */
    private $editQuestionCmd;
    /**
     * @var null|string
     */
    private $editPageCmd = null;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;

        parent::__construct();
    }

    public function build()
    {
        // Edit Question
        if ($this->getEditQuestionCmd() !== null) {
            $button_edit_question = ilLinkButton::getInstance();
            $button_edit_question->setUrl($this->getEditQuestionCmd());
            $button_edit_question->setPrimary(true);
            $button_edit_question->setCaption('edit_question');
            $this->addButtonInstance($button_edit_question);
        }
        // Edit Page
        if ($this->getEditPageCmd() !== null) {
            $button_edit_page = ilLinkButton::getInstance();
            $button_edit_page->setUrl($this->getEditPageCmd());
            $button_edit_page->setCaption('edit_page');
            $this->addButtonInstance($button_edit_page);
        }

        // Reset Preview
        $this->addFormButton($this->lng->txt('qpl_reset_preview'), $this->getResetPreviewCmd(), '', false);
    }

    public function setResetPreviewCmd($resetPreviewCmd)
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
    public function setEditQuestionCmd($editQuestionCmd) : void
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
    public function setEditPageCmd($editPageCmd) : void
    {
        $this->editPageCmd = $editPageCmd;
    }
}
