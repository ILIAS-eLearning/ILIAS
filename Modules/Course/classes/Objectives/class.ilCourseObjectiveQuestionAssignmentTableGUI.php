<?php

declare(strict_types=0);
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
 * TableGUI for question assignments of course objectives
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilCourseObjectiveQuestionAssignmentTableGUI extends ilTable2GUI
{
    private ilLOSettings $settings;
    private int $mode = 0;
    private ?ilCourseObjectiveQuestion $objective_qst_obj = null;

    private int $objective_id = 0;
    private ilObject $course_obj;

    protected ilObjectDefinition $objDefinition;
    protected ilTree $tree;

    public function __construct(object $a_parent_obj, ilObject $a_course_obj, int $a_objective_id, int $a_mode)
    {
        global $DIC;

        $this->objective_id = $a_objective_id;
        $this->course_obj = $a_course_obj;
        $this->settings = ilLOSettings::getInstanceByObjId($this->course_obj->getId());
        $this->objDefinition = $DIC['objDefinition'];
        $this->tree = $DIC->repositoryTree();

        parent::__construct($a_parent_obj, 'materialAssignment');
        $this->lng->loadLanguageModule('crs');

        $this->setFormName('assignments');
        $this->addColumn($this->lng->txt('type'), 'type', "20px");
        $this->addColumn($this->lng->txt('title'), 'title', '');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.crs_objective_list_questions_row.html", "Modules/Course");
        $this->disable('sort');
        $this->disable('header');
        $this->disable('numinfo');
        $this->disable('select_all');

        #$this->setDefaultOrderField('title');
        $this->setLimit(200);

        $this->mode = $a_mode;
        switch ($a_mode) {
            case ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT:
                $this->addCommandButton('updateSelfAssessmentAssignment', $this->lng->txt('crs_wiz_next'));
                break;

            case ilCourseObjectiveQuestion::TYPE_FINAL_TEST:
                $this->addCommandButton('updateFinalTestAssignment', $this->lng->txt('crs_wiz_next'));
                break;
        }

        $this->initQuestionAssignments();
    }

    public function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    protected function fillRow(array $a_set): void
    {
        foreach ($a_set['sub'] as $sub_data) {
            if ($sub_data['description']) {
                $this->tpl->setVariable('QST_DESCRIPTION', $sub_data['description']);
            }
            if ($sub_data['qst_txt']) {
                $txt = $sub_data['qst_txt'];
                if ($sub_data['qst_points']) {
                    $this->lng->loadLanguageModule('assessment');
                    $txt .= (' (' . $sub_data['qst_points'] . ' ' . $this->lng->txt('points') . ')');
                }

                $this->tpl->setVariable('QST_DESCRIPTION', $txt);
            }
            $this->tpl->setCurrentBlock('qst');
            $this->tpl->setVariable('QST_TITLE', $sub_data['title']);
            $this->tpl->setVariable('QST_ID', $a_set['id'] . '_' . $sub_data['id']);

            switch ($this->mode) {
                case ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT:
                    if ($this->objective_qst_obj->isSelfAssessmentQuestion($sub_data['id'])) {
                        $this->tpl->setVariable('QST_CHECKED', 'checked="checked"');
                    }
                    break;

                case ilCourseObjectiveQuestion::TYPE_FINAL_TEST:
                    if ($this->objective_qst_obj->isFinalTestQuestion($sub_data['id'])) {
                        $this->tpl->setVariable('QST_CHECKED', 'checked="checked"');
                    }
                    break;
            }
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['sub'])) {
            $this->tpl->setVariable('TXT_QUESTIONS', $this->lng->txt('objs_qst'));
        }
        $this->tpl->setVariable('VAL_ID', $a_set['id']);

        $this->tpl->setVariable('ROW_TYPE_IMG', ilObject::_getIcon($a_set['obj_id'], "tiny", $a_set['type']));
        $this->tpl->setVariable('ROW_TYPE_ALT', $this->lng->txt('obj_' . $a_set['type']));

        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
    }

    public function parse(array $a_assignable): void
    {
        $a_assignable = $this->getTestNode();
        $tests = array();
        foreach ($a_assignable as $node) {
            $tmp_data = array();
            $subobjects = array();

            if (!$tmp_tst = ilObjectFactory::getInstanceByRefId((int) $node['ref_id'], false)) {
                continue;
            }

            foreach ($qst = $this->sortQuestions($tmp_tst->getAllQuestions()) as $question_data) {
                $tmp_question = ilObjTest::_instanciateQuestion($question_data['question_id']);
                #$sub['qst_txt'] = $tmp_question->_getQuestionText($question_data['question_id']);
                $sub['qst_txt'] = '';
                $sub['qst_points'] = assQuestion::_getMaximumPoints($question_data['question_id']);

                $sub['title'] = $tmp_question->getTitle();
                $sub['description'] = $tmp_question->getComment();
                $sub['id'] = $question_data['question_id'];

                $subobjects[] = $sub;
            }
            $tmp_data['title'] = $node['title'];
            $tmp_data['description'] = $node['description'];
            $tmp_data['type'] = $node['type'];
            $tmp_data['id'] = $node['child'];
            $tmp_data['obj_id'] = $node['obj_id'];
            $tmp_data['sub'] = $subobjects;
            $tests[] = $tmp_data;
        }
        $this->setData($tests);
    }

    protected function getTestNode(): array
    {
        if ($this->mode == ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT) {
            $tst_ref_id = $this->getSettings()->getInitialTest();
            if ($tst_ref_id) {
                return array($this->tree->getNodeData($tst_ref_id));
            }
        }
        if ($this->mode == ilCourseObjectiveQuestion::TYPE_FINAL_TEST) {
            $tst_ref_id = $this->getSettings()->getQualifiedTest();
            if ($tst_ref_id) {
                return array($this->tree->getNodeData($tst_ref_id));
            }
        }
        return [];
    }

    // end-patch lok

    protected function initQuestionAssignments(): void
    {
        $this->objective_qst_obj = new ilCourseObjectiveQuestion($this->objective_id);
    }

    protected function sortQuestions(array $a_qst_ids): array
    {
        return ilArrayUtil::sortArray($a_qst_ids, 'title', 'asc');
    }
}
