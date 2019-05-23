<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseMemberReadonlyRepository implements ReadOnlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct(Repository $repository) {
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
	public function findAllByObjId(int $obj_id): array
	{
		return $this->repository->doFindByFields(['objId' => $obj_id]);
	}
}