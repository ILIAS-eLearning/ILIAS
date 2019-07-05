<?php

namespace ILIAS\Changelog\Membership\AR;


use ActiveRecord;

/**
 * Class MembershipRequested
 * @package ILIAS\Changelog\Membership\AR
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipEventAR extends ActiveRecord {

	const TABLE_NAME = 'changelog_membership';

	protected $event_id;

	protected $user_id;

	protected $obj_id;
}