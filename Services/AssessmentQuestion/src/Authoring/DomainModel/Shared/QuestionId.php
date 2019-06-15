<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared;

use  ILIAS\Data\Domain\Entity\AggregateId;

/**
 * Class QuestionId
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionId implements AggregateId {

	public function __construct($id = null)
	{
		$this->id = $id ?: uniqid();
	}
	public function id()
	{
		return $this->id;
	}
	public function equals(AggregateId $anId)
	{
		return $this->id === $anId->id();
	}
}