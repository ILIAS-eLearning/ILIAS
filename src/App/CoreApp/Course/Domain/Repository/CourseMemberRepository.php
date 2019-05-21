<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseMemberRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function find(): CourseMember
	{
		//TODO
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
}