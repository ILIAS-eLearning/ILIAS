<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

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

    const CSS_CLASS_FEEDBACK_TYPE_CORRECT = 'ilc_qfeedr_FeedbackRight';
    const CSS_CLASS_FEEDBACK_TYPE_WRONG = 'ilc_qfeedw_FeedbackWrong';

    /**
     * @var int
     */
    private $reached_points;
    /**
     * @var int
     */
    private $max_points;
    /**
     * @var int
     */
    private $answer_feedback_type;


    /**
     * ScoreDto constructor.
     *
     * @param int $reached_points
     * @param int $max_points
     * @param int $answer_feedback
     */
    public function __construct(int $reached_points, int $max_points, int $answer_feedback_type)
    {
        $this->reached_points = $reached_points;
        $this->max_points = $max_points;
        $this->answer_feedback_type = $answer_feedback_type;
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
    public function getMaxPoints() : int
    {
        return $this->max_points;
    }


    /**
     * @return int
     */
    public function getAnswerFeedbackType() : int
    {
        return $this->answer_feedback_type;
    }
}