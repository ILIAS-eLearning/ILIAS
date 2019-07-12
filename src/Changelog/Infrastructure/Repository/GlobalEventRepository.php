<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use ILIAS\Changelog\Events\GlobalEvents\ChangelogActivated;
use ILIAS\Changelog\Events\GlobalEvents\ChangelogDeactivated;
use ILIAS\Changelog\Interfaces\Repository;

/**
 * Class GlobalEventRepository
 * @package ILIAS\Changelog\Infrastructure\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class GlobalEventRepository implements Repository {

	/**
	 * @param ChangelogActivated $changelogActivated
	 * @return mixed
	 */
	abstract public function saveChangelogActivated(ChangelogActivated $changelogActivated);

	/**
	 * @param ChangelogDeactivated $changelogDeactivated
	 * @return mixed
	 */
	abstract public function saveChangelogDeactivated(ChangelogDeactivated $changelogDeactivated);
}