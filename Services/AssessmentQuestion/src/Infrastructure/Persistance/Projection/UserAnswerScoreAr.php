<?php

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ilDateTime;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;

/**
 * Class UserAnswerScoreAr
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UserAnswerScoreAr extends AbstractProjectionAr
{

    const STORAGE_NAME = "asq_user_answer_scpre";


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
    private $scored_on;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     */
    protected $user_answer_imported_from = AnswerScoreDto::ANSWER_IMPORTED_SOURCE_TYPE_NONE;
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
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $question_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $revision_key;
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
     * @con_has_field        true
     * @con_fieldtype        integer
     * @con_length           8
     * @con_is_notnull       true
     * @con_is_unique        true
     *
     * @var int
     */
    protected $question_int_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_is_notnull true
     */
    protected $question_title;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype clob
     * @con_is_notnull true
     */
    protected $answer_value;
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
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_is_notnull true
     */
    protected $feedback_type = 0;


    /**
     * @param AnswerScoreDto $answer_score_dto
     *
     * @return UserAnswerScoreAr
     */
    public static function createNew(AnswerScoreDto $answer_score_dto)
    {
        $object = new UserAnswerScoreAr();

        $created = new ilDateTime(time(), IL_CAL_UNIX);
        $object->created = $created->get(IL_CAL_DATETIME);

        $object->scored_on = $answer_score_dto->getScoredOn()->get(IL_CAL_DATETIME);
        $object->user_answer_imported_from = $answer_score_dto->getUserAnswerImportSourceType();
        $object->answerer_id = $answer_score_dto->getAnswererId();
        $object->question_id = $answer_score_dto->getQuestionId();
        $object->revision_key = $answer_score_dto->getRevisionKey();
        $object->container_obj_id = $answer_score_dto->getContainerObjId();
        $object->question_int_id = $answer_score_dto->getQuestionIntId();
        $object->question_title = $answer_score_dto->getQuestionTitle();
        $object->answer_value = $answer_score_dto->getAnswerValue();
        $object->attempt_number = $answer_score_dto->getAttemptNumber();
        $object->requested_hints = $answer_score_dto->getRequestedHints();
        $object->max_points = $answer_score_dto->getMaxPoints();
        $object->reached_points = $answer_score_dto->getReachedPoints();
        $object->percent_solved  = $answer_score_dto->getPercentSolved();
        $object->feedback_type = $answer_score_dto->getAnswerFeedbackType();

        return $object;
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
     * @return mixed
     */
    public function getScoredOn()
    {
        return $this->scored_on;
    }


    /**
     * @return int
     */
    public function getUserAnswerImportedfrom() : int
    {
        return $this->user_answer_imported_from;
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
    public function getAnswerValue() : string
    {
        return $this->answer_value;
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
    public function getFeedbackType() : int
    {
        return $this->feedback_type;
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