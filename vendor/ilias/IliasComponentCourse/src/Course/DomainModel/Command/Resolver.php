<?php

namespace srag\IliasComponentCourse\Course\Command\Command;

use srag\IliasComponent\Context\Command\Command\AbstractResolver;
use srag\IliasComponentCourse\Course\Command\AddCourseMemberToCourseCommand;
use srag\IliasComponentCourse\Course\Command\AddCourseMemberToCourseCommandHandler;

/**
 * Class Resolver
 *
 * @package srag\IliasComponentCourse\Course\Command\Command;
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Resolver extends AbstractResolver {

	/**
	 * @inheritdoc
	 */
	protected function getCommandHandlerMap(): array {
		return [
			AddCourseMemberToCourseCommand::class => AddCourseMemberToCourseCommandHandler::class
		];
	}
}
