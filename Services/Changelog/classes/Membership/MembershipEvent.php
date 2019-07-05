<?php

namespace ILIAS\Changelog\Membership;


use ILIAS\Changelog\Event;

/**
 * Class ChangelogMembershipEvent
 * @package ILIAS\Changelog\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipEvent implements Event {

	protected $obj_type;

	protected $obj_id;

	protected $obj_ref_id;

	protected $ilias_component;

	protected $initiating_user;

	protected $affected_user;

	protected $timestamp;

	abstract public function getTitle(): String;
}