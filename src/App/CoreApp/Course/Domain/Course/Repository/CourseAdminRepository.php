<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseAdmin;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseAdminRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function find(): CourseAdmin
	{
		//TODO
	}


	/**
	 * @param int $obj_id
	 *
	 * @return CourseAdmin[]
	 */
	public function findallByObjId(int $obj_id): array
	{
		return $this->repository->doFindByFields(['course' => $obj_id]);
	}

	public function save(CourseAdmin $course_member): CourseAdmin
	{
		$this->repository->doSave($course_member);
	}

}