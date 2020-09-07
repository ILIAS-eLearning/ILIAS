<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/interfaces/interface.ilTestQuestionSequence.php';
require_once 'Modules/Test/interfaces/interface.ilTestSequenceSummaryProvider.php';

/**
* Test sequence handler
*
* This class manages the sequence settings for a given user
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestSequence implements ilTestQuestionSequence, ilTestSequenceSummaryProvider
{
    /**
    * An array containing the sequence data
    *
    * @var array
    */
    public $sequencedata;

    /**
    * The mapping of the sequence numbers to the questions
    *
    * @var array
    */
    public $questions;

    /**
    * The active id of the sequence data
    *
    * @var integer
    */
    public $active_id;

    /**
    * The pass of the current sequence
    *
    * @var integer
    */
    public $pass;

    /**
    * Indicates wheather the active test is a random test or not
    *
    * @var boolean
    */
    public $isRandomTest;
    
    /**
     * @var integer[]
     */
    protected $alreadyPresentedQuestions = array();
    
    /**
     * @var int
     */
    protected $newlyPresentedQuestion = 0;

    /**
     * @var array
     */
    protected $alreadyCheckedQuestions;

    /**
     * @var integer
     */
    protected $newlyCheckedQuestion;

    /**
     * @var array
     */
    protected $optionalQuestions;

    /**
     * @var bool
     */
    private $answeringOptionalQuestionsConfirmed;

    /**
     * @var bool
     */
    private $considerHiddenQuestionsEnabled;

    /**
     * @var bool
     */
    private $considerOptionalQuestionsEnabled;
    
    /**
    * ilTestSequence constructor
    *
    * The constructor takes possible arguments an creates an instance of
    * the ilTestSequence object.
    *
    * @param object $a_object A reference to the test container object
    * @access public
    */
    public function __construct($active_id, $pass, $randomtest)
    {
        $this->active_id = $active_id;
        $this->pass = $pass;
        $this->isRandomTest = $randomtest;
        $this->sequencedata = array(
            "sequence" => array(),
            "postponed" => array(),
            "hidden" => array()
        );
        
        $this->alreadyCheckedQuestions = array();
        $this->newlyCheckedQuestion = null;
        
        $this->optionalQuestions = array();
        $this->answeringOptionalQuestionsConfirmed = false;
        
        $this->considerHiddenQuestionsEnabled = false;
        $this->considerOptionalQuestionsEnabled = true;
    }
    
    public function getActiveId()
    {
        return $this->active_id;
    }
    
    public function createNewSequence($max, $shuffle)
    {
        $newsequence = array();
        if ($max > 0) {
            for ($i = 1; $i <= $max; $i++) {
                array_push($newsequence, $i);
            }
            if ($shuffle) {
                $newsequence = $this->pcArrayShuffle($newsequence);
            }
        }
        $this->sequencedata["sequence"] = $newsequence;
    }
    
    /**
    * Loads the question mapping
    */
    public function loadQuestions(ilTestQuestionSetConfig $testQuestionSetConfig = null, $taxonomyFilterSelection = array())
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->questions = array();

        $result = $ilDB->queryF(
            "SELECT tst_test_question.* FROM tst_test_question, qpl_questions, tst_active WHERE tst_active.active_id = %s AND tst_test_question.test_fi = tst_active.test_fi AND qpl_questions.question_id = tst_test_question.question_fi ORDER BY tst_test_question.sequence",
            array('integer'),
            array($this->active_id)
        );

        $index = 1;

        // TODO bheyser: There might be "sequence" gaps which lead to issues with tst_sequence when deleting/adding questions before any participant starts the test
        while ($data = $ilDB->fetchAssoc($result)) {
            $this->questions[$index++] = $data["question_fi"];
        }
    }
    
    /**
    * Loads the sequence data for a given active id
    *
    * @return string The filesystem path of the certificate
    */
    public function loadFromDb()
    {
        $this->loadQuestionSequence();
        $this->loadPresentedQuestions();
        $this->loadCheckedQuestions();
        $this->loadOptionalQuestions();
    }
    
    private function loadQuestionSequence()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_sequence WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $this->sequencedata = array(
                "sequence" => unserialize($row["sequence"]),
                "postponed" => unserialize($row["postponed"]),
                "hidden" => unserialize($row["hidden"])
            );
            if (!is_array($this->sequencedata["sequence"])) {
                $this->sequencedata["sequence"] = array();
            }
            if (!is_array($this->sequencedata["postponed"])) {
                $this->sequencedata["postponed"] = array();
            }
            if (!is_array($this->sequencedata["hidden"])) {
                $this->sequencedata["hidden"] = array();
            }
            
            $this->setAnsweringOptionalQuestionsConfirmed((bool) $row['ans_opt_confirmed']);
        }
    }
    
    protected function loadPresentedQuestions()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT question_fi FROM tst_seq_qst_presented WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $this->alreadyPresentedQuestions[ $row['question_fi'] ] = $row['question_fi'];
        }
    }
    
    private function loadCheckedQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT question_fi FROM tst_seq_qst_checked WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->alreadyCheckedQuestions[ $row['question_fi'] ] = $row['question_fi'];
        }
    }
    
    private function loadOptionalQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT question_fi FROM tst_seq_qst_optional WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            $this->optionalQuestions[ $row['question_fi'] ] = $row['question_fi'];
        }
    }
    
    /**
    * Saves the sequence data for a given pass to the database
    *
    * @access public
    */
    public function saveToDb()
    {
        $this->saveQuestionSequence();
        $this->saveNewlyPresentedQuestion();
        $this->saveNewlyCheckedQuestion();
        $this->saveOptionalQuestions();
    }
    
    private function saveQuestionSequence()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $postponed = null;
        if ((is_array($this->sequencedata["postponed"])) && (count($this->sequencedata["postponed"]))) {
            $postponed = serialize($this->sequencedata["postponed"]);
        }
        $hidden = null;
        if ((is_array($this->sequencedata["hidden"])) && (count($this->sequencedata["hidden"]))) {
            $hidden = serialize($this->sequencedata["hidden"]);
        }

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM tst_sequence WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );

        $affectedRows = $ilDB->insert("tst_sequence", array(
            "active_fi" => array("integer", $this->active_id),
            "pass" => array("integer", $this->pass),
            "sequence" => array("clob", serialize($this->sequencedata["sequence"])),
            "postponed" => array("text", $postponed),
            "hidden" => array("text", $hidden),
            "tstamp" => array("integer", time()),
            'ans_opt_confirmed' => array('integer', (int) $this->isAnsweringOptionalQuestionsConfirmed())
        ));
    }
    
    protected function saveNewlyPresentedQuestion()
    {
        if ((int) $this->newlyPresentedQuestion) {
            global $DIC; /* @var ILIAS\DI\Container $DIC */
            
            $DIC->database()->replace('tst_seq_qst_presented', array(
                'active_fi' => array('integer', (int) $this->active_id),
                'pass' => array('integer', (int) $this->pass),
                'question_fi' => array('integer', (int) $this->newlyPresentedQuestion)
            ), array());
        }
    }

    /**
     * @global ilDBInterface $ilDB
     */
    private function saveNewlyCheckedQuestion()
    {
        if ((int) $this->newlyCheckedQuestion) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            
            $ilDB->replace('tst_seq_qst_checked', array(
                'active_fi' => array('integer', (int) $this->active_id),
                'pass' => array('integer', (int) $this->pass),
                'question_fi' => array('integer', (int) $this->newlyCheckedQuestion)
            ), array());
        }
    }

    /**
     * @global ilDBInterface $ilDB
     */
    private function saveOptionalQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $NOT_IN_questions = $ilDB->in('question_fi', $this->optionalQuestions, true, 'integer');
        
        $ilDB->queryF(
            "DELETE FROM tst_seq_qst_optional WHERE active_fi = %s AND pass = %s AND $NOT_IN_questions",
            array('integer', 'integer'),
            array($this->active_id, $this->pass)
        );

        foreach ($this->optionalQuestions as $questionId) {
            $ilDB->replace('tst_seq_qst_optional', array(
                'active_fi' => array('integer', (int) $this->active_id),
                'pass' => array('integer', (int) $this->pass),
                'question_fi' => array('integer', (int) $questionId)
            ), array());
        }
    }
    
    public function postponeQuestion($question_id)
    {
        if (!$this->isPostponedQuestion($question_id)) {
            array_push($this->sequencedata["postponed"], intval($question_id));
        }
    }
    
    public function hideQuestion($question_id)
    {
        if (!$this->isHiddenQuestion($question_id)) {
            array_push($this->sequencedata["hidden"], intval($question_id));
        }
    }
    
    public function isPostponedQuestion($question_id)
    {
        if (!is_array($this->sequencedata["postponed"])) {
            return false;
        }
        if (!in_array($question_id, $this->sequencedata["postponed"])) {
            return false;
        } else {
            return true;
        }
    }
    
    public function isHiddenQuestion($question_id)
    {
        if (!is_array($this->sequencedata["hidden"])) {
            return false;
        }
        if (!in_array($question_id, $this->sequencedata["hidden"])) {
            return false;
        } else {
            return true;
        }
    }
    
    public function isPostponedSequence($sequence)
    {
        if (!array_key_exists($sequence, $this->questions)) {
            return false;
        }
        if (!is_array($this->sequencedata["postponed"])) {
            return false;
        }
        if (!in_array($this->questions[$sequence], $this->sequencedata["postponed"])) {
            return false;
        } else {
            return true;
        }
    }
    
    public function isHiddenSequence($sequence)
    {
        if (!array_key_exists($sequence, $this->questions)) {
            return false;
        }
        if (!is_array($this->sequencedata["hidden"])) {
            return false;
        }
        if (!in_array($this->questions[$sequence], $this->sequencedata["hidden"])) {
            return false;
        } else {
            return true;
        }
    }
    
    public function postponeSequence($sequence)
    {
        if (!$this->isPostponedSequence($sequence)) {
            if (array_key_exists($sequence, $this->questions)) {
                if (!is_array($this->sequencedata["postponed"])) {
                    $this->sequencedata["postponed"] = array();
                }
                array_push($this->sequencedata["postponed"], intval($this->questions[$sequence]));
            }
        }
    }
    
    public function hideSequence($sequence)
    {
        if (!$this->isHiddenSequence($sequence)) {
            if (array_key_exists($sequence, $this->questions)) {
                if (!is_array($this->sequencedata["hidden"])) {
                    $this->sequencedata["hidden"] = array();
                }
                array_push($this->sequencedata["hidden"], intval($this->questions[$sequence]));
            }
        }
    }
    
    public function setQuestionPresented($questionId)
    {
        $this->newlyPresentedQuestion = $questionId;
    }
    
    public function isQuestionPresented($questionId)
    {
        return (
            $this->newlyPresentedQuestion == $questionId || in_array($questionId, $this->alreadyPresentedQuestions)
        );
    }
    
    public function isNextQuestionPresented($questionId)
    {
        $nextQstId = $this->getQuestionForSequence(
            $this->getNextSequence($this->getSequenceForQuestion($questionId))
        );
        
        if (!$nextQstId) {
            return false;
        }
        
        if ($this->newlyPresentedQuestion == $nextQstId) {
            return true;
        }
        
        if (in_array($nextQstId, $this->alreadyPresentedQuestions)) {
            return true;
        }
        
        return false;
    }
    
    public function setQuestionChecked($questionId)
    {
        $this->newlyCheckedQuestion = $questionId;
    }
    
    public function isQuestionChecked($questionId)
    {
        return isset($this->alreadyCheckedQuestions[$questionId]);
    }
    
    public function getPositionOfSequence($sequence)
    {
        $correctedsequence = $this->getCorrectedSequence();
        $sequencekey = array_search($sequence, $correctedsequence);
        if ($sequencekey !== false) {
            return $sequencekey + 1;
        } else {
            return "";
        }
    }
    
    public function getUserQuestionCount()
    {
        return count($this->getCorrectedSequence());
    }
    
    public function getOrderedSequence()
    {
        $sequenceKeys = array();
        
        foreach (array_keys($this->questions) as $sequenceKey) {
            if ($this->isHiddenSequence($sequenceKey) && !$this->isConsiderHiddenQuestionsEnabled()) {
                continue;
            }
            
            if ($this->isSequenceOptional($sequenceKey) && !$this->isConsiderOptionalQuestionsEnabled()) {
                continue;
            }
            
            $sequenceKeys[] = $sequenceKey;
        }
        
        return $sequenceKeys;
    }
    
    public function getOrderedSequenceQuestions()
    {
        $questions = array();
        
        foreach ($this->questions as $questionId) {
            if ($this->isHiddenQuestion($questionId) && !$this->isConsiderHiddenQuestionsEnabled()) {
                continue;
            }
            
            if ($this->isQuestionOptional($questionId) && !$this->isConsiderOptionalQuestionsEnabled()) {
                continue;
            }
            
            $questions[] = $questionId;
        }
        
        return $questions;
    }
    
    public function getUserSequence()
    {
        return $this->getCorrectedSequence();
    }

    public function getUserSequenceQuestions()
    {
        $seq = $this->getCorrectedSequence();
        $found = array();
        foreach ($seq as $sequence) {
            array_push($found, $this->getQuestionForSequence($sequence));
        }
        return $found;
    }
    
    private function ensureQuestionNotInSequence($sequence, $questionId)
    {
        $questionKey = array_search($questionId, $this->questions);
        
        if ($questionKey === false) {
            return $sequence;
        }
        
        $sequenceKey = array_search($questionKey, $sequence);
        
        if ($sequenceKey === false) {
            return $sequence;
        }

        unset($sequence[$sequenceKey]);
        
        return $sequence;
    }

    protected function getCorrectedSequence()
    {
        $correctedsequence = $this->sequencedata["sequence"];
        if (!$this->isConsiderHiddenQuestionsEnabled()) {
            if (is_array($this->sequencedata["hidden"])) {
                foreach ($this->sequencedata["hidden"] as $question_id) {
                    $correctedsequence = $this->ensureQuestionNotInSequence($correctedsequence, $question_id);
                }
            }
        }
        if (!$this->isConsiderOptionalQuestionsEnabled()) {
            foreach ($this->optionalQuestions as $questionId) {
                $correctedsequence = $this->ensureQuestionNotInSequence($correctedsequence, $questionId);
            }
        }
        if (is_array($this->sequencedata["postponed"])) {
            foreach ($this->sequencedata["postponed"] as $question_id) {
                $foundsequence = array_search($question_id, $this->questions);
                if ($foundsequence !== false) {
                    $sequencekey = array_search($foundsequence, $correctedsequence);
                    if ($sequencekey !== false) {
                        unset($correctedsequence[$sequencekey]);
                        array_push($correctedsequence, $foundsequence);
                    }
                }
            }
        }
        return array_values($correctedsequence);
    }
    
    public function getSequenceForQuestion($question_id)
    {
        return array_search($question_id, $this->questions);
    }
    
    public function getFirstSequence()
    {
        $correctedsequence = $this->getCorrectedSequence();
        if (count($correctedsequence)) {
            return reset($correctedsequence);
        } else {
            return false;
        }
    }
    
    public function getLastSequence()
    {
        $correctedsequence = $this->getCorrectedSequence();
        if (count($correctedsequence)) {
            return end($correctedsequence);
        } else {
            return false;
        }
    }
    
    public function getNextSequence($sequence)
    {
        $correctedsequence = $this->getCorrectedSequence();
        $sequencekey = array_search($sequence, $correctedsequence);
        if ($sequencekey !== false) {
            $nextsequencekey = $sequencekey + 1;
            if (array_key_exists($nextsequencekey, $correctedsequence)) {
                return $correctedsequence[$nextsequencekey];
            }
        }
        return false;
    }
    
    public function getPreviousSequence($sequence)
    {
        $correctedsequence = $this->getCorrectedSequence();
        $sequencekey = array_search($sequence, $correctedsequence);
        if ($sequencekey !== false) {
            $prevsequencekey = $sequencekey - 1;
            if (($prevsequencekey >= 0) && (array_key_exists($prevsequencekey, $correctedsequence))) {
                return $correctedsequence[$prevsequencekey];
            }
        }
        return false;
    }
    
    /**
    * Shuffles the values of a given array
    *
    * Shuffles the values of a given array
    *
    * @param array $array An array which should be shuffled
    * @access public
    */
    public function pcArrayShuffle($array)
    {
        $keys = array_keys($array);
        shuffle($keys);
        $result = array();
        foreach ($keys as $key) {
            $result[$key] = $array[$key];
        }
        return $result;
    }

    public function getQuestionForSequence($sequence)
    {
        if ($sequence < 1) {
            return false;
        }
        if (array_key_exists($sequence, $this->questions)) {
            return $this->questions[$sequence];
        } else {
            return false;
        }
    }
    
    public function getSequenceSummary($obligationsFilterEnabled = false)
    {
        $correctedsequence = $this->getCorrectedSequence();
        $result_array = array();
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        $solved_questions = ilObjTest::_getSolvedQuestions($this->active_id);
        $key = 1;
        foreach ($correctedsequence as $sequence) {
            $question = &ilObjTest::_instanciateQuestion($this->getQuestionForSequence($sequence));
            if (is_object($question)) {
                $worked_through = $question->_isWorkedThrough($this->active_id, $question->getId(), $this->pass);
                $solved = 0;
                if (array_key_exists($question->getId(), $solved_questions)) {
                    $solved = $solved_questions[$question->getId()]["solved"];
                }
                $is_postponed = $this->isPostponedQuestion($question->getId());

                $row = array(
                    "nr" => "$key",
                    "title" => $question->getTitle(),
                    "qid" => $question->getId(),
                    "presented" => $this->isQuestionPresented($question->getId()),
                    "visited" => $worked_through,
                    "solved" => (($solved)?"1":"0"),
                    "description" => $question->getComment(),
                    "points" => $question->getMaximumPoints(),
                    "worked_through" => $worked_through,
                    "postponed" => $is_postponed,
                    "sequence" => $sequence,
                    "obligatory" => ilObjTest::isQuestionObligatory($question->getId()),
                    'isAnswered' => $question->isAnswered($this->active_id, $this->pass)
                );
                
                if (!$obligationsFilterEnabled || $row['obligatory']) {
                    array_push($result_array, $row);
                }
                
                $key++;
            }
        }
        return $result_array;
    }
    
    public function getPass()
    {
        return $this->pass;
    }
    
    public function setPass($pass)
    {
        $this->pass = $pass;
    }
    
    public function hasSequence()
    {
        if ((is_array($this->sequencedata["sequence"])) && (count($this->sequencedata["sequence"]) > 0)) {
            return true;
        } else {
            return false;
        }
    }

    public function hasHiddenQuestions()
    {
        if ((is_array($this->sequencedata["hidden"])) && (count($this->sequencedata["hidden"]) > 0)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function clearHiddenQuestions()
    {
        $this->sequencedata["hidden"] = array();
    }
    
    private function hideCorrectAnsweredQuestions(ilObjTest $testOBJ, $activeId, $pass)
    {
        if ($activeId > 0) {
            $result = $testOBJ->getTestResult($activeId, $pass, true);
            
            foreach ($result as $sequence => $question) {
                if (is_numeric($sequence)) {
                    if ($question['reached'] == $question['max']) {
                        $this->hideQuestion($question['qid']);
                    }
                }
            }
            
            $this->saveToDb();
        }
    }
    
    public function hasStarted(ilTestSession $testSession)
    {
        if ($testSession->getLastSequence() < 1) {
            return false;
        }
        
        // WTF ?? heard about tests with only one question !?
        if ($testSession->getLastSequence() == $this->getFirstSequence()) {
            return false;
        }
                
        return true;
    }
    
    public function openQuestionExists()
    {
        return $this->getFirstSequence() !== false;
    }

    public function getQuestionIds()
    {
        return array_values($this->questions);
    }

    public function questionExists($questionId)
    {
        return in_array($questionId, $this->questions);
    }

    public function setQuestionOptional($questionId)
    {
        $this->optionalQuestions[$questionId] = $questionId;
    }

    public function isQuestionOptional($questionId)
    {
        return isset($this->optionalQuestions[$questionId]);
    }
    
    public function hasOptionalQuestions()
    {
        return (bool) count($this->optionalQuestions);
    }

    public function getOptionalQuestions()
    {
        return $this->optionalQuestions;
    }

    public function clearOptionalQuestions()
    {
        $this->optionalQuestions = array();
    }
    
    public function reorderOptionalQuestionsToSequenceEnd()
    {
        $optionalSequenceKeys = array();
        
        foreach ($this->sequencedata['sequence'] as $index => $sequenceKey) {
            if ($this->isQuestionOptional($this->getQuestionForSequence($sequenceKey))) {
                $optionalSequenceKeys[$index] = $sequenceKey;
                unset($this->sequencedata['sequence'][$index]);
            }
        }
        
        foreach ($optionalSequenceKeys as $index => $sequenceKey) {
            $this->sequencedata['sequence'][$index] = $sequenceKey;
        }
    }

    /**
     * @return boolean
     */
    public function isAnsweringOptionalQuestionsConfirmed()
    {
        return $this->answeringOptionalQuestionsConfirmed;
    }

    /**
     * @param boolean $answeringOptionalQuestionsConfirmed
     */
    public function setAnsweringOptionalQuestionsConfirmed($answeringOptionalQuestionsConfirmed)
    {
        $this->answeringOptionalQuestionsConfirmed = $answeringOptionalQuestionsConfirmed;
    }

    /**
     * @return boolean
     */
    public function isConsiderHiddenQuestionsEnabled()
    {
        return $this->considerHiddenQuestionsEnabled;
    }

    /**
     * @param boolean $considerHiddenQuestionsEnabled
     */
    public function setConsiderHiddenQuestionsEnabled($considerHiddenQuestionsEnabled)
    {
        $this->considerHiddenQuestionsEnabled = $considerHiddenQuestionsEnabled;
    }

    /**
     * @return boolean
     */
    public function isConsiderOptionalQuestionsEnabled()
    {
        return $this->considerOptionalQuestionsEnabled;
    }

    /**
     * @param boolean $considerOptionalQuestionsEnabled
     */
    public function setConsiderOptionalQuestionsEnabled($considerOptionalQuestionsEnabled)
    {
        $this->considerOptionalQuestionsEnabled = $considerOptionalQuestionsEnabled;
    }
}
