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
include_once './Modules/Course/classes/class.ilCourseObjective.php';
include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';

// begin-patch lok
include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
// end-patch lok

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseObjectivesTableGUI extends ilTable2GUI
{
    protected $course_obj = null;
    
    // begin-patch lok
    protected $settings = null;
    // end-patch lok
    
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
        
        // begin-patch lok
        $this->settings = ilLOSettings::getInstanceByObjId($this->course_obj->getId());
        // end-patch lok
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, 'listObjectives');
        $this->setFormName('objectives');
        $this->addColumn('', 'f', "1px");
        $this->addColumn($this->lng->txt('position'), 'position', '10em');
        $this->addColumn($this->lng->txt('title'), 'title', '20%');
        $this->addColumn($this->lng->txt('crs_objective_assigned_materials'), 'materials');
        // begin-patch lok
        if ($this->getSettings()->worksWithInitialTest()) {
            $this->addColumn($this->lng->txt('crs_objective_self_assessment'), 'self');
        }
        // end-patch lok
        if ($this->getSettings()->getQualifyingTestType() == ilLOSettings::TYPE_QUALIFYING_SELECTED) {
            $this->addColumn($this->lng->txt('crs_objective_tbl_col_final_tsts'), 'final');
        } else {
            $this->addColumn($this->lng->txt('crs_objective_final_test'), 'final');
        }
        $this->addColumn($this->lng->txt('actions'), '5em');
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.crs_objectives_table_row.html", "Modules/Course");
        $this->disable('sort');
        $this->enable('header');
        $this->disable('numinfo');
        $this->enable('select_all');
        // begin-patch lok
        $this->setSelectAllCheckbox('objective');
        // end-patch lok
        $this->setLimit(200);
        
        // begin-patch lo
        $this->addMultiCommand('activateObjectives', $this->lng->txt('set_online'));
        $this->addMultiCommand('deactivateObjectives', $this->lng->txt('set_offline'));
        $this->addMultiCommand('askDeleteObjectives', $this->lng->txt('delete'));
        // end-patch lok
        $this->addCommandButton('saveSorting', $this->lng->txt('sorting_save'));
        // $this->addCommandButton('create',$this->lng->txt('crs_add_objective'));
    }
    
    // begin-patch lok
    /**
     * Get settings
     * @return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    // end-patch lok
    
    
    /**
     * fill row
     *
     * @access protected
     * @param array row data
     * @return
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_POSITION', $a_set['position']);
        
        // begin-patch lok
        if ($a_set['online']) {
            $this->tpl->setVariable('VAL_ONOFFLINE', $this->lng->txt('online'));
            $this->tpl->setVariable('ONOFFLINE_CLASS', 'smallgreen');
        } else {
            $this->tpl->setVariable('VAL_ONOFFLINE', $this->lng->txt('offline'));
            $this->tpl->setVariable('ONOFFLINE_CLASS', 'smallred');
        }
        
        if ($a_set['passes']) {
            $this->tpl->setVariable('PASSES_TXT', $this->lng->txt('crs_loc_passes_info'));
            $this->tpl->setVariable('PASSES_VAL', $a_set['passes']);
        }
        
        
        // begin-patch lok
        $this->ctrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE_LINKED', $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'edit'));
        // end-patch lok

        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
        
        // materials
        foreach ($a_set['materials'] as $ref_id => $data) {
            if ($data['items']) {
                $this->tpl->touchBlock('ul_begin');
                foreach ($data['items'] as $pg_st) {
                    $this->tpl->setCurrentBlock('st_pg');
                    $this->tpl->setVariable('MAT_IMG', ilObject::_getIcon($pg_st['obj_id'], "tiny", $pg_st['type']));
                    $this->tpl->setVariable('MAT_ALT', $this->lng->txt('obj_' . $pg_st['type']));
                    include_once('Modules/LearningModule/classes/class.ilLMObject.php');
                    $title = ilLMObject::_lookupTitle($pg_st['obj_id']);
                    $this->tpl->setVariable('MAT_TITLE', $title);
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->touchBlock('ul_end');
            } else {
                $this->tpl->touchBlock('new_line');
            }
            $this->tpl->setCurrentBlock('mat_row');
            $this->tpl->setVariable('LM_IMG', ilObject::_getIcon($data['obj_id'], "tiny", $data['type']));
            $this->tpl->setVariable('LM_ALT', $this->lng->txt('obj_' . $data['type']));
            
            if ($data['type'] == 'catr' or $data['type'] == 'crsr' or $data['type'] == 'grpr') {
                include_once './Services/ContainerReference/classes/class.ilContainerReference.php';
                $this->tpl->setVariable(
                    'LM_TITLE',
                    ilContainerReference::_lookupTargetTitle($data['obj_id'])
                );
            } else {
                $this->tpl->setVariable('LM_TITLE', ilObject::_lookupTitle($data['obj_id']));
            }
            $this->tpl->parseCurrentBlock();
        }
        
        // self assessment
        // begin-patch lok
        if ($this->getSettings()->worksWithInitialTest()) {
            if ($this->getSettings()->hasSeparateInitialTests()) {
                if ($a_set['initial']) {
                    include_once './Services/Link/classes/class.ilLink.php';
                    $obj_id = ilObject::_lookupObjId($a_set['initial']);
                    $this->tpl->setCurrentBlock('initial_test_per_objective');
                    $this->tpl->setVariable('IT_IMG', ilObject::_getIcon($obj_id, 'tiny'));
                    $this->tpl->setVariable('IT_ALT', $this->lng->txt('obj_tst'));
                    $this->tpl->setVariable('IT_TITLE', ilObject::_lookupTitle($obj_id));
                    $this->tpl->setVariable('IT_TITLE_LINK', ilLink::_getLink($a_set['initial']));
                    
                    include_once './Services/Link/classes/class.ilLink.php';
                    $this->ctrl->setParameterByClass('ilobjtestgui', 'ref_id', $a_set['initial']);
                    $this->ctrl->setParameterByClass('ilobjtestgui', 'cmd', 'questionsTabGateway');
                    $this->tpl->setVariable(
                        'IT_TITLE_LINK',
                        $this->ctrl->getLinkTargetByClass('ilobjtestgui')
                    );
                                        
                    $this->tpl->parseCurrentBlock();
                } else {
                    $this->tpl->touchBlock('initial_test_per_objective');
                }
            } else {
                foreach ($a_set['self'] as $test) {
                    // begin-patch lok
                    foreach ((array) $test['questions'] as $question) {
                        $this->tpl->setCurrentBlock('self_qst_row');
                        $this->tpl->setVariable('SELF_QST_TITLE', $question['title']);
                        $this->tpl->parseCurrentBlock();
                    }
                    // end-patch lok
                }
                // begin-patch lok
                if (!count($a_set['self'])) {
                    $this->tpl->touchBlock('self_qst_row');
                }
            }
            
            // end-patch lok
        }
        // end-patch lok

        // final test questions
        if ($this->getSettings()->getQualifyingTestType() == ilLOSettings::TYPE_QUALIFYING_SELECTED) {
            if ($a_set['final']) {
                $obj_id = ilObject::_lookupObjId($a_set['final']);
                $this->tpl->setCurrentBlock('final_test_per_objective');
                $this->tpl->setVariable('FT_IMG', ilObject::_getIcon($obj_id, 'tiny'));
                $this->tpl->setVariable('FT_ALT', $this->lng->txt('obj_tst'));
                $this->tpl->setVariable('FT_TITLE', ilObject::_lookupTitle($obj_id));
                
                include_once './Services/Link/classes/class.ilLink.php';
                $this->ctrl->setParameterByClass('ilobjtestgui', 'ref_id', $a_set['final']);
                $this->ctrl->setParameterByClass('ilobjtestgui', 'cmd', 'questionsTabGateway');
                $this->tpl->setVariable(
                    'FT_TITLE_LINK',
                    $this->ctrl->getLinkTargetByClass('ilobjtestgui')
                );
                
                
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('final_test_per_objective');
            }
        } else {
            foreach ((array) $a_set['final'] as $test) {
                foreach ((array) $test['questions'] as $question) {
                    $this->tpl->setCurrentBlock('final_qst_row');
                    $this->tpl->setVariable('FINAL_QST_TITLE', $question['title']);
                    $this->tpl->parseCurrentBlock();
                }
                // begin-patch lok
                #$this->tpl->setCurrentBlock('final_test_row');
                #$this->tpl->setVariable('FINAL_TST_IMG',ilUtil::getImagePath('icon_tst_s.png'));
                #$this->tpl->setVariable('FINAL_TST_ALT',$this->lng->txt('obj_tst'));
                #$this->tpl->setVariable('FINAL_TST_TITLE',ilObject::_lookupTitle($test['obj_id']));
                #$this->tpl->parseCurrentBlock();
                // end-patch lok
            }
        }
        
        // begin-patch lok
        // Edit Link
        #$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'objective_id',$a_set['id']);
        $this->ctrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', $a_set['id']);
        #$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'edit'));
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'edit'));
        // end-patch lok
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($a_set['id']);
        //$alist->setListTitle($this->lng->txt("actions"));
        
        $alist->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'edit')
        );
        // materials
        $alist->addItem(
            $this->lng->txt('crs_objective_action_materials'),
            '',
            $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'materialAssignment')
        );
        // itest
        if ($this->getSettings()->worksWithInitialTest() && !$this->getSettings()->hasSeparateInitialTests()) {
            $alist->addItem(
                $this->lng->txt('crs_objective_action_itest'),
                '',
                $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'selfAssessmentAssignment')
            );
        }
        // qtest
        if ($this->getSettings()->hasSeparateQualifiedTests()) {
            #$alist->addItem(
            #		$this->lng->txt('crs_objective_action_qtest_sep'),
            #		'',
            #		$this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'finalSeparatedTestAssignment')
            #);
        } else {
            $alist->addItem(
                $this->lng->txt('crs_objective_action_qtest'),
                '',
                $this->ctrl->getLinkTargetByClass('ilcourseobjectivesgui', 'finalTestAssignment')
            );
        }
        
        $this->ctrl->setParameterByClass('illopagegui', 'objective_id', $a_set['id']);
        $alist->addItem(
            $this->lng->txt('crs_edit_lo_introduction'),
            '',
            $this->ctrl->getLinkTargetByClass('illopagegui', 'edit')
        );
        
        
        $this->tpl->setVariable('VAL_ACTIONS', $alist->getHTML());
        
        // end-patch lok
    }
        
    
    /**
     * parse
     *
     * @access public
     * @param array array of objective id's
     */
    public function parse($a_objective_ids)
    {
        $position = 1;
        foreach ($a_objective_ids as $objective_id) {
            $objective = new ilCourseObjective($this->course_obj, $objective_id);
            
            $objective_data = [];
            $objective_data['id'] = $objective_id;
            $objective_data['position'] = sprintf("%.1f", $position++) * 10;
            $objective_data['title'] = $objective->getTitle();
            $objective_data['description'] = $objective->getDescription();
            
            // begin-patch lok
            $objective_data['online'] = $objective->isActive();
            $objective_data['passes'] = $objective->getPasses();
            // end-patch lok
            
            // assigned materials
            $materials = array();
            $ass_materials = new ilCourseObjectiveMaterials($objective_id);
            foreach ($ass_materials->getMaterials() as $material) {
                $materials[$material['ref_id']]['obj_id'] = $obj_id = ilObject::_lookupObjId($material['ref_id']);
                $materials[$material['ref_id']]['type'] = ilObject::_lookupType($obj_id);

                switch ($material['type']) {
                    case 'pg':
                    case 'st':
                        $materials[$material['ref_id']]['items'][] = $material;
                        break;
                    default:
                        
                }
            }
            $objective_data['materials'] = $materials;
            $question_obj = new ilCourseObjectiveQuestion($objective_id);
            
            // self assessment questions
            // begin-patch lok
            if ($this->getSettings()->worksWithInitialTest()) {
                if ($this->getSettings()->hasSeparateInitialTests()) {
                    include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
                    $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
                    $assignment = $assignments->getAssignmentByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);

                    $objective_data['initial'] = 0;
                    if ($assignment instanceof ilLOTestAssignment) {
                        $test_id = $assignment->getTestRefId();

                        include_once './Services/Object/classes/class.ilObjectFactory.php';
                        $factory = new ilObjectFactory();
                        $test_candidate = $factory->getInstanceByRefId($test_id, false);
                        if ($test_candidate instanceof ilObjTest) {
                            $objective_data['initial'] = $test_id;
                        }
                    }
                } elseif (ilLOUtils::lookupRandomTest(ilObject::_lookupObjId($this->getSettings()->getInitialTest()))) {
                    $test = array();
                    $objective_data['self'] = [];
                    foreach (ilLORandomTestQuestionPools::lookupSequencesByType(
                        $this->course_obj->getId(),
                        $objective_id,
                        ilObject::_lookupObjId($this->getSettings()->getInitialTest()),
                        ilLOSettings::TYPE_TEST_INITIAL
                    ) as $sequence_id
                    ) {
                        $test['obj_id'] = ilObject::_lookupObjId($this->getSettings()->getInitialTest());
                        $qst = ilLOUtils::lookupQplBySequence($this->getSettings()->getInitialTest(), $sequence_id);
                        if ($qst) {
                            $test['questions'][] = array('title' => $qst);
                        }
                        $objective_data['self'] = array($test);
                    }
                } else {
                    $tests = array();
                    foreach ($question_obj->getSelfAssessmentTests() as $test) {
                        $questions = array();
                        foreach ($question_obj->getQuestionsOfTest($test['obj_id']) as $qst) {
                            $questions[] = $qst;
                        }
                        $tmp_test = $test;
                        $tmp_test['questions'] = $questions;

                        $tests[] = $tmp_test;
                    }
                    $objective_data['self'] = $tests;
                }
            }
            // end-patch lok
            
            // final test questions
            // begin-patch lok
            // single test assignments
            if ($this->getSettings()->getQualifyingTestType() == ilLOSettings::TYPE_QUALIFYING_SELECTED) {
                include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
                $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
                $assignment = $assignments->getAssignmentByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
            
                $objective_data['final'] = 0;
                if ($assignment instanceof ilLOTestAssignment) {
                    $test_id = $assignment->getTestRefId();

                    include_once './Services/Object/classes/class.ilObjectFactory.php';
                    $factory = new ilObjectFactory();
                    $test_candidate = $factory->getInstanceByRefId($test_id, false);
                    if ($test_candidate instanceof ilObjTest) {
                        $objective_data['final'] = $test_id;
                    }
                }
            } elseif ($this->getSettings()->getQualifiedTest()) {
                if (ilLOUtils::lookupRandomTest(ilObject::_lookupObjId($this->getSettings()->getQualifiedTest()))) {
                    $test = array();
                    foreach (ilLORandomTestQuestionPools::lookupSequencesByType(
                        $this->course_obj->getId(),
                        $objective_id,
                        ilObject::_lookupObjId($this->getSettings()->getQualifiedTest()),
                        ilLOSettings::TYPE_TEST_QUALIFIED
                    ) as $sequence_id
                    ) {
                        $test['obj_id'] = ilObject::_lookupObjId($this->getSettings()->getQualifiedTest());
                        $qst = ilLOUtils::lookupQplBySequence($this->getSettings()->getQualifiedTest(), $sequence_id);
                        if ($qst) {
                            $test['questions'][] = array('title' => $qst);
                        }
                        $objective_data['final'] = array($test);
                    }
                } else {
                    $tests = array();
                    foreach ($question_obj->getFinalTests() as $test) {
                        $questions = array();
                        foreach ($question_obj->getQuestionsOfTest($test['obj_id']) as $qst) {
                            $questions[] = $qst;
                        }
                        $tmp_test = $test;
                        $tmp_test['questions'] = $questions;

                        $tests[] = $tmp_test;
                    }
                    $objective_data['final'] = $tests;
                }
            }
            // end-patch lok
            $objectives[] = (array) $objective_data;
        }
        $this->setData($objectives ? $objectives : array());
    }
}
