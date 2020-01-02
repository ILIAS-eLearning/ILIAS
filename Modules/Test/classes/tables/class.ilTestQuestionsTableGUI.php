<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @author Björn Heyser <bheyser@databay.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestQuestionsTableGUI extends ilTable2GUI
{
    /**
     * @var bool
     */
    protected $questionTitleLinksEnabled = false;
    
    /**
     * @var bool
     */
    protected $questionRemoveRowButtonEnabled = false;
    
    /**
     * @var bool
     */
    protected $questionManagingEnabled = false;
    
    /**
     * @var bool
     */
    protected $positionInsertCommandsEnabled = false;
    
    /**
     * @var bool
     */
    protected $questionPositioningEnabled = false;
    
    /**
     * @var bool
     */
    protected $obligatoryQuestionsHandlingEnabled = false;
    
    /**
     * @var int
     */
    protected $totalPoints = 0;
    
    /**
     * @var string
     */
    protected $totalWorkingTime = '';
    
    /**
     * @var int
     */
    private $position = 0;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $parentRefId)
    {
        $this->setId('tst_qst_lst_' . $parentRefId);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setFormName('questionbrowser');
        $this->setStyle('table', 'fullwidth');

        $this->setExternalSegmentation(true);

        $this->setRowTemplate("tpl.il_as_tst_questions_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->disable('sort');
        $this->enable('header');
        
        $this->setShowRowsSelector(false);
    }
    
    /**
     * @return array
     */
    public function getSelectableColumns()
    {
        $cols = array(
            'qid'         => array('txt' => $this->lng->txt('question_id'), 'default' => true),
            'description' => array('txt' => $this->lng->txt('description'), 'default' => false),
            'author'      => array('txt' => $this->lng->txt('author'), 'default' => false),
            'working_time'=> array('txt' => $this->lng->txt('working_time'), 'default' => false)
        );
        
        return $cols;
    }
    
    /**
     *
     */
    public function init()
    {
        $this->initColumns();
        $this->initCommands();

        if ($this->isQuestionManagingEnabled()) {
            $this->setSelectAllCheckbox('q_id');
        }
    }
    
    /**
     *
     */
    protected function initColumns()
    {
        if ($this->isCheckboxColumnRequired()) {
            $this->addColumn('', 'f', '1%', true);
        }
        
        if ($this->isQuestionPositioningEnabled()) {
            $this->addColumn('', 'f', '1%');
        }

        if ($this->isColumnSelected('qid')) {
            $this->addColumn($this->lng->txt('question_id'), 'qid', '');
        }

        $this->addColumn($this->lng->txt("tst_question_title"), 'title', '');

        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            $this->addColumn($this->lng->txt("obligatory"), 'obligatory', '');
        }
        
        if ($this->isColumnSelected('description')) {
            $this->addColumn($this->lng->txt('description'), 'description', '');
        }
        
        $this->addColumn($this->lng->txt("tst_question_type"), 'type', '');
        $this->addColumn($this->buildPointsHeader(), '', '');
        
        if ($this->isColumnSelected('author')) {
            $this->addColumn($this->lng->txt('author'), 'author', '');
        }
        if ($this->isColumnSelected('working_time')) {
            $this->addColumn($this->buildWorkingTimeHeader(), 'working_time', '');
        }
        
        $this->addColumn($this->lng->txt('qpl'), 'qpl', '');
        
        if ($this->isQuestionRemoveRowButtonEnabled()) {
            $this->addColumn('', '', '1%');
        }
    }
    
    protected function initCommands()
    {
        if ($this->isQuestionManagingEnabled()) {
            $this->addMultiCommand('removeQuestions', $this->lng->txt('remove_question'));
            $this->addMultiCommand('moveQuestions', $this->lng->txt('move'));
        }
        
        if ($this->isPositionInsertCommandsEnabled()) {
            $this->addMultiCommand('insertQuestionsBefore', $this->lng->txt('insert_before'));
            $this->addMultiCommand('insertQuestionsAfter', $this->lng->txt('insert_after'));
        }
        
        if ($this->isQuestionManagingEnabled()) {
            $this->addMultiCommand('copyQuestion', $this->lng->txt('copy'));
            $this->addMultiCommand('copyAndLinkToQuestionpool', $this->lng->txt('copy_and_link_to_questionpool'));
        }
        
        if ($this->isTableSaveCommandRequired()) {
            $this->addCommandButton('saveOrderAndObligations', $this->buildTableSaveCommandLabel());
        }
    }
    
    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        if ($this->isCheckboxColumnRequired()) {
            $this->tpl->setVariable("CHECKBOX_QID", $data['question_id']);
        }
        
        if ($this->isQuestionPositioningEnabled()) {
            $this->position += 10;
            $inputField = $this->buildPositionInput($data['question_id'], $this->position);
            
            $this->tpl->setVariable("QUESTION_POSITION", $inputField);
            $this->tpl->setVariable("POSITION_QID", $data['question_id']);
        }
        
        if ($this->isColumnSelected('qid')) {
            $this->tpl->setVariable("QUESTION_ID_PRESENTATION", $data['question_id']);
        }
        
        if ($this->isQuestionTitleLinksEnabled()) {
            $this->tpl->setVariable("QUESTION_TITLE", $this->buildQuestionTitleLink($data));
        } else {
            $this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
        }
        
        if (!$data['complete']) {
            $this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
            $this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
        }
        
        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            $this->tpl->setVariable("QUESTION_OBLIGATORY", $this->buildObligatoryColumnContent($data));
        }
        
        if ($this->isColumnSelected('description')) {
            $this->tpl->setVariable("QUESTION_COMMENT", $data["description"] ? $data["description"] : '&nbsp;');
        }
        
        $this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
        $this->tpl->setVariable("QUESTION_POINTS", $data["points"]);
        
        if ($this->isColumnSelected('author')) {
            $this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
        }

        if ($this->isColumnSelected('working_time')) {
            $this->tpl->setVariable("QUESTION_WORKING_TIME", $data["working_time"]);
        }

        if (ilObject::_lookupType($data["orig_obj_fi"]) == 'qpl') {
            $this->tpl->setVariable("QUESTION_POOL", ilObject::_lookupTitle($data["orig_obj_fi"]));
        } else {
            $this->tpl->setVariable("QUESTION_POOL", $this->lng->txt('tst_question_not_from_pool_info'));
        }
        
        if ($this->isQuestionRemoveRowButtonEnabled()) {
            $this->tpl->setVariable('ROW_ACTIONS', $this->buildQuestionRemoveButton($data));
        }
    }
    
    /**
     * @param array $rowData
     * @return string
     */
    protected function buildQuestionRemoveButton(array $rowData) : string
    {
        $this->ctrl->setParameter($this->getParentObject(), 'removeQid', $rowData['question_id']);
        $removeUrl = $this->ctrl->getLinkTarget($this->getParentObject(), $this->getParentCmd());
        $this->ctrl->setParameter($this->getParentObject(), 'removeQid', '');

        $button = ilLinkButton::getInstance();
        $button->setCaption('remove_question');
        $button->setUrl($removeUrl);
        
        return $button->render();
    }
    
    /**
     * @param array $rowData
     * @return string
     */
    protected function buildQuestionTitleLink(array $rowData) : string
    {
        $this->ctrl->setParameter(
            $this->getParentObject(),
            'eqpl',
            current(ilObject::_getAllReferences($rowData['obj_fi']))
        );
        
        $this->ctrl->setParameter(
            $this->getParentObject(),
            'eqid',
            $rowData['question_id']
        );
        
        $questionHref = $this->ctrl->getLinkTarget($this->getParentObject(), $this->getParentCmd());
        
        $this->ctrl->setParameter($this->getParentObject(), 'eqpl', '');
        $this->ctrl->setParameter($this->getParentObject(), 'eqid', '');
        
        return '<a href="' . $questionHref . '">' . $rowData["title"] . '</a>';
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function buildObligatoryColumnContent(array $rowData) : string
    {
        if (!$rowData['obligationPossible']) {
            return '&nbsp;';
        }
        
        if ($rowData['obligatory'] && !$this->isQuestionManagingEnabled()) {
            // obligatory icon
            return ilGlyphGUI::get(ilGlyphGUI::EXCLAMATION, $this->lng->txt('question_obligatory'));
        }
        
        $checkedAttr = $rowData['obligatory'] ? 'checked="checked"' : '';
        return '<input type="checkbox" name="obligatory[' . $rowData['question_id'] . ']" value="1" ' . $checkedAttr . ' />';
    }
    
    /**
     * @param $questionId
     * @param $position
     * @return string
     */
    protected function buildPositionInput($questionId, $position) : string
    {
        return '<input type="text" name="order[q_' . $questionId . ']" value="' . $position . '" maxlength="3" size="3" />';
    }
    
    /**
     * @return string
     */
    protected function buildTableSaveCommandLabel() : string
    {
        if ($this->isObligatoryQuestionsHandlingEnabled() && $this->isQuestionPositioningEnabled()) {
            return $this->lng->txt('saveOrderAndObligations');
        }
        
        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            return $this->lng->txt('saveObligations');
        }
        
        if ($this->isQuestionPositioningEnabled()) {
            return $this->lng->txt('saveOrder');
        }
        
        return $this->lng->txt('save');
    }
    
    /**
     * @return string
     */
    protected function buildPointsHeader() : string
    {
        if ($this->getTotalPoints()) {
            return $this->lng->txt('points') . ' (' . $this->getTotalPoints() . ')';
        }
        
        return $this->lng->txt('points');
    }
    
    /**
     * @return string
     */
    protected function buildWorkingTimeHeader() : string
    {
        if (strlen($this->getTotalWorkingTime())) {
            return $this->lng->txt('working_time') . ' (' . $this->getTotalWorkingTime() . ')';
        }
        
        return $this->lng->txt('working_time');
    }
    
    /**
     * @return bool
     */
    protected function isTableSaveCommandRequired() : bool
    {
        if (!$this->isQuestionManagingEnabled()) {
            return false;
        }
        
        return $this->isQuestionPositioningEnabled() || $this->isObligatoryQuestionsHandlingEnabled();
    }
    
    /**
     * @return bool
     */
    protected function isCheckboxColumnRequired() : bool
    {
        return $this->isQuestionManagingEnabled() || $this->isPositionInsertCommandsEnabled();
    }
    
    /**
     * @return bool
     */
    public function isQuestionManagingEnabled() : bool
    {
        return $this->questionManagingEnabled;
    }
    
    /**
     * @param bool $questionManagingEnabled
     */
    public function setQuestionManagingEnabled(bool $questionManagingEnabled)
    {
        $this->questionManagingEnabled = $questionManagingEnabled;
    }
    
    /**
     * @return bool
     */
    public function isPositionInsertCommandsEnabled() : bool
    {
        return $this->positionInsertCommandsEnabled;
    }
    
    /**
     * @param bool $positionInsertCommandsEnabled
     */
    public function setPositionInsertCommandsEnabled(bool $positionInsertCommandsEnabled)
    {
        $this->positionInsertCommandsEnabled = $positionInsertCommandsEnabled;
    }
    
    /**
     * @return bool
     */
    public function isQuestionPositioningEnabled() : bool
    {
        return $this->questionPositioningEnabled;
    }
    
    /**
     * @param bool $questionPositioningEnabled
     */
    public function setQuestionPositioningEnabled(bool $questionPositioningEnabled)
    {
        $this->questionPositioningEnabled = $questionPositioningEnabled;
    }
    
    /**
     * @return bool
     */
    public function isObligatoryQuestionsHandlingEnabled() : bool
    {
        return $this->obligatoryQuestionsHandlingEnabled;
    }
    
    /**
     * @param bool $obligatoryQuestionsHandlingEnabled
     */
    public function setObligatoryQuestionsHandlingEnabled(bool $obligatoryQuestionsHandlingEnabled)
    {
        $this->obligatoryQuestionsHandlingEnabled = $obligatoryQuestionsHandlingEnabled;
    }
    
    /**
     * @return int
     */
    public function getTotalPoints() : int
    {
        return $this->totalPoints;
    }
    
    /**
     * @param int $totalPoints
     */
    public function setTotalPoints(int $totalPoints)
    {
        $this->totalPoints = $totalPoints;
    }
    
    /**
     * @return string
     */
    public function getTotalWorkingTime() : string
    {
        return $this->totalWorkingTime;
    }
    
    /**
     * @param string $totalWorkingTime
     */
    public function setTotalWorkingTime(string $totalWorkingTime)
    {
        $this->totalWorkingTime = $totalWorkingTime;
    }
    
    /**
     * @return bool
     */
    public function isQuestionTitleLinksEnabled() : bool
    {
        return $this->questionTitleLinksEnabled;
    }
    
    /**
     * @param bool $questionTitleLinksEnabled
     */
    public function setQuestionTitleLinksEnabled(bool $questionTitleLinksEnabled)
    {
        $this->questionTitleLinksEnabled = $questionTitleLinksEnabled;
    }
    
    /**
     * @return bool
     */
    public function isQuestionRemoveRowButtonEnabled() : bool
    {
        return $this->questionRemoveRowButtonEnabled;
    }
    
    /**
     * @param bool $questionRemoveRowButtonEnabled
     */
    public function setQuestionRemoveRowButtonEnabled(bool $questionRemoveRowButtonEnabled)
    {
        $this->questionRemoveRowButtonEnabled = $questionRemoveRowButtonEnabled;
    }
}
