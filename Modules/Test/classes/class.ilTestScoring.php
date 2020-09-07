<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestScoring
 *
 * This class holds a mechanism to get the scoring for
 * - a test,
 * - a user in a test,
 * - a pass in a users passes in a test, or
 * - a question in a pass in a users passes in a test.
 *
 * Warning:
 * Please use carefully, this is one of the classes that may cause funny spikes on your servers load graph on large
 * datasets in the test.
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTest
 */
class ilTestScoring
{
    /** @var ilObjTest $test */
    protected $test;

    /** @var bool $preserve_manual_scores */
    protected $preserve_manual_scores;

    private $recalculatedPasses;
    
    /**
     * @var int
     */
    protected $questionId = 0;

    public function __construct(ilObjTest $test)
    {
        $this->test = $test;
        $this->preserve_manual_scores = false;
        
        $this->recalculatedPasses = array();
    }

    /**
     * @param boolean $preserve_manual_scores
     */
    public function setPreserveManualScores($preserve_manual_scores)
    {
        $this->preserve_manual_scores = $preserve_manual_scores;
    }

    /**
     * @return boolean
     */
    public function getPreserveManualScores()
    {
        return $this->preserve_manual_scores;
    }
    
    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    /**
     * @param int $questionId
     */
    public function setQuestionId(int $questionId)
    {
        $this->questionId = $questionId;
    }
    
    public function recalculateSolutions()
    {
        $participants = $this->test->getCompleteEvaluationData(false)->getParticipants();
        if (is_array($participants)) {
            require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            foreach ($participants as $active_id => $userdata) {
                if (is_object($userdata) && is_array($userdata->getPasses())) {
                    $this->recalculatePasses($userdata, $active_id);
                }
                assQuestion::_updateTestResultCache($active_id);
            }
        }
    }

    /**
     * @param $userdata
     * @param $active_id
     */
    public function recalculatePasses($userdata, $active_id)
    {
        $passes = $userdata->getPasses();
        foreach ($passes as $pass => $passdata) {
            if (is_object($passdata)) {
                $this->recalculatePass($passdata, $active_id, $pass);
                $this->addRecalculatedPassByActive($active_id, $pass);
            }
        }
    }

    /**
     * @param $passdata
     * @param $active_id
     * @param $pass
     */
    public function recalculatePass($passdata, $active_id, $pass)
    {
        $questions = $passdata->getAnsweredQuestions();
        if (is_array($questions)) {
            foreach ($questions as $questiondata) {
                if ($this->getQuestionId() && $this->getQuestionId() != $questiondata['id']) {
                    continue;
                }
                
                $question_gui = $this->test->createQuestionGUI("", $questiondata['id']);
                $this->recalculateQuestionScore($question_gui, $active_id, $pass, $questiondata);
            }
        }
    }

    /**
     * @param $question_gui
     * @param $active_id
     * @param $pass
     * @param $questiondata
     */
    public function recalculateQuestionScore($question_gui, $active_id, $pass, $questiondata)
    {
        /** @var assQuestion $question_gui */
        if (is_object($question_gui)) {
            $reached = $question_gui->object->calculateReachedPoints($active_id, $pass);
            $actual_reached = $question_gui->object->adjustReachedPointsByScoringOptions($reached, $active_id, $pass);

            if ($this->preserve_manual_scores == true && $questiondata['manual'] == '1') {
                // Do we need processing here?
            } else {
                assQuestion::setForcePassResultUpdateEnabled(true);
                
                assQuestion::_setReachedPoints(
                    $active_id,
                    $questiondata['id'],
                    $actual_reached,
                    $question_gui->object->getMaximumPoints(),
                    $pass,
                    false,
                    true
                );
                
                assQuestion::setForcePassResultUpdateEnabled(false);
            }
        }
    }

    /**
     * @return string HTML with the best solution output.
     */
    public function calculateBestSolutionForTest()
    {
        $solution = '';
        foreach ($this->test->getAllQuestions() as $question) {
            /** @var AssQuestionGUI $question_gui */
            $question_gui = $this->test->createQuestionGUI("", $question['question_id']);
            $solution .= $question_gui->getSolutionOutput(0, null, true, true, false, false, true, false);
        }
        
        return $solution;
    }

    public function resetRecalculatedPassesByActives()
    {
        $this->recalculatedPasses = array();
    }
    
    public function getRecalculatedPassesByActives()
    {
        return $this->recalculatedPasses;
    }
    
    public function addRecalculatedPassByActive($activeId, $pass)
    {
        if (!is_array($this->recalculatedPasses[$activeId])) {
            $this->recalculatedPasses[$activeId] = array();
        }

        $this->recalculatedPasses[$activeId][] = $pass;
    }
    
    public function removeAllQuestionResults($questionId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $query = "DELETE FROM tst_test_result WHERE question_fi = %s";
        $DIC->database()->manipulateF($query, array('integer'), array($questionId));
    }
    
    public function updatePassAndTestResults($activeIds)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        foreach ($activeIds as $activeId) {
            $passSelector = new ilTestPassesSelector($DIC->database(), $this->test);
            $passSelector->setActiveId($activeId);
            
            foreach ($passSelector->getExistingPasses() as $pass) {
                assQuestion::_updateTestPassResults($activeId, $pass, $this->test->areObligationsEnabled());
            }
            
            assQuestion::_updateTestResultCache($activeId);
        }
    }
    
    /**
     * @return int
     */
    public function getNumManualScorings()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $query = "
			SELECT COUNT(*) num_manual_scorings
			FROM tst_test_result tres
			
			INNER JOIN tst_active tact
			ON tact.active_id = tres.active_fi
			AND tact.test_fi = %s
			
			WHERE tres.manual = 1
		";
        
        $types = array('integer');
        $values = array($this->test->getTestId());
        
        if ($this->getQuestionId()) {
            $query .= "
				AND tres.question_fi = %s
			";
            
            $types[] = 'integer';
            $values[] = $this->getQuestionId();
        }
        
        $res = $DIC->database()->queryF($query, $types, $values);
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            return (int) $row['num_manual_scorings'];
        }
        
        return 0;
    }
}
