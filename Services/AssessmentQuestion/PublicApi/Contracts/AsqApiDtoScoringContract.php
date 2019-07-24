<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ilDateTime;

interface AsqApiDtoScoringContract {

	public function getQuestionUuid(): string;


	public function getUserId(): int;


	public function getSubmittedOn(): ilDateTime;


	public function isUserAnswerCorrect(): bool;


	public function getPoints(): int;
}