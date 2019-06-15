<?php

namespace ILIAS\AssessmentQuestion\AuthoringAPIGateway;

class CreateQuestionResult {
	protected $successful;

	protected $new_id;

	protected $message;

	function __construct(bool $successful, string $message, string $new_id = "") {
		if ($successful) {
			$this->new_id = $new_id;
		}

		$this->successful = $successful;
		$this->message = $message;
	}


	/**
	 * @return string
	 */
	public function isSuccessful(): bool {
		return $this->successful;
	}


	/**
	 * @return string
	 */
	public function getNewId(): string {
		return $this->new_id;
	}


	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}
}