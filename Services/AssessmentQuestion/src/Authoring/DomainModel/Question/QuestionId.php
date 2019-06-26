<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared;

use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Guid;

/**
 * Class QuestionId
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionId implements AggregateId {
	/**
	 * @var string
	 */
	private $id;


	public function __construct(string $id = null)
	{
		$this->id = $id ?: Guid::createGuid();
	}

	public function getId(): string {
		return $this->id;
	}


	public function equals(AggregateId $anId) {
		return $anId instanceof QuestionId && $this->getId() === $anId->getId();
	}
}