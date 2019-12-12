<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseObjectiveQuestionsTableGUI extends ilTable2GUI
{
    protected $course_obj = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param object parent gui object
     * @return
     */
    public function __construct($a_parent_obj, $a_course_obj)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->course_obj = $a_course_obj;
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, 'questionOverview');
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
    
    /**
     * fill row
     *
     * @access protected
     * @param array row data
     * @return
     */
    protected function fillRow($a_set)
    {
        static $row_counter = 1;
        
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
        
        foreach ($a_set['self_tests'] as $tst) {
            foreach ($tst['questions'] as $qst) {
                $this->tpl->setCurrentBlock('self_qst');
                $this->tpl->setVariable('SELF_QST_TITLE', $qst['title']);
                if (strlen($qst['description'])) {
                    $this->tpl->setVariable('SELF_QST_DESCRIPTION', $qst['description']);
                }
                $this->tpl->setVariable('SELF_QST_POINTS', $qst['points']);
                $this->tpl->setVariable('SELF_QST_TXT_POINTS', $this->lng->txt('crs_objective_points'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('self_tst');
            $this->tpl->setVariable('SELF_TST_TITLE', $tst['title']);
            if (strlen($tst['description'])) {
                $this->tpl->setVariable('SELF_TST_DESC', $tst['description']);
            }
            $this->tpl->setVariable('SELF_TYPE_IMG', ilUtil::getImagePath('icon_tst.svg'));
            $this->tpl->setVariable('SELF_TYPE_ALT', $this->lng->txt('obj_tst'));
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['self_tests'])) {
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
                if (strlen($qst['description'])) {
                    $this->tpl->setVariable('FINAL_QST_DESCRIPTION', $qst['description']);
                }
                $this->tpl->setVariable('FINAL_QST_POINTS', $qst['points']);
                $this->tpl->setVariable('FINAL_QST_TXT_POINTS', $this->lng->txt('crs_objective_points'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('final_tst');
            $this->tpl->setVariable('FINAL_TST_TITLE', $tst['title']);
            if (strlen($tst['description'])) {
                $this->tpl->setVariable('FINAL_TST_DESC', $tst['description']);
            }
            $this->tpl->setVariable('FINAL_TYPE_IMG', ilUtil::getImagePath('icon_tst.svg'));
            $this->tpl->setVariable('FINAL_TYPE_ALT', $this->lng->txt('obj_tst'));
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['final_tests'])) {
            $this->tpl->setVariable('FINAL_TXT_ALL_POINTS', $this->lng->txt('crs_objective_all_points'));
            $this->tpl->setVariable('FINAL_TXT_POINTS', $this->lng->txt('crs_objective_points'));
            $this->tpl->setVariable('FINAL_TXT_REQ_POINTS', $this->lng->txt('crs_obj_required_points'));
            $this->tpl->setVariable('FINAL_POINTS', $a_set['final_max_points']);
            $this->tpl->setVariable('FINAL_ID', $a_set['id']);
            $this->tpl->setVariable('FINAL_LIMIT', $a_set['final_limit']);
        }
        
        $this->tpl->setVariable('TST_CSS', ilUtil::switchColor($row_counter++, 'tblrow1', 'tblrow2'));
    }
    
    /**
     * parse
     *
     * @access public
     * @param array array of objective id's
     */
    public function parse($a_objective_ids)
    {
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        
        $objectives = array();
        foreach ($a_objective_ids as $objective_id) {
            $objective = new ilCourseObjective($this->course_obj, $objective_id);
            
            // Self assessment tests
            $question_obj = new ilCourseObjectiveQuestion($objective_id);
            
            $tests = array();
            foreach ($question_obj->getSelfAssessmentTests() as $tmp_test) {
                if (isset($_POST['self'][$objective_id])) {
                    $objective_data['self_limit'] = $_POST['self'][$objective_id];
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
                if (isset($_POST['final'][$objective_id])) {
                    $objective_data['final_limit'] = $_POST['final'][$objective_id];
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
        $this->setData($objectives ? $objectives : array());
    }
}
