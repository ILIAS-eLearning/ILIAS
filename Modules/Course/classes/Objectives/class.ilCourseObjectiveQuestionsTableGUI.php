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

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ModulesCourse
 */
class ilCourseObjectiveQuestionsTableGUI extends ilTable2GUI
{
    protected HTTPServices $http;
    protected Refinery $refinery;
    protected ilObject $course_obj;


    public function __construct(object $a_parent_obj, ilObject $a_course_obj)
    {
        global $DIC;

        $this->course_obj = $a_course_obj;
        parent::__construct($a_parent_obj, 'questionOverview');

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule('crs');
        $this->setFormName('questions');
        $this->addColumn($this->lng->txt('title'), 'title', '33%');
        $this->addColumn($this->lng->txt('crs_objective_self_assessment'), 'self', '33%%');
        $this->addColumn($this->lng->txt('crs_objective_final_test'), 'final', '33%');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.crs_questions_table_row.html", "Modules/Course");
        $this->disable('sort');
        $this->enable('header');
        $this->disable('numinfo');
        $this->enable('select_all');
        $this->setLimit(200);
        $this->addCommandButton('saveQuestionOverview', $this->lng->txt('save'));
    }

    protected function fillRow(array $a_set): void
    {
        static $row_counter = 1;

        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description']) !== 0) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }

        foreach ($a_set['self_tests'] as $tst) {
            foreach ($tst['questions'] as $qst) {
                $this->tpl->setCurrentBlock('self_qst');
                $this->tpl->setVariable('SELF_QST_TITLE', $qst['title']);
                if (strlen($qst['description']) !== 0) {
                    $this->tpl->setVariable('SELF_QST_DESCRIPTION', $qst['description']);
                }
                $this->tpl->setVariable('SELF_QST_POINTS', $qst['points']);
                $this->tpl->setVariable('SELF_QST_TXT_POINTS', $this->lng->txt('crs_objective_points'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('self_tst');
            $this->tpl->setVariable('SELF_TST_TITLE', $tst['title']);
            if (strlen($tst['description']) !== 0) {
                $this->tpl->setVariable('SELF_TST_DESC', $tst['description']);
            }
            $this->tpl->setVariable('SELF_TYPE_IMG', ilUtil::getImagePath('icon_tst.svg'));
            $this->tpl->setVariable('SELF_TYPE_ALT', $this->lng->txt('obj_tst'));
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['self_tests']) > 0) {
            $this->tpl->setVariable('SELF_TXT_ALL_POINTS', $this->lng->txt('crs_objective_all_points'));
            $this->tpl->setVariable('SELF_TXT_POINTS', $this->lng->txt('crs_objective_points'));
            $this->tpl->setVariable('SELF_TXT_REQ_POINTS', $this->lng->txt('crs_obj_required_points'));
            $this->tpl->setVariable('SELF_POINTS', $a_set['self_max_points']);
            $this->tpl->setVariable('SELF_ID', $a_set['id']);
            $this->tpl->setVariable('SELF_LIMIT', $a_set['self_limit']);
        }

        foreach ($a_set['final_tests'] as $tst) {
            foreach ($tst['questions'] as $qst) {
                $this->tpl->setCurrentBlock('final_qst');
                $this->tpl->setVariable('FINAL_QST_TITLE', $qst['title']);
                if (strlen($qst['description']) !== 0) {
                    $this->tpl->setVariable('FINAL_QST_DESCRIPTION', $qst['description']);
                }
                $this->tpl->setVariable('FINAL_QST_POINTS', $qst['points']);
                $this->tpl->setVariable('FINAL_QST_TXT_POINTS', $this->lng->txt('crs_objective_points'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('final_tst');
            $this->tpl->setVariable('FINAL_TST_TITLE', $tst['title']);
            if (strlen($tst['description']) !== 0) {
                $this->tpl->setVariable('FINAL_TST_DESC', $tst['description']);
            }
            $this->tpl->setVariable('FINAL_TYPE_IMG', ilUtil::getImagePath('icon_tst.svg'));
            $this->tpl->setVariable('FINAL_TYPE_ALT', $this->lng->txt('obj_tst'));
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['final_tests']) > 0) {
            $this->tpl->setVariable('FINAL_TXT_ALL_POINTS', $this->lng->txt('crs_objective_all_points'));
            $this->tpl->setVariable('FINAL_TXT_POINTS', $this->lng->txt('crs_objective_points'));
            $this->tpl->setVariable('FINAL_TXT_REQ_POINTS', $this->lng->txt('crs_obj_required_points'));
            $this->tpl->setVariable('FINAL_POINTS', $a_set['final_max_points']);
            $this->tpl->setVariable('FINAL_ID', $a_set['id']);
            $this->tpl->setVariable('FINAL_LIMIT', $a_set['final_limit']);
        }
    }

    public function parse(array $a_objective_ids): void
    {
        $post_self_limits = [];
        if ($this->http->wrapper()->post()->has('self')) {
            $post_self_limits = $this->http->wrapper()->post()->retrieve(
                'self',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->float()
                )
            );
        }
        $post_final_limits = [];
        if ($this->http->wrapper()->post()->has('final')) {
            $post_final_limits = $this->http->wrapper()->post()->retrieve(
                'final',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->float()
                )
            );
        }

        $objectives = array();
        foreach ($a_objective_ids as $objective_id) {
            $objective = new ilCourseObjective($this->course_obj, $objective_id);

            // Self assessment tests
            $question_obj = new ilCourseObjectiveQuestion($objective_id);

            $tests = array();
            foreach ($question_obj->getSelfAssessmentTests() as $tmp_test) {
                if (isset($post_self_limits[$objective_id])) {
                    $objective_data['self_limit'] = (float) $post_self_limits[$objective_id];
                } else {
                    $objective_data['self_limit'] = $tmp_test['limit'];
                }
                $questions = array();
                foreach ($question_obj->getQuestionsOfTest($tmp_test['obj_id']) as $tmp_question) {
                    $qst['title'] = $tmp_question['title'];
                    $qst['description'] = $tmp_question['description'];
                    $qst['points'] = $tmp_question['points'];

                    $questions[] = $qst;
                }
                $tst['questions'] = $questions;
                $tst['title'] = ilObject::_lookupTitle($tmp_test['obj_id']);
                $tst['description'] = ilObject::_lookupDescription($tmp_test['obj_id']);

                $tests[] = $tst;
            }
            $objective_data['self_tests'] = $tests;
            $objective_data['self_max_points'] = $question_obj->getSelfAssessmentPoints();

            // Final tests
            $tests = array();
            foreach ($question_obj->getFinalTests() as $tmp_test) {
                if (isset($post_final_limits[$objective_id])) {
                    $objective_data['final_limit'] = (float) $post_final_limits[$objective_id];
                } else {
                    $objective_data['final_limit'] = $tmp_test['limit'];
                }

                $questions = array();
                foreach ($question_obj->getQuestionsOfTest($tmp_test['obj_id']) as $tmp_question) {
                    $qst['title'] = $tmp_question['title'];
                    $qst['description'] = $tmp_question['description'];
                    $qst['points'] = $tmp_question['points'];

                    $questions[] = $qst;
                }
                $tst['questions'] = $questions;
                $tst['title'] = ilObject::_lookupTitle($tmp_test['obj_id']);
                $tst['description'] = ilObject::_lookupDescription($tmp_test['obj_id']);

                $tests[] = $tst;
            }

            $objective_data['final_tests'] = $tests;
            $objective_data['final_max_points'] = $question_obj->getFinalTestPoints();

            $objective_data['id'] = $objective_id;
            $objective_data['title'] = $objective->getTitle();

            $objective_data['description'] = $objective->getDescription();

            $objectives[] = $objective_data;
        }
        $this->setData($objectives);
    }
}
