<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Question;

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
abstract class AbstractScoring {
	const SCORING_DEFINITION_SUFFIX = 'Definition';

	/**
	 * @var Question
	 */
	protected $question;

	/**
	 * AbstractScoring constructor.
	 *
	 * @param Question $question
	 * @param array    $configuration
	 */
	public function __construct(Question $question) {
		$this->question = $question;
	}

	abstract function score(Answer $answer) : int;

	/**
	 * @return array|null
	 */
	public static function generateFields(): ?array {
		return [];
	}

	/**
	 * @return string
	 */
	public static function getScoringDefinitionClass(): string {
		return get_called_class() . self::SCORING_DEFINITION_SUFFIX;
	}
}