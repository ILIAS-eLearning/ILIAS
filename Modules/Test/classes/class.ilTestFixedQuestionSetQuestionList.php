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
        $this->questions = [];
        $this->testId = $testId;
    }


    /**
     * @param ilTestFixedQuestionSetQuestion $question
     */
    public function addQuestion(ilTestFixedQuestionSetQuestion $question)
    {
        $this->questions[] = $question;
    }


    public function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "
            SELECT * FROM {$this->getDbTable()}
            WHERE test_fi = %s
            ORDER BY sequence ASC
        ";

        $res = $DIC->queryF($query, ['integer'], [$this->testId]);

        while($row = $DIC->database()->fetchAssoc($res))
        {
            $question = new ilTestFixedQuestionSetQuestion();
            $question->assignFromDbRow($row);
            $this->addQuestion($question);
        }
    }

    public function save()
    {
        foreach($this as $question)
        {
            $question->save();
        }
    }

    public function delete()
    {
        foreach($this as $question)
        {
            $question->delete();
        }
    }


    /**
     * @return string
     */
    public function getDbTable()
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
        return $this->key() !== false;
    }


    /**
     * @return ilTestFixedQuestionSetQuestion
     */
    public function rewind()
    {
        return reset($this->questions);
    }
}
