<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\AnswerOptionScoring;

/**
 * Interface AnswerOptionWithPoints
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\Points
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AnswerOptionHasScoring {

	/**
	 * @return AnswerOptionScoring
	 */
	public function getAnswerOptionScoring();
}