<?php

namespace srag\IliasComponent\Context\Command;

use srag\IliasComponent\Context\Command\Aggregate\Entity;
use srag\IliasComponent\Context\DomainRepository;

/**
 * Interface WriteOnlyDomainRepository
 *
 * @package srag\IliasComponent\Command
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface WriteOnlyDomainRepository extends DomainRepository {

	/**
	 * @param Entity $entity
	 */
	public function Save(Entity $entity): void;


	/**
	 * @param Entity $entity
	 */
	public function Delete(Entity $entity): void;

}
