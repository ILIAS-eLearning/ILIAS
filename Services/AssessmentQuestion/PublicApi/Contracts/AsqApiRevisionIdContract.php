<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ilDateTime;

interface AsqApiRevisionIdContract {

	/**
	 * @return string
	 */
	public function getRevisionId():string;

	public function getCreatedOn():ilDateTime;

	public function getIliasNicId():string;


}