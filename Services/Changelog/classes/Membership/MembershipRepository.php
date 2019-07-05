<?php

namespace ILIAS\Changelog\Membership;


use ILIAS\Changelog\Membership\Events\MembershipRequested;
use ILIAS\Changelog\Repository;

/**
 * Class MembershipRepository
 * @package ILIAS\Changelog\Membership\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipRepository implements Repository {

	/**
	 * @param MembershipRequested $membershipRequested
	 */
	abstract public function saveMembershipRequested(MembershipRequested $membershipRequested): void;

}