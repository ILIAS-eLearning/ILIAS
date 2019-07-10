<?php

namespace ILIAS\Changelog\Query;


use ILIAS\Changelog\Infrastructure\Repository\ilDBMembershipEventRepository;

/**
 * Class QueryService
 * @package ILIAS\Changelog\Query
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class QueryService {

	/**
	 * @return MembershipQueryService
	 */
	public function membership(): MembershipQueryService {
		return new MembershipQueryService(new ilDBMembershipEventRepository());
	}

}