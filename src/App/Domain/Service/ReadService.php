<?php

namespace ILIAS\App\Domain\Service;

use ILIAS\App\CoreApp\Course\Domain\Entity\Course;

interface ReadService {

	/**
	 * @param array $criteria
	 *
	 * @return array
	 *
	 */
	public function getByCriteria(array $criteria): array;


	/**
	 * @param int $id
	 *
	 * @return Course
	 */
	public function get(int $id): Course;
}