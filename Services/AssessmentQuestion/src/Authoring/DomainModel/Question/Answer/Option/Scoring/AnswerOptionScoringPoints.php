<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring;

/**
 * Interface AnswerOptionScoringPoints
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AnswerOptionScoringPoints {
	public function getPoints();
}