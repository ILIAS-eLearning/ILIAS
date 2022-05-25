<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    private $editQuestionCmd;
    private $editPageCmd;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;

        parent::__construct();
    }
    
    public function build()
    {
        //Edit Question
        $button_edit_question = ilLinkButton::getInstance();
        $button_edit_question->setUrl($this->getEditQuestionCmd());
        $button_edit_question->setPrimary(true);
        $button_edit_question->setCaption('edit_question');
        $this->addButtonInstance($button_edit_question);
        //$this->addFormButton($this->lng->txt('edit_question'), $this->getEditQuestionCmd(), '', true);

        //Edit Page
        $button_edit_page = ilLinkButton::getInstance();
        $button_edit_page->setUrl($this->getEditPageCmd());
        $button_edit_page->setCaption('edit_page');
        $this->addButtonInstance($button_edit_page);
        //$this->addFormButton($this->lng->txt('edit_page'), $this->getEditPageCmd(), '', false);

        //Reset Preview
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
