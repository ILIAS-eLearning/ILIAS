<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Adapter\ilRepositoryObject;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerIdContract;

class AsqApiIlRepositoryContainerIdContract implements AsqApiContainerIdContract {

	/**
	 * @var int;
	 */
	protected $container_obj_id;
	/**
	 * @var int;
	 */
	protected $container_ref_id;

	public function construct(int $container_obj_id, int $container_ref_id) {
		$this->container_obj_id = $container_obj_id;
		$this->container_ref_id = $container_ref_id;
	}
}