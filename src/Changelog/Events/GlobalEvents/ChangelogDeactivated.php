<?php

namespace ILIAS\Changelog\Events\GlobalEvents;


/**
 * Class ChangelogDeactivated
 * @package ILIAS\Changelog\Events\GlobalEvents
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogDeactivated extends GlobalEvent {

	const TYPE_ID = 11;

	/**
	 * @var int
	 */
	protected $deactivating_user_id;


	/**
	 * ChangelogDeactivated constructor.
	 * @param int $deactivating_user_id
	 */
	public function __construct(int $deactivating_user_id) {
		$this->deactivating_user_id = $deactivating_user_id;
	}

	/**
	 * @return int
	 */
	public function getDeactivatingUserId(): int {
		return $this->deactivating_user_id;
	}

	/**
	 * @return int
	 */
	public function getTypeId(): int {
		return self::TYPE_ID;
	}
}