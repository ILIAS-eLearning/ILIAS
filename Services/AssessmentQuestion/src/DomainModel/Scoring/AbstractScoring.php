<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

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
     * @var Question
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


    /**
     * @param float $reached_points
     * @param float $max_points
     *
     * @return int
     */
    public function getAnswerFeedbackType(float $reached_points, float $max_points) : int
    {
        if($max_points === 0) {
            return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_NOT_DETERMINABLLE;
        }
        if($reached_points === $max_points) {
            return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_CORRECT;
        }
        return AnswerScoreDto::ANSWER_FEEDBACK_TYPE_INCORRECT;
    }
}