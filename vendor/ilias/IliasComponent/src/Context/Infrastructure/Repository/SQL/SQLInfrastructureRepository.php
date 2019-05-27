<?php

namespace srag\IliasComponent\Infrastructure\Repository\SQL;

use srag\DIC\Database\DatabaseInterface;
use srag\IliasComponent\Infrastructure\Repository\InfrastructureRepository;

/**
 * Interface SQLInfrastructureRepository
 *
 * @package srag\IliasComponent\Infrastructure\Repository\SQL
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface SQLInfrastructureRepository extends InfrastructureRepository {

	/**
	 * SQLInfrastructureRepository constructor
	 *
	 * @param DatabaseInterface $db
	 */
	public function __construct(DatabaseInterface $db);
}
