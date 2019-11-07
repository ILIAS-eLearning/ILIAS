<?php

namespace ILIAS\Modules\Test\Result;

use ActiveRecord;

/**
 * Class TestResultAr
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestResultAr extends ActiveRecord
{

    const STORAGE_NAME = "tst_test_result";
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $test_result_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $active_fi;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $question_fi;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $points;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $pass;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $manual;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $tstamp;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     */
    protected $hint_count;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     */
    protected $hint_points;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $answered;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     */
    protected $step;


    /**
     * @param int $active_fi
     * @param int $question_fi
     * @param int $points
     * @param int $pass
     * @param int $manual
     * @param int $tstamp
     * @param int $hint_count
     * @param int $hint_points
     * @param int $answered
     * @param int $step
     *
     * @return TestResultAr
     */
    public static function createNew(
        int $test_result_id,
        int $active_fi,
        int $question_fi,
        int $points,
        int $pass,
        int $manual,
        int $tstamp,
        int $hint_count,
        int $hint_points,
        int $answered,
        int $step
    ) : TestResultAr {
        $object = new TestResultAr();

        $object->test_result_id = $test_result_id;
        $object->active_fi = $active_fi;
        $object->question_fi = $question_fi;
        $object->points = $points;
        $object->pass = $pass;
        $object->manual = $manual;
        $object->tstamp = $tstamp;
        $object->hint_count = $hint_count;
        $object->hint_points = $hint_points;
        $object->answered = $answered;
        $object->step = $step;

        return $object;
    }


    /**
     * @return int
     */
    public function getTestResultId() : int
    {
        return $this->test_result_id;
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
    public function getQuestionFi() : int
    {
        return $this->question_fi;
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
    public function getPass() : int
    {
        return $this->pass;
    }


    /**
     * @return int
     */
    public function getManual() : int
    {
        return $this->manual;
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
    public function getAnswered() : int
    {
        return $this->answered;
    }


    /**
     * @return int
     */
    public function getStep() : int
    {
        return $this->step;
    }


    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
}