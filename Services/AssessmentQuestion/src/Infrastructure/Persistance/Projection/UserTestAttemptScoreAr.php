<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ilDateTime;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\TestAttemptScoreDto;

/**
 * Class UserTestAttemptScoreAr
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UserTestAttemptScoreAr extends AbstractProjectionAr
{

    const STORAGE_NAME = "asq_user_test_score";

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
    protected $id;
    /**
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    protected $created;
    /**
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    private $finished_on;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $test_attempt_imported_from = TestAttemptScoreDto::TEST_ATTEMPT_IMPORTED_SOURCE_TYPE_NONE;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $answerer_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $container_obj_id;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_is_notnull true
     */
    protected $attempt_number;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_is_notnull true
     */
    protected $requested_hints = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype float
     * @con_is_notnull true
     */
    protected $max_points = 0;
    /**
     * @con_has_field true
     * @con_fieldtype float
     * @con_is_notnull true
     */
    protected $reached_points = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype float
     * @con_is_notnull true
     */
    protected $percent_solved = 0;




    public static function createNew(TestAttemptScoreDto $test_attempt_score_dto) {

        $object = new UserTestAttemptScoreAr();


        $created = new ilDateTime(time(), IL_CAL_UNIX);
        $object->created = $created->get(IL_CAL_DATETIME);

        $object->finished_on = $test_attempt_score_dto->getFinishedOn()->get(IL_CAL_DATETIME);
        $object->test_attempt_imported_from = $test_attempt_score_dto->getTestAttemptImportSourceType();
        $object->answerer_id = $test_attempt_score_dto->getAnswererId();
        $object->container_obj_id = $test_attempt_score_dto->getContainerObjId();
        $object->attempt_number = $test_attempt_score_dto->getAttemptNumber();
        $object->requested_hints = $test_attempt_score_dto->getRequestedHints();
        $object->max_points = $test_attempt_score_dto->getMaxPoints();
        $object->reached_points = $test_attempt_score_dto->getReachedPoints();
        $object->percent_solved = $test_attempt_score_dto->getPercentSolved();
    }


    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @return int
     */
    public function getTestattemptImportedfrom() : int
    {
        return $this->test_attempt_imported_from;
    }


    /**
     * @return string
     */
    public function getQuestionTitle() : string
    {
        return $this->question_title;
    }


    /**
     * @return int
     */
    public function getAnswererId() : int
    {
        return $this->answerer_id;
    }


    /**
     * @return string
     */
    public function getQuestionId() : string
    {
        return $this->question_id;
    }


    /**
     * @return string
     */
    public function getRevisionKey() : string
    {
        return $this->revision_key;
    }


    /**
     * @return int
     */
    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }


    /**
     * @return int
     */
    public function getQuestionIntId() : int
    {
        return $this->question_int_id;
    }


    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }


    /**
     * @return int
     */
    public function getAttemptNumber() : int
    {
        return $this->attempt_number;
    }


    /**
     * @return int
     */
    public function getMaxPoints() : int
    {
        return $this->max_points;
    }


    /**
     * @return int
     */
    public function getReachedPoints() : int
    {
        return $this->reached_points;
    }


    /**
     * @return int
     */
    public function getPercentSolved() : int
    {
        return $this->percent_solved;
    }


    /**
     * @return int
     */
    public function getRequestedHints() : int
    {
        return $this->requested_hints;
    }




    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
}