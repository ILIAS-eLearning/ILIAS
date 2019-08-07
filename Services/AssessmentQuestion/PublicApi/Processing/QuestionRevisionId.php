<?php
namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

class QuestionRevisionId {

	/**
	 * @var string
	 */
	protected $uuid;

	public function __construct(string $uuid) {
		$this->uuid = $uuid;
	}

	public function getUuid():string {
		return $this->uuid;
	}
}