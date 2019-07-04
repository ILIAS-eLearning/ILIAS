<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared;

use Ramsey\Uuid\Uuid;

/**
 * Class QuestionId
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AggregateId {
	/**
	 * @var string
	 */
	private $id;


	public function __construct(string $id = null)
	{
		$this->id = $id ?: Uuid::uuid4();
	}

	public function getId(): string {
		return $this->id;
	}


	public function equals(AggregateId $anId) : bool{
		return $this->getId() === $anId->getId();
	}
}