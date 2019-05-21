<?php
namespace ILIAS\App\CoreApp\Member\Domain\Repository;

use ILIAS\App\CoreApp\Member\Domain\Entity\Member;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class MemberReadonlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function find(): Member
	{
		//TODO
	}


	/**
	 * @param int $obj_id
	 *
	 * @return Member[]
	 */
	public function findallByObjId(int $obj_id): array
	{
		return $this->repository->doFindByFields(['objId' => $obj_id]);
	}
}