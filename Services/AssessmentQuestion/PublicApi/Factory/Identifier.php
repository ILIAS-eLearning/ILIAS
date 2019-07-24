<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;


use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\RevisionId;
use ilDateTime;

/**
 * Class Identifier
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Identifier
{
	/**
	 * @param string $questionUuid
	 * @return QuestionIdContract
	 */
	public function questionId(string $questionUuid, string $iliasNicId) : QuestionIdContract
	{
		return new QuestionId($questionUuid, $iliasNicId);
	}
	
	/**
	 * @param string $revisionUuid
	 * @return RevisionIdContract
	 */
	public function revisionId(string $revisionUuid) : RevisionIdContract
	{
		return new RevisionId($revisionUuid, new ilDateTime(time(), IL_CAL_UNIX));
	}
}
