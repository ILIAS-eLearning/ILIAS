<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ilDateTime;

/**
 * Class ScoreDto
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerScoreDto
{

    const ANSWER_FEEDBACK_TYPE_CORRECT = 1;
    const ANSWER_FEEDBACK_TYPE_INCORRECT = 2;
    const ANSWER_FEEDBACK_TYPE_NOT_DETERMINABLLE = 3;

    const ANSWER_IMPORTED_SOURCE_TYPE_NONE = 0;
    const ANSWER_IMPORTED_SOURCE_TYPE_ILIAS_5_UPGRADE = 1;

    const CSS_CLASS_FEEDBACK_TYPE_CORRECT = 'ilc_qfeedr_FeedbackRight';
    const CSS_CLASS_FEEDBACK_TYPE_WRONG = 'ilc_qfeedw_FeedbackWrong';

    /**
     * @var int
     */
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $user_answer_import_source_type = self::ANSWER_IMPORTED_SOURCE_TYPE_NONE;
    /**
     * @var int
     */
    protected $answerer_id;
    /**
     * @var string
     */
    protected $question_id;
    /**
     * @var string
     */
    protected $revision_key;
    /**
     * @var int
     */
    protected $attempt_number;
    /**
     * @var  ilDateTime
     */
    protected $scored_on;
    /**
     * @var int
     */
    protected $question_int_id;
    /**
     * @var string
     */
    protected $question_title;
    /**
     * @var string
     */
    protected $answer_value;
    /**
     * @var int
     */
    protected $requested_hints = 0;
    /**
     * @var float
     */
    protected $max_points = 0;
    /**
     * @var float
     */
    protected $reached_points = 0;
    /**
     " var float
     */
    protected $percent_solved = 0;
    /**
     * @var int
     *
     */
    protected $answer_feedback_type = self::ANSWER_FEEDBACK_TYPE_NOT_DETERMINABLLE;


    /**
     * AnswerScoreDto constructor.
     *
     * @param int        $container_obj_id
     * @param int        $user_answer_import_source_type
     * @param int        $answerer_id
     * @param string     $question_id
     * @param string     $revision_key
     * @param int        $attempt_number
     * @param ilDateTime $scored_on
     * @param int        $question_int_id
     * @param string     $question_title
     * @param string     $answer_value
     * @param int        $requested_hints
     * @param float      $max_points
     * @param float      $reached_points
     * @param int        $percent_solved
     * @param int        $answer_feedback_type
     */
    public function __construct(
        int $container_obj_id,
        int $user_answer_import_source_type,
        int $answerer_id,
        string $question_id,
        string $revision_key,
        int $attempt_number,
        ilDateTime $scored_on,
        int $question_int_id,
        string $question_title,
        string $answer_value,
        int $requested_hints,
        float $max_points,
        float $reached_points,
        int $percent_solved,
        int $answer_feedback_type
    ) {
        $this->container_obj_id = $container_obj_id;
        $this->user_answer_import_source_type = $user_answer_import_source_type;
        $this->answerer_id = $answerer_id;
        $this->question_id = $question_id;
        $this->revision_key = $revision_key;
        $this->attempt_number = $attempt_number;
        $this->scored_on = $scored_on;
        $this->question_int_id = $question_int_id;
        $this->question_title = $question_title;
        $this->answer_value = $answer_value;
        $this->requested_hints = $requested_hints;
        $this->max_points = $max_points;
        $this->reached_points = $reached_points;
        $this->percent_solved = $percent_solved;
        $this->answer_feedback_type = $answer_feedback_type;
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
    public function getUserAnswerImportSourceType() : int
    {
        return $this->user_answer_import_source_type;
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
    public function getAttemptNumber() : int
    {
        return $this->attempt_number;
    }


    /**
     * @return ilDateTime
     */
    public function getScoredOn() : ilDateTime
    {
        return $this->scored_on;
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
    public function getQuestionTitle() : string
    {
        return $this->question_title;
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
    public function getRequestedHints() : int
    {
        return $this->requested_hints;
    }


    /**
     * @return float
     */
    public function getMaxPoints() : float
    {
        return $this->max_points;
    }


    /**
     * @return float
     */
    public function getReachedPoints() : float
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
    public function getAnswerFeedbackType() : int
    {
        return $this->answer_feedback_type;
    }




    /**
     * ScoreDto constructor.
     *
     * @param int $reached_points
     * @param int $max_points
     * @param int $answer_feedback
     */
    /*
    public function __construct(float $reached_points, float $max_points, int $answer_feedback_type)
    {
        $this->reached_points = $reached_points;
        $this->max_points = $max_points;
        if($max_points > 0) {
            $this->percent_solved = $reached_points / $max_points * 100;
        }
        $this->answer_feedback_type = $answer_feedback_type;
    }*/

}