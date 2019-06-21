<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\Data\Domain\Entity\RevisionId;

/**
 * Interface QuestionRepository
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 */
interface QuestionRepository {
	public function save(Question $question);


	public function byAggregateAndRevisionId(QuestionId $aggregate_id, RevisionId $aggregate_revision);
}