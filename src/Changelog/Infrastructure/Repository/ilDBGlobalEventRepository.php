<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use Exception;
use ilDBInterface;
use ILIAS\Changelog\Events\GlobalEvents\ChangelogActivated;
use ILIAS\Changelog\Events\GlobalEvents\ChangelogDeactivated;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\EventID;

/**
 * Class ilDBGlobalEventRepository
 * @package ILIAS\Changelog\Infrastructure\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBGlobalEventRepository extends GlobalEventRepository {

	/**
	 * @var ilDBInterface
	 */
	protected $database;

	/**
	 * ilDBMembershipEventRepository constructor.
	 */
	public function __construct() {
		global $DIC;
		$this->database = $DIC->database();
	}

	/**
	 * @param int $type_id
	 * @param int $actor_user_id
	 * @throws Exception
	 */
	protected function saveGlobalEvent(int $type_id, int $actor_user_id) {
		$EventID = new EventID();

		$EventAR = new EventAR();
		$EventAR->setActorUserId($actor_user_id);
		$EventAR->setEventId($EventID);
		$EventAR->setTimestamp(time());
		$EventAR->setTypeId($type_id);
		$EventAR->create();
	}

	/**
	 * @param ChangelogActivated $changelogActivated
	 * @throws Exception
	 */
	public function saveChangelogActivated(ChangelogActivated $changelogActivated) {
		$this->saveGlobalEvent($changelogActivated->getTypeId(), $changelogActivated->getActivatingUserId());
	}

	/**
	 * @param ChangelogDeactivated $changelogDeactivated
	 * @throws Exception
	 */
	public function saveChangelogDeactivated(ChangelogDeactivated $changelogDeactivated) {
		$this->saveGlobalEvent($changelogDeactivated->getTypeId(), $changelogDeactivated->getDeactivatingUserId());
	}


}