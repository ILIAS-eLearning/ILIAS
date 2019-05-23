<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseMemberWriteonlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}


}