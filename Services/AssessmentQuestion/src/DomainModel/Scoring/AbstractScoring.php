<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ilDateTime;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\AnswerScoreDto as iAnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

/**
 * Abstract Class AbstractScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractScoring
{

    const SCORING_DEFINITION_SUFFIX = 'Definition';
    /**
     * @var QuestionDto
     */
    protected $question;


    /**
     * AbstractScoring constructor.
     *
     * @param QuestionDto $question
     * @param array       $configuration
     */
    public function __construct(QuestionDto $question)
    {
        $this->question = $question;
    }


    abstract function score(Answer $answer) : AnswerScoreDto;


    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config) : ?array
    {
        return [];
    }


    public static abstract function readConfig();


    /**
     * @return string
     */
    public static function getScoringDefinitionClass() : string
    {
        return get_called_class() . self::SCORING_DEFINITION_SUFFIX;
    }


    public static abstract function isComplete(Question $question) : bool;


    /**
     * @param float $reached_points
     * @param float $max_points
     *
     * @return int
     */
    public function getAnswerFeedbackType(float $reached_points, float $max_points) : int
    {
        if ($max_points === 0) {
            return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_NOT_DETERMINABLLE;
        }
        if ($reached_points === $max_points) {
            return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_CORRECT;
        }

        return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_INCORRECT;
    }


    protected function createScoreDto(Answer $answer, float $max_points, float $reached_points, $answer_feedback_type) : iAnswerScoreDto
    {

        $percent_solved = 0;
        if ($max_points > 0) {
            $percent_solved = $reached_points / $max_points * 100;
        }

        return AnswerScoreDto::createNew(
            $max_points,
            $reached_points,
            0, //TODO
            0, //TODO
            $percent_solved,
            $answer_feedback_type);
    }
}