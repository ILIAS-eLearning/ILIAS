<?php

namespace srag\IliasComponentCourse\Course\Member\Application;

use srag\IliasComponent\Context\Command\Command\Command;
use srag\IliasComponent\Context\Command\Command\CommandHandler;
use srag\IliasComponent\Context\Command\DomainRepository;

/**
 * Class RemoveCourseMemberFromCourseCommandHandler
 *
 * @package srag\IliasComponentCourse\Course\Command\Command;
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class RemoveCourseMemberFromCourseCommandHandler implements CommandHandler {

	/**
	 * @var DomainRepository
	 */
	protected $repository;


	/**
	 * @inheritdoc
	 */
	public function __construct(DomainRepository $repository) {
		$this->repository = $repository;
	}


	/**
	 * @inheritdoc
	 */
	public function __invoke(Command $command): void {
		$this->repository->removeParticipant($command->getObjId(), $command->getUserId());
	}
}
