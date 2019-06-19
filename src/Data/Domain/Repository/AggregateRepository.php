<?php


namespace ILIAS\Data\Domain\Repository;

use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\AggregateRoot;

/**
 * Interface AggregateRepository
 * @package ILIAS\Data\Domain
 */
interface AggregateRepository {

	/**
	 * @param AggregateId $aggregate_id
	 * @return AggregateRoot
	 */
	public function findById(AggregateId $aggregate_id): AggregateRoot;
}