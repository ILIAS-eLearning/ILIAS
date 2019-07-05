<?php

namespace ILIAS\Changelog\Membership\Repository;


use ilDBInterface;
use ILIAS\Changelog\Membership\Events\MembershipRequested;
use ILIAS\Changelog\Membership\MembershipRepository;

/**
 * Class ilDBMembershipRepository
 * @package ILIAS\Changelog\Membership\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBMembershipRepository extends MembershipRepository {

	/**
	 * @var ilDBInterface
	 */
	protected $database;

	/**
	 * ilDBMembershipRepository constructor.
	 */
	public function __construct() {
		global $DIC;
		$this->database = $DIC->database();
	}


	/**
	 * @param MembershipRequested $membershipRequested
	 */
	public function saveMembershipRequested(MembershipRequested $membershipRequested): void {

	}


}