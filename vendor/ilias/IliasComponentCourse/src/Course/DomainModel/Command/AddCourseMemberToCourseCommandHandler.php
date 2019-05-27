<?php

namespace srag\IliasComponentCourse\Course\Command;;

use srag\IliasComponent\Context\Command\Command\Command;
use srag\IliasComponent\Context\Command\CommandHandler;
use srag\IliasComponent\Context\Aggregate\Repository\DomainRepository;

/**
 * Class AddCourseMemberToCourseCommandHandler
 *
 * @package srag\IliasComponentCourse\Course\Command\Command;
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AddCourseMemberToCourseCommandHandler implements CommandHandler {

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
		$this->repository->addParticipant($command->getObjId(), $command->getUserId());
	}
}
