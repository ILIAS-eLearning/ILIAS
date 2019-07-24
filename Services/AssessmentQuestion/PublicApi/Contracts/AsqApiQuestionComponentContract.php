<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

interface AsqApiQuestionComponentContract {

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