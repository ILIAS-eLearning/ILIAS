<?php

namespace srag\IliasComponent\Context\Infrastructure\Persistence\SQL;

use srag\DIC\Database\DatabaseInterface;
use srag\IliasComponent\Dao\Entity\Entity;
use srag\IliasComponent\Infrastructure\Repository\SQL\SQLInfrastructureRepository;

/**
 * Class AbstractSQLRepository
 *
 * @package srag\IliasComponent\Context\Infrastructure\Persistence\SQL
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractSQLRepository implements SQLInfrastructureRepository {

	/**
	 * @var DatabaseInterface
	 */
	protected $db;


	/**
	 * AbstractSQLRepository constructor
	 *
	 * @param DatabaseInterface $db
	 */
	public function __construct(DatabaseInterface $db) {
		$this->db = $db;
	}


	/**
	 * @inheritdoc
	 */
	public function doFindById(int $id): ?Entity {
		// TODO
	}


	/**
	 * @inheritdoc
	 */
	public function doFindByFields(array $fields): array {
		// TODO
	}


	/**
	 * @inheritdoc
	 */
	public function doSave($entity): void {
		// TODO
	}


	/**
	 * @inheritdoc
	 */
	public function doDelete($entity): void {
		// TODO
	}
}
