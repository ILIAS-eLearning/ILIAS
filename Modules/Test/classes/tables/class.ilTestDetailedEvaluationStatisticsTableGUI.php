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

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_COUNTER', $a_set['counter']);
        $this->tpl->setVariable('VAL_QUESTION_ID_TXT', $a_set['id_txt']);
        $this->tpl->setVariable('VAL_QUESTION_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_QUESTION_TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_POINTS', $a_set['points']);
    }
}
