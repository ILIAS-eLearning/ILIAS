<?php

namespace ILIAS\App\CoreApp\Course\Domain\Command;

class AddMemberCommand {

	/**
	 * @var int
	 */
	private $obj_id;
	/**
	 * @var int
	 */
	private $user_id;


	public function __construct(int $obj_id, int $user_id) {
		$this->obj_id = $obj_id;
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->obj_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}
}
