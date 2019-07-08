<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring;

/**
 * Class Scoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionScoring {

	/**
	 * Scoring constructor.
	 *
	 * @param AnswerOptionScoringPoints[] $answer_option_scoring_points
	 */
	public function __construct(array $answer_option_scoring_points) {
		$this->answer_option_scoring_points = $answer_option_scoring_points;
	}


	/**
	 * @return AnswerOptionScoringPoints[]
	 */
	public function getAnswerOptionScoringPoints() {
		return $this->answer_option_scoring_points;
	}
}