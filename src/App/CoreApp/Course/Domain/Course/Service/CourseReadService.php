<?php
namespace ILIAS\App\CoreApp\Course\Domain\Service;
use ILIAS\App\Domain\Service\ReadService;
use ILIAS\App\CoreApp\Course\Domain\Entity\Course;

class CourseReadService implements ReadService /*implements CustomerServiceInterface*/
{
	/**
	 * @param array $criteria
	 *
	 * @return Course[]
	 *
	 */
	public function getByCriteria(array $criteria):array
	{
		//TODO
	}


	/**
	 * @param int $id
	 *
	 * @return Course
	 */
	public function get(int $id):Course
	{
		//TODO
	}
}