<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;


/**
 * Class UserAnswerScoring
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
interface ScoredUserAnswerDto
{
	//TODO -> zu Consuming Service!!

	public function getQuestionRevisionId(): QuestionRevisionId;
	
	public function getUserAnswerId(): UserAnswerId;
	
	public function getUserId(): int;
	
	public function getSubmittedOn();
	
	public function isCorrect(): bool;
	
	public function getPoints(): int;
	
}
