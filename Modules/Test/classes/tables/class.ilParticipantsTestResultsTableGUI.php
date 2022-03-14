<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 * @ingroup ModulesTest
 */

class ilParticipantsTestResultsTableGUI extends ilTable2GUI
{
    protected $accessResultsCommandsEnabled = false;
    protected $manageResultsCommandsEnabled = false;
    
    protected $anonymity;
    
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->setId('tst_participants_' . $a_parent_obj->getTestObj()->getRefId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        
        $this->setStyle('table', 'fullwidth');
        
        $this->setFormName('partResultsForm');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->setRowTemplate("tpl.il_as_tst_scorings_row.html", "Modules/Test");
        
        $this->enable('header');
        $this->enable('sort');
        
        $this->setSelectAllCheckbox('chbUser');
        
        $this->setDefaultOrderField('name');
        $this->setDefaultOrderDirection('asc');
    }
    
    /**
     * @return bool
     */
    public function isAccessResultsCommandsEnabled() : bool
    {
        return $this->accessResultsCommandsEnabled;
    }
    
    /**
     * @param bool $accessResultsCommandsEnabled
     */
    public function setAccessResultsCommandsEnabled($accessResultsCommandsEnabled)
    {
        $this->accessResultsCommandsEnabled = $accessResultsCommandsEnabled;
    }
    
    /**
     * @return bool
     */
    public function isManageResultsCommandsEnabled() : bool
    {
        return $this->manageResultsCommandsEnabled;
    }
    
    /**
     * @param bool $manageResultsCommandsEnabled
     */
    public function setManageResultsCommandsEnabled($manageResultsCommandsEnabled)
    {
        $this->manageResultsCommandsEnabled = $manageResultsCommandsEnabled;
    }
    
    /**
     * @return mixed
     */
    public function getAnonymity()
    {
        return $this->anonymity;
    }
    
    /**
     * @param mixed $anonymity
     */
    public function setAnonymity($anonymity)
    {
        $this->anonymity = $anonymity;
    }
    
    /**
     * @param string $a_field
     * @return bool
     */
    public function numericOrdering(string $a_field) : bool
    {
        return in_array($a_field, array(
            'scored_pass', 'answered_questions', 'points', 'percent_result'
        ));
    }
    
    public function init()
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->setShowRowsSelector(true);
        }
        
        $this->initColumns();
        $this->initCommands();
        $this->initFilter();
    }
    
    public function initColumns()
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->addColumn('', '', '1%');
        }
        
        $this->addColumn($this->lng->txt("name"), 'name');
        $this->addColumn($this->lng->txt("login"), 'login');
        
        $this->addColumn($this->lng->txt("tst_tbl_col_scored_pass"), 'scored_pass');
        $this->addColumn($this->lng->txt("tst_tbl_col_pass_finished"), 'pass_finished');
        
        $this->addColumn($this->lng->txt("tst_tbl_col_answered_questions"), 'answered_questions');
        $this->addColumn($this->lng->txt("tst_tbl_col_reached_points"), 'reached_points');
        $this->addColumn($this->lng->txt("tst_tbl_col_percent_result"), 'percent_result');
        
        $this->addColumn($this->lng->txt("tst_tbl_col_passed_status"), 'passed_status');
        $this->addColumn($this->lng->txt("tst_tbl_col_final_mark"), 'final_mark');
        
        if ($this->isActionsColumnRequired()) {
            $this->addColumn('', '', '');
        }
    }
    
    public function initCommands()
    {
        if ($this->isAccessResultsCommandsEnabled() && !$this->getAnonymity()) {
            $this->addMultiCommand('showPassOverview', $this->lng->txt('show_pass_overview'));
            $this->addMultiCommand('showUserAnswers', $this->lng->txt('show_user_answers'));
            $this->addMultiCommand('showDetailedResults', $this->lng->txt('show_detailed_results'));
        }
        
        if ($this->isManageResultsCommandsEnabled()) {
            $this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));
        }
    }
    
    public function initFilter() : void
    {
        global $DIC;

        // no filter at all
    }
    
    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set) : void
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->tpl->setCurrentBlock('checkbox_column');
            $this->tpl->setVariable("CHB_ROW_KEY", $a_set['active_id']);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($this->isActionsColumnRequired()) {
            $this->tpl->setCurrentBlock('actions_column');
            $this->tpl->setVariable('ACTIONS', $this->buildActionsMenu($a_set)->getHTML());
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("ROW_KEY", $a_set['active_id']);
        $this->tpl->setVariable("LOGIN", $a_set['login']);
        $this->tpl->setVariable("FULLNAME", $a_set['name']);
        
        $this->tpl->setVariable("SCORED_PASS", $this->buildScoredPassString($a_set));
        $this->tpl->setVariable("PASS_FINISHED", $this->buildPassFinishedString($a_set));

        $this->tpl->setVariable("ANSWERED_QUESTIONS", $this->buildAnsweredQuestionsString($a_set));
        $this->tpl->setVariable("REACHED_POINTS", $this->buildReachedPointsString($a_set));
        $this->tpl->setVariable("PERCENT_RESULT", $this->buildPercentResultString($a_set));
        
        $this->tpl->setVariable("PASSED_STATUS", $this->buildPassedStatusString($a_set));
        $this->tpl->setVariable("FINAL_MARK", $a_set['final_mark']);
    }
    
    /**
     * @param array $data
     * @return ilAdvancedSelectionListGUI
     */
    protected function buildActionsMenu($data) : ilAdvancedSelectionListGUI
    {
        $asl = new ilAdvancedSelectionListGUI();
        
        $this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
        
        if ($this->isAccessResultsCommandsEnabled()) {
            $resultsHref = $this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'outParticipantsResultsOverview');
            $asl->addItem($this->lng->txt('tst_show_results'), $resultsHref, $resultsHref);
        }
        
        return $asl;
    }
    
    /**
     * @return bool
     */
    protected function isActionsColumnRequired() : bool
    {
        if ($this->isAccessResultsCommandsEnabled()) {
            return true;
        }
        
        return false;
    }
    
    protected function isMultiRowSelectionRequired() : bool
    {
        if ($this->isAccessResultsCommandsEnabled() && !$this->getAnonymity()) {
            return true;
        }
        
        if ($this->isManageResultsCommandsEnabled()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function buildPassedStatusString($data) : string
    {
        if ($data['passed_status']) {
            return $this->buildPassedIcon() . ' ' . $this->lng->txt('tst_passed');
        }
        
        return $this->buildFailedIcon() . ' ' . $this->lng->txt('tst_failed');
    }
    
    /**
     * @return string
     */
    protected function buildPassedIcon() : string
    {
        return $this->buildImageIcon(ilUtil::getImagePath("icon_ok.svg"), $this->lng->txt("passed"));
    }
    
    /**
     * @return string
     */
    protected function buildFailedIcon() : string
    {
        return $this->buildImageIcon(ilUtil::getImagePath("icon_not_ok.svg"), $this->lng->txt("failed"));
    }
    
    /**
     * @param $src
     * @param $alt
     * @return string
     */
    protected function buildImageIcon($src, $alt) : string
    {
        return "<img border=\"0\" align=\"middle\" src=\"" . $src . "\" alt=\"" . $alt . "\" />";
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function buildFormattedAccessDate($data) : string
    {
        return ilDatePresentation::formatDate(new ilDateTime($data['access'], IL_CAL_DATETIME));
    }
    
    /**
     * @param array $data
     * @return int
     */
    protected function buildScoredPassString($data)
    {
        return $this->lng->txt('pass') . ' ' . ($data['scored_pass'] + 1);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function buildPassFinishedString($data) : string
    {
        return ilDatePresentation::formatDate(new ilDateTime($data['pass_finished'], IL_CAL_UNIX));
    }

    /**
     * @param array $data
     * @return string
     */
    protected function buildAnsweredQuestionsString($data) : string
    {
        return sprintf(
            $this->lng->txt('tst_answered_questions_of_total'),
            $data['answered_questions'],
            $data['total_questions']
        );
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function buildReachedPointsString($data) : string
    {
        return sprintf(
            $this->lng->txt('tst_reached_points_of_max'),
            $data['reached_points'],
            $data['max_points']
        );
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function buildPercentResultString($data) : string
    {
        return sprintf('%0.2f %%', $data['percent_result'] * 100);
    }
}
