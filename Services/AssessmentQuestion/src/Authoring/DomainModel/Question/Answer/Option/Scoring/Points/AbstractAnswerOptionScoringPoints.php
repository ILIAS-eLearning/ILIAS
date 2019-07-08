<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\Points;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\AnswerOptionScoringPoints;

/**
 * Class AbstractPoints
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\Points
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractAnswerOptionScoringPoints implements AnswerOptionScoringPoints {
	/**
	 * @var int
	 */
	protected $points;


	/**
	 * @param int $points
	 */
	public function __construct(int $points) {
		$this->configured_points = $points;
	}


	public function getPoints(): int {
		return $this->points;
	}
}