<?php
namespace ILIAS\Modules\Course\Domain\Repository;

use ILIAS\Modules\Course\Domain\Entity\CourseMember;
use ILIAS\Infrasctrutre\Repository\Repository;

class CourseMemberRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function find(string $name, int $year): CourseMember
	{
		return $this->repository->doFind(['name' => $name, 'year' => $year]);
	}


	/**
	 * @param int $obj_id
	 *
	 * @return CourseMember[]
	 */
	public function findallByObjId(int $obj_id): array
	{
		return $this->repository->doFindByFields(['course' => $obj_id]);
	}

	public function save(CourseMember $course_member): CourseMember
	{
		$this->repository->doSave($course_member);
	}

}