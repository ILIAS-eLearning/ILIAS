<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

class QuestionContainer extends AbstractValueObject {

	/**
	 * @var int
	 */
	protected $container_obj_id;


	public function __construct(int $container_obj_id) {
		$this->container_obj_id = $container_obj_id;
	}


	/**
	 * @return int
	 */
	public function getContainerObjId(): int {
		return $this->container_obj_id;
	}
}