<?php

namespace srag\IliasComponent\Context\Infrastructure\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use srag\IliasComponent\Context\Infrastructure\Repository\InfrastructureRepository;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\DoctrineEntityManager;

/**
 * Interface DoctrineInfrastructureRepository
 *
 * @package srag\IliasComponent\Infrastructure\Repository\Doctrine
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DoctrineInfrastructureRepository extends InfrastructureRepository {

	/**
	 * DoctrineInfrastructureRepository constructor
	 *
	 * @param EntityManager $em
	 */
	public function __construct(DoctrineEntityManager $em);
}
