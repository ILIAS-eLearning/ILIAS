<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilTestDetailedEvaluationStatisticsTableGUI
 */
class ilTestDetailedEvaluationStatisticsTableGUI extends ilTable2GUI
{
    /**
     * @inheritdoc
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
    {
        global $DIC;

        $this->setId('ass_eval_det_' . $a_template_context);
        parent::__construct($a_parent_obj, $a_parent_cmd, '');
        
        $this->setFormAction($DIC->ctrl()->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->setRowTemplate('tpl.table_evaluation_detail_row.html', 'Modules/Test');
        $this->setShowRowsSelector(false);
        $this->disable('sort');
        $this->disable('header');
        $this->setLimit(PHP_INT_MAX);
    }

    /**
     * @inheritdoc
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_COUNTER', $a_set['counter']);
        $this->tpl->setVariable('VAL_QUESTION_ID_TXT', $a_set['id_txt']);
        $this->tpl->setVariable('VAL_QUESTION_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_QUESTION_TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_POINTS', $a_set['points']);
    }
}
