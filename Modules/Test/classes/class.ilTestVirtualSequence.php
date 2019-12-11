<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/interfaces/interface.ilTestQuestionSequence.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestVirtualSequence implements ilTestQuestionSequence
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilObjTest
     */
    protected $testOBJ;

    /**
     * @var ilTestSequenceFactory
     */
    protected $testSequenceFactory;

    /**
     * @var integer
     */
    protected $activeId;

    /**
     * @var array
     */
    protected $questionsPassMap;
    
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ, ilTestSequenceFactory $testSequenceFactory)
    {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
        $this->testSequenceFactory = $testSequenceFactory;
        
        $this->activeId = null;

        $this->questionsPassMap = array();
    }

    public function getActiveId()
    {
        return $this->activeId;
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getQuestionIds()
    {
        return array_keys($this->questionsPassMap);
    }

    public function getQuestionsPassMap()
    {
        return $this->questionsPassMap;
    }

    public function getUniquePasses()
    {
        return array_unique(array_values($this->questionsPassMap));
    }
    
    public function init()
    {
        $passes = $this->getExistingPassesDescendent($this->getActiveId());
        $this->fetchQuestionsFromPasses($this->getActiveId(), $passes);
    }

    private function getExistingPassesDescendent($activeId)
    {
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $passesSelector = new ilTestPassesSelector($this->db, $this->testOBJ);
        $passesSelector->setActiveId($activeId);
        
        $passes = $passesSelector->getExistingPasses();

        rsort($passes, SORT_NUMERIC);

        return $passes;
    }

    protected function getTestSequence($activeId, $pass)
    {
        $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($activeId, $pass);

        $testSequence->loadFromDb();
        $testSequence->loadQuestions();

        $testSequence->setConsiderHiddenQuestionsEnabled(true);
        $testSequence->setConsiderOptionalQuestionsEnabled(true);
        return $testSequence;
    }

    protected function wasAnsweredInThisPass(ilTestSequence $testSequence, $questionId)
    {
        if ($testSequence->isHiddenQuestion($questionId)) {
            return false;
        }

        if (!$testSequence->isQuestionOptional($questionId)) {
            return true;
        }

        if ($testSequence->isAnsweringOptionalQuestionsConfirmed()) {
            return true;
        }

        return false;
    }

    protected function fetchQuestionsFromPasses($activeId, $passes)
    {
        $this->questionsPassMap = array();

        foreach ($passes as $pass) {
            $testSequence = $this->getTestSequence($activeId, $pass);

            foreach ($testSequence->getOrderedSequenceQuestions() as $questionId) {
                if (isset($this->questionsPassMap[$questionId])) {
                    continue;
                }

                if ($this->wasAnsweredInThisPass($testSequence, $questionId)) {
                    $this->questionsPassMap[$questionId] = $pass;
                }
            }
        }
    }
}
