<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* class ilcourseobjectiveQuestion
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends Object
*/

class ilCourseObjectiveQuestion
{
    const TYPE_SELF_ASSESSMENT = 0;
    const TYPE_FINAL_TEST = 1;
    
    public $db = null;

    public $objective_id = null;
    public $questions;
    protected $tests = array();

    /**
     * Constructor
     * @global type $ilDB
     * @param type $a_objective_id
     */
    public function __construct($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
    
        $this->objective_id = $a_objective_id;

        $this->__read();
    }
    
    
    /**
     * Lookup objective for test question
     * @global type $ilDB
     * @param type $a_test_ref_id
     * @param type $a_qid
     * @return int
     */
    public static function lookupObjectivesOfQuestion($a_qid)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT objective_id FROM crs_objective_qst ' .
                'WHERE question_id = ' . $ilDB->quote($a_qid, 'integer');
        $res = $ilDB->query($query);
        $objectiveIds = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectiveIds[] = $row->objective_id;
        }
        return $objectiveIds;
    }
    
    /**
     * Check if test is assigned to objective
     *
     * @access public
     * @static
     *
     * @param int test ref_id
     * @param int objective_id
     * @return boolean success
     */
    public static function _isTestAssignedToObjective($a_test_id, $a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT qst_ass_id FROM crs_objective_qst " .
            "WHERE ref_id = " . $ilDB->quote($a_test_id, 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }
    
    /**
     * clone objective questions
     *
     * @access public
     *
     * @param int source objective
     * @param int target objective
     * @param int copy id
     */
    public function cloneDependencies($a_new_objective, $a_copy_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilLog = $DIC['ilLog'];
        $ilDB = $DIC['ilDB'];
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getQuestions() as $question) {
            $mapping_key = $question['ref_id'] . '_question_' . $question['question_id'];
            if (!isset($mappings[$mapping_key]) or !$mappings[$mapping_key]) {
                continue;
            }
            $question_ref_id = $question['ref_id'];
            $question_obj_id = $question['obj_id'];
            $question_qst_id = $question['question_id'];
            $new_ref_id = $mappings[$question_ref_id];
            $new_obj_id = $ilObjDataCache->lookupObjId($new_ref_id);
            
            if ($new_obj_id == $question_obj_id) {
                ilLoggerFactory::getLogger('crs')->info('Test has been linked. Keeping question id');
                // Object has been linked
                $new_question_id = $question_qst_id;
            } else {
                $new_question_info = $mappings[$question_ref_id . '_question_' . $question_qst_id];
                $new_question_arr = explode('_', $new_question_info);
                if (!isset($new_question_arr[2]) or !$new_question_arr[2]) {
                    ilLoggerFactory::getLogger('crs')->debug('found invalid format of question id mapping: ' . print_r($new_question_arr, true));
                    continue;
                }
                $new_question_id = $new_question_arr[2];
                ilLoggerFactory::getLogger('crs')->info('New question id is: ' . $new_question_id);
            }
    
            ilLoggerFactory::getLogger('crs')->debug('Copying question assignments');
            $new_question = new ilCourseObjectiveQuestion($a_new_objective);
            $new_question->setTestRefId($new_ref_id);
            $new_question->setTestObjId($new_obj_id);
            $new_question->setQuestionId($new_question_id);
            $new_question->add();
        }
        
        // Copy tests
        foreach ($this->getTests() as $test) {
            $new_test_id = $mappings["$test[ref_id]"];
            
            $query = "UPDATE crs_objective_tst " .
                "SET tst_status = " . $this->db->quote($test['tst_status'], 'integer') . ", " .
                "tst_limit_p = " . $this->db->quote($test['tst_limit'], 'integer') . " " .
                "WHERE objective_id = " . $this->db->quote($a_new_objective, 'integer') . " " .
                "AND ref_id = " . $this->db->quote($new_test_id, 'integer');
            $res = $ilDB->manipulate($query);
        }
    }
    
    /**
     * Get assignable tests
     *
     * @access public
     * @static
     *
     * @param
     */
    public static function _getAssignableTests($a_container_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        return $tree->getSubTree($tree->getNodeData($a_container_ref_id), true, 'tst');
    }

    // ########################################################  Methods for test table
    public function setTestStatus($a_status)
    {
        $this->tst_status = $a_status;
    }
    public function getTestStatus()
    {
        return (int) $this->tst_status;
    }
    public function setTestSuggestedLimit($a_limit)
    {
        $this->tst_limit = $a_limit;
    }
    public function getTestSuggestedLimit()
    {
        return (int) $this->tst_limit;
    }
    public function __addTest()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objective_tst " .
            "SET tst_status = " . $this->db->quote($this->getTestStatus(), 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $this->db->quote($this->getTestRefId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        

        // CHECK if entry already exists
        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($this->getTestRefId(), 'integer') . "";

        $res = $this->db->query($query);
        if ($res->numRows()) {
            return false;
        }
        
        // Check for existing limit
        $query = "SELECT tst_limit_p FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND tst_status = " . $this->db->quote($this->getTestStatus(), 'integer') . " ";
            
        $res = $this->db->query($query);
        
        $limit = 100;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $limit = $row->tst_limit_p;
        }
        
        $next_id = $ilDB->nextId('crs_objective_tst');
        $query = "INSERT INTO crs_objective_tst (test_objective_id,objective_id,ref_id,obj_id,tst_status,tst_limit_p) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getObjectiveId(), 'integer') . ", " .
            $ilDB->quote($this->getTestRefId(), 'integer') . ", " .
            $ilDB->quote($this->getTestObjId(), 'integer') . ", " .
            $ilDB->quote($this->getTestStatus(), 'integer') . ", " .
            $this->db->quote($limit, 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function __deleteTest($a_test_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // Delete questions
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($a_test_ref_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // delete tst entries
        $query = "DELETE FROM crs_objective_tst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($a_test_ref_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        unset($this->tests[$a_test_ref_id]);

        return true;
    }

    /**
     * update test limits
     *
     * @access public
     * @param int objective_id
     * @param int status
     * @param int limit
     * @return
     * @static
     */
    public static function _updateTestLimits($a_objective_id, $a_status, $a_limit)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objective_tst " .
            "SET tst_limit_p = " . $ilDB->quote($a_limit, 'integer') . " " .
            "WHERE tst_status = " . $ilDB->quote($a_status, 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function updateTest($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objective_tst " .
            "SET tst_status = " . $ilDB->quote($this->getTestStatus(), 'integer') . ", " .
            "tst_limit_p = " . $ilDB->quote($this->getTestSuggestedLimit(), 'integer') . " " .
            "WHERE test_objective_id = " . $ilDB->quote($a_objective_id, 'integer') . "";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function getTests()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_objective_tst cot " .
            "JOIN object_data obd ON cot.obj_id = obd.obj_id " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY title ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test['test_objective_id'] = $row->test_objective_id;
            $test['objective_id'] = $row->objective_id;
            $test['ref_id'] = $row->ref_id;
            $test['obj_id'] = $row->obj_id;
            $test['tst_status'] = $row->tst_status;
            $test['tst_limit'] = $row->tst_limit_p;
            $test['title'] = $row->title;

            $tests[] = $test;
        }

        return $tests ? $tests : array();
    }
    
    /**
     * get self assessment tests
     *
     * @access public
     * @param
     * @return
     */
    public function getSelfAssessmentTests()
    {
        foreach ($this->tests as $test) {
            if ($test['status'] == self::TYPE_SELF_ASSESSMENT) {
                $self[] = $test;
            }
        }
        return $self ? $self : array();
    }
    
    /**
     * get final tests
     *
     * @access public
     * @return
     */
    public function getFinalTests()
    {
        foreach ($this->tests as $test) {
            if ($test['status'] == self::TYPE_FINAL_TEST) {
                $final[] = $test;
            }
        }
        return $final ? $final : array();
    }
    
    public static function _getTest($a_test_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE test_objective_id = " . $ilDB->quote($a_test_objective_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test['test_objective_id'] = $row->test_objective_id;
            $test['objective_id'] = $row->objective_id;
            $test['ref_id'] = $row->ref_id;
            $test['obj_id'] = $row->obj_id;
            $test['tst_status'] = $row->tst_status;
            $test['tst_limit'] = $row->tst_limit_p;
        }

        return $test ? $test : array();
    }

    // ############################################################# METHODS for question table
    public function getQuestions()
    {
        return $this->questions ? $this->questions : array();
    }
    
    /**
     * get self assessment questions
     *
     * @access public
     * @return
     */
    public function getSelfAssessmentQuestions()
    {
        foreach ($this->questions as $question) {
            if ($question['test_type'] == self::TYPE_SELF_ASSESSMENT) {
                $self[] = $question;
            }
        }
        return $self ? $self : array();
    }

    /**
     * get self assessment points
     *
     * @access public
     * @return
     */
    public function getSelfAssessmentPoints()
    {
        foreach ($this->getSelfAssessmentQuestions() as $question) {
            $points += $question['points'];
        }
        return $points ? $points : 0;
    }
    
    /**
     * get final test points
     *
     * @access public
     * @return
     */
    public function getFinalTestPoints()
    {
        foreach ($this->getFinalTestQuestions() as $question) {
            $points += $question['points'];
        }
        return $points ? $points : 0;
    }
    
    /**
     * check if question is self assessment question
     * @param int question id
     * @access public
     * @return
     */
    public function isSelfAssessmentQuestion($a_question_id)
    {
        foreach ($this->questions as $question) {
            if ($question['question_id'] == $a_question_id) {
                return $question['test_type'] == self::TYPE_SELF_ASSESSMENT;
            }
        }
        return false;
    }
    
    /**
     * is final test question
     *
     * @access public
     * @param int question id
     * @return
     */
    public function isFinalTestQuestion($a_question_id)
    {
        foreach ($this->questions as $question) {
            if ($question['question_id'] == $a_question_id) {
                return $question['test_type'] == self::TYPE_FINAL_TEST;
            }
        }
        return false;
    }
    
    /**
     * get final test questions
     *
     * @access public
     * @return
     */
    public function getFinalTestQuestions()
    {
        foreach ($this->questions as $question) {
            if ($question['test_type'] == self::TYPE_FINAL_TEST) {
                $final[] = $question;
            }
        }
        return $final ? $final : array();
    }
    
    
    
    /**
     * Get questions of test
     *
     * @access public
     * @param int test id
     *
     */
    public function getQuestionsOfTest($a_test_id)
    {
        foreach ($this->getQuestions() as $qst) {
            if ($a_test_id == $qst['obj_id']) {
                $questions[] = $qst;
            }
        }
        return $questions ? $questions : array();
    }
    
    public function getQuestion($question_id)
    {
        return $this->questions[$question_id] ? $this->questions[$question_id] : array();
    }

    public function getObjectiveId()
    {
        return $this->objective_id;
    }

    public function setTestRefId($a_ref_id)
    {
        $this->tst_ref_id = $a_ref_id;
    }
    public function getTestRefId()
    {
        return $this->tst_ref_id ? $this->tst_ref_id : 0;
    }
    public function setTestObjId($a_obj_id)
    {
        $this->tst_obj_id = $a_obj_id;
    }
    public function getTestObjId()
    {
        return $this->tst_obj_id ? $this->tst_obj_id : 0;
    }
    public function setQuestionId($a_question_id)
    {
        $this->question_id = $a_question_id;
    }
    public function getQuestionId()
    {
        return $this->question_id;
    }


    public function getMaxPointsByObjective()
    {
        include_once './Modules/Test/classes/class.ilObjTest.php';

        $points = 0;
        foreach ($this->getQuestions() as $question) {
            $tmp_test = &ilObjectFactory::getInstanceByRefId($question['ref_id']);

            $tmp_question = &ilObjTest::_instanciateQuestion($question['question_id']);

            $points += $tmp_question->getMaximumPoints();

            unset($tmp_question);
            unset($tmp_test);
        }
        return $points;
    }
    
    public function getMaxPointsByTest($a_test_ref_id)
    {
        $points = 0;

        $tmp_test = &ilObjectFactory::getInstanceByRefId($a_test_ref_id);

        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                $tmp_question = &ilObjTest::_instanciateQuestion($question['question_id']);

                $points += $tmp_question->getMaximumPoints();

                unset($tmp_question);
            }
        }
        unset($tmp_test);

        return $points;
    }
    
    /**
     * lookup maximimum point
     *
     * @access public
     * @param int question id
     * @return
     * @static
     */
    public static function _lookupMaximumPointsOfQuestion($a_question_id)
    {
        include_once('Modules/TestQuestionPool/classes/class.assQuestion.php');
        return assQuestion::_getMaximumPoints($a_question_id);
    }
    

    public function getNumberOfQuestionsByTest($a_test_ref_id)
    {
        $counter = 0;

        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                ++$counter;
            }
        }
        return $counter;
    }

    public function getQuestionsByTest($a_test_ref_id)
    {
        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                $qst[] = $question['question_id'];
            }
        }
        return $qst ? $qst : array();
    }

    /**
     * update limits
     *
     * @access public
     * @param
     * @return
     */
    public function updateLimits()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        foreach ($this->tests as $ref_id => $test_data) {
            switch ($test_data['status']) {
                case self::TYPE_SELF_ASSESSMENT:
                    $points = $this->getSelfAssessmentPoints();
                    break;
                
                case self::TYPE_FINAL_TEST:
                    $points = $this->getFinalTestPoints();
                    break;
            }
            if ($test_data['limit'] == -1 or $test_data['limit'] > $points) {
                switch ($test_data['status']) {
                    case self::TYPE_SELF_ASSESSMENT:
                        $points = $this->getSelfAssessmentPoints();
                        break;
                    
                    case self::TYPE_FINAL_TEST:
                        $points = $this->getFinalTestPoints();
                        break;
                }
                $query = "UPDATE crs_objective_tst " .
                    "SET tst_limit = " . $this->db->quote($points, 'integer') . " " .
                    "WHERE test_objective_id = " . $this->db->quote($test_data['test_objective_id'], 'integer') . " ";
                $res = $ilDB->manipulate($query);
            }
        }
    }


    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND question_id = " . $this->db->quote($this->getQuestionId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        $next_id = $ilDB->nextId('crs_objective_qst');
        $query = "INSERT INTO crs_objective_qst (qst_ass_id, objective_id,ref_id,obj_id,question_id) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getObjectiveId(), 'integer') . ", " .
            $ilDB->quote($this->getTestRefId(), 'integer') . ", " .
            $ilDB->quote($this->getTestObjId(), 'integer') . ", " .
            $ilDB->quote($this->getQuestionId(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);

        $this->__addTest();
        
        $this->__read();

        return true;
    }
    public function delete($qst_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$qst_id) {
            return false;
        }
        
        $query = "SELECT * FROM crs_objective_qst " .
            "WHERE qst_ass_id = " . $ilDB->quote($qst_id, 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test_rid = $row->ref_id;
            $test_oid = $row->obj_id;
        }

        $query = "DELETE FROM crs_objective_qst " .
            "WHERE qst_ass_id = " . $ilDB->quote($qst_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // delete test if it was the last question
        $query = "SELECT * FROM crs_objective_qst " .
            "WHERE ref_id = " . $ilDB->quote($test_rid, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($test_oid, 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";

        $res = $this->db->query($query);
        if (!$res->numRows()) {
            $this->__deleteTest($test_rid);
        }

        return true;
    }
    
    // begin-patch lok
    public static function deleteTest($a_tst_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'DELETE FROM crs_objective_tst ' .
                'WHERE ref_id = ' . $ilDB->quote($a_tst_ref_id, 'integer');
        $ilDB->manipulate($query);

        $query = 'DELETE FROM crs_objective_qst ' .
                'WHERE ref_id = ' . $ilDB->quote($a_tst_ref_id, 'integer');
        $ilDB->manipulate($query);
    }
    
    
    public function deleteByTestType($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        
        // Read tests by type
        $deletable_refs = array();
        foreach ((array) $this->tests as $tst_data) {
            if ($tst_data['status'] == $a_type) {
                $deletable_refs[] = $tst_data['ref_id'];
            }
        }
        
        $query = 'DELETE from crs_objective_tst ' .
                'WHERE objective_id = ' . $ilDB->quote($this->getObjectiveId(), 'integer') . ' ' .
                'AND tst_status = ' . $ilDB->quote($a_type, 'integer');
        $ilDB->manipulate($query);
        
        
        $query = 'DELETE from crs_objective_tst ' .
                'WHERE objective_id = ' . $ilDB->quote($this->getObjectiveId(), 'integer') . ' ' .
                'AND ' . $ilDB->in('ref_id', $deletable_refs, false, 'integer');
        $ilDB->manipulate($query);
        
        return true;
    }
    // end-patch lok
    

    public function deleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = "DELETE FROM crs_objective_tst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }


    // PRIVATE
    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        
        include_once './Modules/Test/classes/class.ilObjTest.php';
        include_once('Modules/Course/classes/class.ilCourseObjective.php');

        $container_ref_ids = ilObject::_getAllReferences(ilCourseObjective::_lookupContainerIdByObjectiveId($this->objective_id));
        $container_ref_id = current($container_ref_ids);
        
        // Read test data
        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->tests[$row->ref_id]['test_objective_id'] = $row->test_objective_id;
            $this->tests[$row->ref_id]['ref_id'] = $row->ref_id;
            $this->tests[$row->ref_id]['obj_id'] = $row->obj_id;
            $this->tests[$row->ref_id]['status'] = $row->tst_status;
            $this->tests[$row->ref_id]['limit'] = $row->tst_limit_p;
        }

        $this->questions = array();
        $query = "SELECT * FROM crs_objective_qst coq " .
            "JOIN qpl_questions qq ON coq.question_id = qq.question_id " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY title";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$tree->isInTree($row->ref_id) or !$tree->isGrandChild($container_ref_id, $row->ref_id)) {
                $this->__deleteTest($row->ref_id);
                continue;
            }
            if (!$question = ilObjTest::_instanciateQuestion($row->question_id)) {
                $this->delete($row->question_id);
                continue;
            }
    
            $qst['ref_id'] = $row->ref_id;
            $qst['obj_id'] = $row->obj_id;
            $qst['question_id'] = $row->question_id;
            $qst['qst_ass_id'] = $row->qst_ass_id;
            $qst['title'] = $question->getTitle();
            $qst['description'] = $question->getComment();
            $qst['test_type'] = $this->tests[$row->ref_id]['status'];
            $qst['points'] = $question->getPoints();

            $this->questions[$row->qst_ass_id] = $qst;
        }

        return true;
    }

    // STATIC
    /**
     *
     *
     * @access public
     * @param
     * @return
     */
    public static function _hasTests($a_course_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT co.objective_id FROM crs_objectives co JOIN " .
            "crs_objective_tst cot ON co.objective_id = cot.objective_id " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }
    
    
    public static function _isAssigned($a_objective_id, $a_tst_ref_id, $a_question_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT crs_qst.objective_id objective_id FROM crs_objective_qst crs_qst, crs_objectives crs_obj " .
            "WHERE crs_qst.objective_id = crs_obj.objective_id " .
            "AND crs_qst.objective_id = " . $ilDB->quote($a_objective_id, 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($a_tst_ref_id, 'integer') . " " .
            "AND question_id = " . $ilDB->quote($a_question_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objective_id = $row->objective_id;
        }
        
        return $objective_id ? $objective_id : 0;
    }
    
    // begin-patch lok
    public static function lookupQuestionsByObjective($a_test_id, $a_objective)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT question_id FROM crs_objective_qst ' .
                'WHERE objective_id = ' . $ilDB->quote($a_objective, 'integer') . ' ' .
                'AND obj_id = ' . $ilDB->quote($a_test_id, 'integer');
        $res = $ilDB->query($query);
        
        $questions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $questions[] = $row->question_id;
        }
        return (array) $questions;
    }
    
    public static function loookupTestLimit($a_test_id, $a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT tst_limit_p FROM crs_objective_tst ' .
                'WHERE objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
                'AND obj_id = ' . $ilDB->quote($a_test_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->tst_limit_p;
        }
        return 0;
    }
    
    /**
     * To xml
     * @param ilXmlWriter $writer
     */
    public function toXml(ilXmlWriter $writer)
    {
        foreach ($this->getTests() as $test) {
            include_once './Modules/Course/classes/Objectives/class.ilLOXmlWriter.php';
            $writer->xmlStartTag(
                'Test',
                array(
                    'type' => ilLOXmlWriter::TYPE_TST_ALL,
                    'refId' => $test['ref_id'],
                    'testType' => $test['tst_status'],
                    'limit' => $test['tst_limit']
                )
            );
            
            // questions
            foreach ($this->getQuestionsByTest($test['ref_id']) as $question_id) {
                $writer->xmlElement('Question', array('id' => $question_id));
            }
            $writer->xmlEndTag('Test');
        }
    }
    
    // end-patch lok
}
