<?php
namespace ILIAS\App\CoreApp\Member\Domain\Repository;

use ILIAS\App\CoreApp\Member\Domain\Entity\Member;
use ILIAS\App\Infrasctrutre\Repository\Repository;
use ilObjCourse;
use ilObject;

class MemberWriteonlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function addParticipant($objId,$usr_id)
	{
		global $DIC;
		$arr_ref_id = ilObject::_getAllReferences($objId);
		$course = new ilObjCourse(array_shift($arr_ref_id),true);

		//TODO Dispatch this!
		$DIC->rbac()->admin()->assignUser($course->getDefaultMemberRole(),$usr_id);
	}
}