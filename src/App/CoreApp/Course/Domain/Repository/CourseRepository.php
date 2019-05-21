<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\Course;
use ILIAS\App\Infrasctrutre\Repository\Repository;
use ilObjCourse;
use \ilObject;

class CourseRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function find($objId): Course
	{
		return $this->repository->doFind(['objId' => $objId]);
	}

	public function addParticipant($objId,$usr_id)
	{
		global $DIC;
		$arr_ref_id = ilObject::_getAllReferences($objId);
		$course = new ilObjCourse(array_shift($arr_ref_id),true);

		//TODO Dispatch this!
		$DIC->rbac()->admin()->assignUser($course->getDefaultMemberRole(),$usr_id);

		return $this->repository->doFind(['objId' => $objId]);
	}


	/**
	 * @param int $obj_id
	 *
	 * @return Course[]
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