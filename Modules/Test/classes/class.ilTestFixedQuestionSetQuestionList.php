<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestFixedQuestionSetQuestionList
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/Test
 */
class ilTestFixedQuestionSetQuestionList implements Iterator
{

    /**
     * @var ilTestFixedQuestionSetQuestion[]
     */
    protected $questions;
    /**
     * @var int
     */
    protected $testId;


    /**
     * ilTestFixedQuestionSetQuestionList constructor.
     *
     * @param int $testId
     */
    public function __construct(int $testId)
    {
        $this->testId = $testId;
        $this->load();
    }


    public function deleteList()
    {
        $this->resetQuestions();
        $this->deleteAllExisting();
    }


    /**
     * @param int    $questionId
     * @param string $questionUid
     *
     * @return ilTestFixedQuestionSetQuestion
     */
    public function appendQuestion(int $questionId, string $questionUid) : ilTestFixedQuestionSetQuestion
    {
        $testQuestion = new ilTestFixedQuestionSetQuestion();

        $testQuestion->setTestId($this->testId);
        $testQuestion->setQuestionId($questionId);
        $testQuestion->setQuestionUid($questionUid);

        $testQuestion->setSequencePosition($this->getNextPosition());
        $testQuestion->setIsObligatory(false);
        $testQuestion->setUpdateTimestamp(time());

        $this->addQuestion($testQuestion);
        $this->save();

        return $testQuestion;
    }


    /**
     * @return bool
     */
    public function hasQuestions() : bool
    {
        return (bool)$this->getNumQuestions();
    }


    /**
     * @return int
     */
    public function getNumQuestions() : int
    {
        return count($this->questions);
    }


    /**
     * @return int
     */
    protected function getNextPosition() : int
    {
        return $this->getNumQuestions() + 1;
    }


    protected function resetQuestions()
    {
        $this->questions = [];
    }


    /**
     * @param ilTestFixedQuestionSetQuestion $question
     */
    protected function addQuestion(ilTestFixedQuestionSetQuestion $question)
    {
        $this->questions[$question->getSequencePosition()] = $question;
    }


    /**
     * @return array
     */
    protected function getAllTestQuestionIds() : array
    {
        $allTestQuestionIds = [];

        foreach($this as $question)
        {
            $allTestQuestionIds[] = $question->getId();
        }

        return $allTestQuestionIds;
    }


    protected function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->resetQuestions();

        $query = "
            SELECT * FROM {$this->getDbTable()}
            WHERE test_fi = %s
            ORDER BY sequence ASC
        ";

        $res = $DIC->database()->queryF($query, ['integer'], [$this->testId]);

        while($row = $DIC->database()->fetchAssoc($res))
        {
            $question = new ilTestFixedQuestionSetQuestion();
            $question->assignFromDbRow($row);
            $this->addQuestion($question);
        }
    }

    protected function save()
    {
        foreach($this as $question)
        {
            $question->save();
        }

        $this->cleanupMissingDeletion();
    }

    protected function cleanupMissingDeletion()
    {
        if( !$this->hasQuestions() )
        {
            $this->deleteAllExisting();
        }
        else
        {
            $this->deleteNonExisting();
        }
    }

    protected function deleteAllExisting()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->manipulateF(
            "DELETE FROM {$this->getDbTable()} WHERE test_fi = %s",
            ['integer'], [$this->testId]
        );
    }

    protected function deleteNonExisting()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $NOT_IN_tstQstIds = $DIC->database()->in(
            'test_question_id', $this->getAllTestQuestionIds(), true, 'integer'
        );

        $DIC->database()->manipulateF(
            "DELETE FROM {$this->getDbTable()} WHERE test_fi = %s AND $NOT_IN_tstQstIds",
            ['integer'], [$this->testId]
        );
    }

    /**
     * @return string
     */
    protected function getDbTable()
    {
        return ilTestFixedQuestionSetQuestion::DB_TABLE;
    }


    /**
     * @return ilTestFixedQuestionSetQuestion
     */
    public function current()
    {
        return current($this->questions);
    }


    /**
     * @return ilTestFixedQuestionSetQuestion
     */
    public function next()
    {
        return next($this->questions);
    }


    /**
     * @return int|mixed|string|null
     */
    public function key()
    {
        return key($this->questions);
    }


    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }


    /**
     * @return ilTestFixedQuestionSetQuestion
     */
    public function rewind()
    {
        return reset($this->questions);
    }
}
