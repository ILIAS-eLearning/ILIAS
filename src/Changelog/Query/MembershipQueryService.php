<?php

namespace ILIAS\Changelog\Query;


use ILIAS\Changelog\Infrastructure\Repository\MembershipRepository;
use ILIAS\Changelog\Query\Requests\getLogsOfCourseRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserAnonymizedRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfCourseResponse;
use ILIAS\Changelog\Query\Responses\getLogsOfUserAnonymizedResponse;
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
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 */
	public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse {
		return $this->repository->getLogsOfUser($getLogsOfUserRequest);
	}

	/**
	 * @param getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedRequest
	 * @return getLogsOfUserAnonymizedResponse
	 */
	public function getLogsOfUserAnonymized(getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedRequest): getLogsOfUserAnonymizedResponse {
		return $this->repository->getLogsOfUserAnonymized($getLogsOfUserAnonymizedRequest);
	}

	/**
	 * @param getLogsOfCourseRequest $getLogsOfCourseRequest
	 * @return getLogsOfCourseResponse
	 */
	public function getLogsOfCourse(getLogsOfCourseRequest $getLogsOfCourseRequest): getLogsOfCourseResponse {
		return $this->repository->getLogsOfCourse($getLogsOfCourseRequest);
	}
}
