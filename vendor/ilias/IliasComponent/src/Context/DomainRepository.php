<?php

namespace srag\IliasComponent\Context;

use srag\IliasComponent\Context\Infrastructure\Repository\InfrastructureRepository;

/**
 * Interface DomainRepository
 *
 * @package srag\IliasComponent\Command
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DomainRepository {

	/**
	 * DomainRepository constructor
	 *
	 * @param InfrastructureRepository $repository
	 */
	public function __construct(InfrastructureRepository $repository);
}
