<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

/**
 * Interface QuestionComponentContract
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface QuestionComponentContract {

	/**
	 * @return string
	 *
	 * Generates HTML code to display the current question
	 */
	public function render(): string;


	/**
	 * @return bool
	 */
	public function hasInlineFeedback(): bool;


	/**
	 * @return bool
	 */
	public function isAutosaveable(): bool;
}