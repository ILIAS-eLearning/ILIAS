<?php

namespace ILIAS\Changelog\Events\GlobalEvents;


/**
 * Class ChangelogActivated
 * @package ILIAS\Changelog\Events\GlobalEvents
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogActivated extends GlobalEvent {

	const TYPE_ID = 10;

	/**
	 * @var int
	 */
	protected $activating_user_id;


	/**
	 * ChangelogActivated constructor.
	 * @param int $activating_user_id
	 */
	public function __construct(int $activating_user_id) {
		$this->activating_user_id = $activating_user_id;
	}

	/**
	 * @return int
	 */
	public function getActivatingUserId(): int {
		return $this->activating_user_id;
	}

	/**
	 * @return int
	 */
	public function getTypeId(): int {
		return self::TYPE_ID;
	}


}