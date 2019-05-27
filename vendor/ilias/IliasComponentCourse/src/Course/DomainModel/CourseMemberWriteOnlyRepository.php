<?php

namespace srag\IliasComponentCourse\Course\Comand;

use srag\IliasComponent\Context\Command\Aggregate\Entity;
use srag\IliasComponent\Context\Command\WriteOnlyDomainRepository;
use srag\IliasComponent\Context\Infrastructure\Repository\InfrastructureRepository;

/**
 * Class CourseMemberWriteOnlyRepository
 *
 * @package srag\IliasComponentCourse\Course\Course\Query\Repository
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseMemberWriteOnlyRepository implements WriteOnlyDomainRepository {

	/**
	 * @var WriteOnlyDomainRepository
	 */
	protected $repository;


	/**
	 * @inheritdoc
	 */
	public function __construct(InfrastructureRepository $repository) {
		$this->repository = $repository;
	}


	/**
	 * @inheritdoc
	 */
	public function save(Entity $entity): void {
		$this->repository->Save($entity);
	}


	public function Delete(Entity $entity): void {
		$this->repository->Delete($entity);
	}
}
