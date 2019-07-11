<?php

namespace ILIAS\Changelog\Query;


use ILIAS\Changelog\Infrastructure\Repository\MembershipRepository;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfUserResponse;

/**
 * Class MembershipQueryService
 * @package ILIAS\Changelog\Query
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipQueryService {

	/**
	 * @var MembershipRepository
	 */
	protected $repository;


	/**
	 * MembershipQueryService constructor.
	 * @param MembershipRepository $repository
	 */
	public function __construct(MembershipRepository $repository) {
		$this->repository = $repository;
	}

	/**
	 * @param getLogsOfUserRequest $getLogsOfUsersRequest
	 * @return getLogsOfUserResponse
	 */
	public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUsersRequest): getLogsOfUserResponse {
		return $this->repository->getLogsOfUser($getLogsOfUsersRequest);
	}
}
