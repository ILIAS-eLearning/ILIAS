<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestFixedQuestionSetQuestion
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/Test
 */
class ilTestFixedQuestionSetQuestion
{
    const DB_TABLE = 'tst_test_question';

    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $questionId;
    /**
     * @var string
     */
    protected $questionUid;
    /**
     * @var int
     */
    protected $testId;
    /**
     * @var int
     */
    protected $sequencePosition;
    /**
     * @var bool
     */
    protected $isObligatory;
    /**
     * @var int
     */
    protected $updateTimestamp;


    /**
     * ilTestFixedQuestionSetQuestion constructor.
     */
    public function __construct()
    {
        $this->id = 0;
        $this->questionId = 0;
        $this->questionUid = '';
        $this->testId = 0;
        $this->sequencePosition = -1;
        $this->isObligatory = false;
        $this->updateTimestamp = 0;
    }


    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getQuestionId() : int
    {
        return $this->questionId;
    }


    /**
     * @param int $questionId
     */
    public function setQuestionId(int $questionId) : void
    {
        $this->questionId = $questionId;
    }


    /**
     * @return string
     */
    public function getQuestionUid() : string
    {
        return $this->questionUid;
    }


    /**
     * @param string $questionUid
     */
    public function setQuestionUid(string $questionUid) : void
    {
        $this->questionUid = $questionUid;
    }


    /**
     * @return int
     */
    public function getTestId() : int
    {
        return $this->testId;
    }


    /**
     * @param int $testId
     */
    public function setTestId(int $testId) : void
    {
        $this->testId = $testId;
    }


    /**
     * @return int
     */
    public function getSequencePosition() : int
    {
        return $this->sequencePosition;
    }


    /**
     * @param int $sequencePosition
     */
    public function setSequencePosition(int $sequencePosition) : void
    {
        $this->sequencePosition = $sequencePosition;
    }


    /**
     * @return bool
     */
    public function isObligatory() : bool
    {
        return $this->isObligatory;
    }


    /**
     * @param bool $isObligatory
     */
    public function setIsObligatory(bool $isObligatory) : void
    {
        $this->isObligatory = $isObligatory;
    }


    /**
     * @return int
     */
    public function getUpdateTimestamp() : int
    {
        return $this->updateTimestamp;
    }


    /**
     * @param int $updateTimestamp
     */
    public function setUpdateTimestamp(int $updateTimestamp) : void
    {
        $this->updateTimestamp = $updateTimestamp;
    }


    /**
     * @param array $dbRow
     */
    public function assignFromDbRow(array $dbRow)
    {
        foreach($dbRow as $field => $value)
        {
            switch($field)
            {
                case 'test_question_id': $this->setId((int)$value); break;
                case 'question_fi': $this->setQuestionId((int)$value); break;
                case 'question_uid': $this->setQuestionUid((string)$value); break;
                case 'test_fi': $this->setTestId((int)$value); break;
                case 'sequence': $this->setSequencePosition((int)$value); break;
                case 'obligatory': $this->setIsObligatory((bool)$value); break;
                case 'tstamp': $this->setUpdateTimestamp((int)$value); break;
            }
        }
    }

    public function save()
    {
        if( $this->getId() )
        {
            $this->update();
        }
        else
        {
            $this->insert();
        }
    }

    protected function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->update($this->getDbTable(),
            [
                'question_fi' => ['integer', $this->getQuestionId()],
                'question_uid' => ['text', $this->getQuestionUid()],
                'test_fi' => ['integer', $this->getTestId()],
                'sequence' => ['integer', $this->getSequencePosition()],
                'obligatory' => ['integer', $this->isObligatory()],
                'tstamp' => ['integer', $this->getUpdateTimestamp()]
            ],
            [
                'test_question_id' => ['integer', $this->getId()]
            ]
        );
    }

    protected function insert()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId( $DIC->database()->nextId('tst_test_question') );

        $DIC->database()->insert($this->getDbTable(), [
            'test_question_id' => ['integer', $this->getId()],
            'question_fi' => ['integer', $this->getQuestionId()],
            'question_uid' => ['text', $this->getQuestionUid()],
            'test_fi' => ['integer', $this->getTestId()],
            'sequence' => ['integer', $this->getSequencePosition()],
            'obligatory' => ['integer', $this->isObligatory()],
            'tstamp' => ['integer', $this->getUpdateTimestamp()]
        ]);
    }

    public function delete()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->manipulateF("DELETE FROM {$this->getDbTable()} WHERE test_question_id = %s",
            ['integer'], [$this->getId()]
        );
    }


    /**
     * @return string
     */
    public function getDbTable()
    {
        return self::DB_TABLE;
    }
}
