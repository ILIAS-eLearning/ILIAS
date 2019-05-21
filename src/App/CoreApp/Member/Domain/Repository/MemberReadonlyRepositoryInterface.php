<?php
namespace ILIAS\App\CoreApp\Member\Domain\Repository;

use ILIAS\App\CoreApp\Member\Domain\Entity\Member;
use ILIAS\App\Infrasctrutre\Repository\Repository;

interface MemberReadonlyRepositoryInterface
{
	public function find():?Member;


	/**
	 * @param int $obj_id
	 *
	 * @return Member[]
	 */
	public function findAllByObjId(int $obj_id):array;
}