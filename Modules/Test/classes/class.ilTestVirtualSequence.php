<?php declare(strict_types=1);
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
    protected ilDBInterface $db;

    protected ilObjTest $testOBJ;

    protected ilTestSequenceFactory $testSequenceFactory;

    protected ?int $activeId;

    protected array $questionsPassMap;
    
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ, ilTestSequenceFactory $testSequenceFactory)
    {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
        $this->testSequenceFactory = $testSequenceFactory;
        
        $this->activeId = null;

        $this->questionsPassMap = array();
    }

    public function getActiveId() : ?int
    {
        return $this->activeId;
    }

    public function setActiveId(int $activeId) : void
    {
        $this->activeId = $activeId;
    }

    public function getQuestionIds() : array
    {
        return array_keys($this->questionsPassMap);
    }

    public function getQuestionsPassMap() : array
    {
        return $this->questionsPassMap;
    }

    public function getUniquePasses() : array
    {
        return array_unique(array_values($this->questionsPassMap));
    }
    
    public function init() : void
    {
        $passes = $this->getExistingPassesDescendent($this->getActiveId());
        $this->fetchQuestionsFromPasses($this->getActiveId(), $passes);
    }

    private function getExistingPassesDescendent($activeId) : array
    {
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $passesSelector = new ilTestPassesSelector($this->db, $this->testOBJ);
        $passesSelector->setActiveId($activeId);
        
        $passes = $passesSelector->getExistingPasses();

        rsort($passes, SORT_NUMERIC);

        return $passes;
    }

    /**
     * @return ilTestSequenceDynamicQuestionSet|ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet
     */
    protected function getTestSequence(int $activeId, int $pass)
    {
        $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($activeId, $pass);

        $testSequence->loadFromDb();
        $testSequence->loadQuestions();

        $testSequence->setConsiderHiddenQuestionsEnabled(true);
        $testSequence->setConsiderOptionalQuestionsEnabled(true);
        return $testSequence;
    }

    protected function wasAnsweredInThisPass(ilTestSequence $testSequence, $questionId) : bool
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

    /**
     * @param int[] $passes
     */
    protected function fetchQuestionsFromPasses(int $activeId, array $passes) : void
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
