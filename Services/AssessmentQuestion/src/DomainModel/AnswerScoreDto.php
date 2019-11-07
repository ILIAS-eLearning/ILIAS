<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\AnswerScoreDto as iAnswerScoreDto;

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
class AnswerScoreDto implements iAnswerScoreDto
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
    protected $requested_hints = 0;
    /**
     * @var int
     */
    protected $hint_points = 0;
    /**
     * @var float
     */
    protected $max_points = 0;
    /**
     * @var float
     */
    protected $reached_points = 0;
    /**
     * " var float
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
     * @param float $max_points
     * @param float $reached_points
     * @param int   $requested_hints
     * @param int   $percent_solved
     * @param int   $answer_feedback_type
     */
    public static function createNew(
        float $max_points,
        float $reached_points,
        int $requested_hints,
        int $hint_points,
        int $percent_solved,
        $answer_feedback_type
    ) : iAnswerScoreDto {
        $object = new AnswerScoreDto();

        $object->max_points = $max_points;
        $object->reached_points = $reached_points;
        $object->requested_hints = $requested_hints;
        $object->hint_points = $hint_points;
        $object->percent_solved = $percent_solved;
        $object->answer_feedback_type = $answer_feedback_type;

        return $object;
    }


    /**
     * @return int
     */
    public function getRequestedHints() : int
    {
        return $this->requested_hints;
    }


    /**
     * @return int
     */
    public function getHintPoints() : int
    {
        return $this->hint_points;
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
}