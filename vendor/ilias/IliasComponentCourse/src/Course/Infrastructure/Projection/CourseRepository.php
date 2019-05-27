<?php

namespace srag\IliasComponentCourse\Course\Query\Projection;

use ilObjCourse;
use srag\IliasComponent\Context\Aggregate\Entity\Entity;
use srag\IliasComponent\Context\Aggregate\Repository\ReadOnlyDomainRepository;
use srag\IliasComponent\Context\Aggregate\Repository\WriteOnlyDomainRepository;
use srag\IliasComponent\Context\Infrastructure\Repository\InfrastructureRepository;

/**
 * Class CourseRepository
 *
 * @package srag\IliasComponentCourse\Course\Course\Query\Repository
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseRepository implements ReadOnlyDomainRepository, WriteOnlyDomainRepository {

	/**
	 * @var InfrastructureRepository
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
	public function findById(int $id): ?Entity {
		return $this->repository->doFindById($id);
	}


	/**
	 * @inheritdoc
	 */
	public function finByCriteria(array $criteria): ?Entity {
		// TODO
	}


	/**
	 * @inheritdoc
	 */
	public function findAllByObjId(int $obj_id): array {
		return $this->repository->doFindByFields([ "obj_id" => $obj_id ]);
	}


	/**
	 * @param Entity $entity
	 */
	public function save(Entity $entity): void {
		$this->repository->doSave($entity);
	}


	/**
	 * @param int $obj_id
	 * @param int $usr_id
	 */
	public function addParticipant(int $obj_id, int $usr_id): void {
		$course = new ilObjCourse($obj_id, false);

		//TODO: Dispatch this!
		$course->getMembersObject()->add($usr_id, IL_CRS_MEMBER);
	}


	/**
	 * @param int $obj_id
	 * @param int $usr_id
	 */
	public function removeParticipant(int $obj_id, int $usr_id): void {
		$course = new ilObjCourse($obj_id, false);

		//TODO: Dispatch this!
		$course->getMembersObject()->delete($usr_id);
	}
}
