<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ilDateTime;

/**
 * Class TestAttemptScoreDto
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestAttemptScoreDto
{
    const TEST_ATTEMPT_IMPORTED_SOURCE_TYPE_NONE = 0;
    const TEST_ATTEMPT_SOURCE_TYPE_ILIAS_5_UPGRADE = 1;

    /**
     * @var int
     */
    private $container_obj_id;
    /**
     * @var int
     */
    protected $test_attempt_import_source_type = self::TEST_ATTEMPT_IMPORTED_SOURCE_TYPE_NONE;
    /**
     * @var int
     */
    protected $answerer_id;
    /**
     * @var  int
     */
    private $attempt_number;
    /**
     * @var  ilDateTime
     */
    private $finished_on;
    /**
     * @var  int
     */
    private $anwwered_questions;
    /**
     * @var  int
     */
    private $max_questions;
    /**
     * @var  int
     */
    private $requested_hints;
    /**
     * @var float
     */
    private $reached_points;
    /**
     * @var float
     */
    private $max_points;
    /**
     * @var float
     */
    private $percent_solved = 0;


    /**
     * TestAttemptScoreDto constructor.
     *
     * @param int        $container_obj_id
     * @param int        $answerer_id
     * @param string     $question_id
     * @param string     $revision_key
     * @param int        $attempt_number
     * @param ilDateTime $finished_on
     * @param int        $anwwered_questions
     * @param int        $max_questions
     * @param int        $requested_hints
     * @param float      $reached_points
     * @param float      $max_points
     * @param float      $percent_solved
     */
    public function __construct(int $container_obj_id, int $test_attempt_import_source_type, int $answerer_id, int $attempt_number, ilDateTime $finished_on, int $anwwered_questions, int $max_questions, int $requested_hints, float $reached_points, float $max_points, float $percent_solved)
    {
        $this->container_obj_id = $container_obj_id;
        $this->test_attempt_import_source_type = $test_attempt_import_source_type;
        $this->answerer_id = $answerer_id;
        $this->attempt_number = $attempt_number;
        $this->finished_on = $finished_on;
        $this->anwwered_questions = $anwwered_questions;
        $this->max_questions = $max_questions;
        $this->requested_hints = $requested_hints;
        $this->reached_points = $reached_points;
        $this->max_points = $max_points;
        $this->percent_solved = $percent_solved;
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
    public function getTestAttemptImportSourceType() : int
    {
        return $this->test_attempt_import_source_type;
    }


    /**
     * @return int
     */
    public function getAnswererId() : int
    {
        return $this->answerer_id;
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
    public function getFinishedOn() : ilDateTime
    {
        return $this->finished_on;
    }


    /**
     * @return int
     */
    public function getAnwweredQuestions() : int
    {
        return $this->anwwered_questions;
    }


    /**
     * @return int
     */
    public function getMaxQuestions() : int
    {
        return $this->max_questions;
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
    public function getReachedPoints() : float
    {
        return $this->reached_points;
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
    public function getPercentSolved() : float
    {
        return $this->percent_solved;
    }
}