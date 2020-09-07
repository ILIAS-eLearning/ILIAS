<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/interfaces/interface.ilQuestionChangeListener.php';

/**
 * Listener for question changes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilDynamicTestQuestionChangeListener implements ilQuestionChangeListener
{
    /**
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }
    
    /**
     * @var array[integer]
     */
    private $testObjIds = array();
    
    /**
     * @param integer $testObjId
     */
    public function addTestObjId($testObjId)
    {
        $this->testObjIds[] = $testObjId;
    }
    
    /**
     * @return array[integer]
     */
    public function getTestObjIds()
    {
        return $this->testObjIds;
    }

    /**
     * @param assQuestion $question
     */
    public function notifyQuestionCreated(assQuestion $question)
    {
        //mail('bheyser@databay.de', __METHOD__, __METHOD__);
        // nothing to do
    }

    /**
     * @param assQuestion $question
     */
    public function notifyQuestionEdited(assQuestion $question)
    {
        //mail('bheyser@databay.de', __METHOD__, __METHOD__);
        $this->deleteTestsParticipantsQuestionData($question);
    }
    
    public function notifyQuestionDeleted(assQuestion $question)
    {
        //mail('bheyser@databay.de', __METHOD__, __METHOD__);
        $this->deleteTestsParticipantsQuestionData($question);
    }
    
    /**
     * @param assQuestion $question
     */
    private function deleteTestsParticipantsQuestionData(assQuestion $question)
    {
        $activeIds = $this->getActiveIds();

        if (!is_array($activeIds) || 0 === count($activeIds)) {
            return null;
        }
        
        $this->deleteTestsParticipantsResultsForQuestion($activeIds, $question->getId());
        $this->deleteTestsParticipantsTrackingsForQuestion($activeIds, $question->getId());
    }

    private function deleteTestsParticipantsResultsForQuestion($activeIds, $questionId)
    {
        $inActiveIds = $this->db->in('active_fi', $activeIds, false, 'integer');

        $this->db->manipulateF(
            "DELETE FROM tst_solutions WHERE question_fi = %s AND $inActiveIds",
            array('integer'),
            array($questionId)
        );

        $this->db->manipulateF(
            "DELETE FROM tst_qst_solved WHERE question_fi = %s AND $inActiveIds",
            array('integer'),
            array($questionId)
        );

        $this->db->manipulateF(
            "DELETE FROM tst_test_result WHERE question_fi = %s AND $inActiveIds",
            array('integer'),
            array($questionId)
        );

        $this->db->manipulate("DELETE FROM tst_pass_result WHERE $inActiveIds");

        $this->db->manipulate("DELETE FROM tst_result_cache WHERE $inActiveIds");
    }
    
    private function deleteTestsParticipantsTrackingsForQuestion($activeIds, $questionId)
    {
        $inActiveIds = $this->db->in('active_fi', $activeIds, false, 'integer');

        $tables = array(
            'tst_seq_qst_tracking', 'tst_seq_qst_answstatus', 'tst_seq_qst_postponed', 'tst_seq_qst_checked'
        );
        
        foreach ($tables as $table) {
            $this->db->manipulateF(
                "DELETE FROM $table WHERE question_fi = %s AND $inActiveIds",
                array('integer'),
                array($questionId)
            );
        }
    }
    
    private function getActiveIds()
    {
        if (!count($this->getTestObjIds())) {
            return null;
        }
        
        $inTestObjIds = $this->db->in('obj_fi', $this->getTestObjIds(), false, 'integer');
        
        $res = $this->db->query("
			SELECT active_id
			FROM tst_tests
			INNER JOIN tst_active
			ON test_fi = test_id
			WHERE $inTestObjIds
		");
        
        $activeIds = array();
        
        while ($row = $this->db->fetchAssoc($res)) {
            $activeIds[] = $row['active_id'];
        }
        
        return $activeIds;
    }
}
