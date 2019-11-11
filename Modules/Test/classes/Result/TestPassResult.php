<?php

namespace ILIAS\Modules\Test\Result;


/**
 * Class TestPassResult
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestPassResult
{
    const STORAGE_NAME = "tst_test_result";
    /**
     * @var int
     */
    protected $active_fi;
    /**
     * @var int
     */
    protected $pass;
    /**
     * @var int
     */
    protected $points;
    /**
     * @var int
     */
    protected $maxpoints;
    /**
     * @var int
     */
    protected $questioncount;
    /**
     * @var int
     */
    protected $answeredquestions;
    /**
     * @var int
     */
    protected $workingtime;
    /**
     * @var int
     */
    protected $tstamp;
    /**
     * @var int
     */
    protected $hint_count;
    /**
     * @var int
     */
    protected $hint_points;
    /**
     * @var int
     */
    protected $obligations_answered;
    /**
     * @var string
     */
    protected $exam_id;


    /**
     * TestPassResult constructor.
     *
     * @param int $active_fi
     * @param int $pass
     * @param int $points
     * @param int $maxpoints
     * @param int $questioncount
     * @param int $answeredquestions
     * @param int $workingtime
     * @param int $tstamp
     * @param int $hint_count
     * @param int $hint_points
     * @param int $obligations_answered
     * @param string $exam_id
     */
    public static function createNew(
        int $active_fi,
        int $pass,
        int $points,
        int $maxpoints,
        int $questioncount,
        int $answeredquestions,
        int $workingtime,
        int $tstamp,
        int $hint_count,
        int $hint_points,
        int $obligations_answered,
        string $exam_id
    ) {
        $object = new TestPassResult();

        $object->active_fi = $active_fi;
        $object->pass = $pass;
        $object->points = $points;
        $object->maxpoints = $maxpoints;
        $object->questioncount = $questioncount;
        $object->answeredquestions = $answeredquestions;
        $object->workingtime = $workingtime;
        $object->tstamp = $tstamp;
        $object->hint_count = $hint_count;
        $object->hint_points = $hint_points;
        $object->obligations_answered = $obligations_answered;
        $object->exam_id = $exam_id;

        return $object;
    }


    /**
     * @return int
     */
    public function getActiveFi() : int
    {
        return $this->active_fi;
    }


    /**
     * @return int
     */
    public function getPass() : int
    {
        return $this->pass;
    }


    /**
     * @return int
     */
    public function getPoints() : int
    {
        return $this->points;
    }


    /**
     * @return int
     */
    public function getMaxpoints() : int
    {
        return $this->maxpoints;
    }


    /**
     * @return int
     */
    public function getQuestioncount() : int
    {
        return $this->questioncount;
    }


    /**
     * @return int
     */
    public function getAnsweredquestions() : int
    {
        return $this->answeredquestions;
    }


    /**
     * @return int
     */
    public function getWorkingtime() : int
    {
        return $this->workingtime;
    }


    /**
     * @return int
     */
    public function getTstamp() : int
    {
        return $this->tstamp;
    }


    /**
     * @return int
     */
    public function getHintCount() : int
    {
        return $this->hint_count;
    }


    /**
     * @return int
     */
    public function getHintPoints() : int
    {
        return $this->hint_points;
    }


    /**
     * @return int
     */
    public function getObligationsAnswered() : int
    {
        return $this->obligations_answered;
    }


    /**
     * @return string
     */
    public function getExamId() : string
    {
        return $this->exam_id;
    }




    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
}