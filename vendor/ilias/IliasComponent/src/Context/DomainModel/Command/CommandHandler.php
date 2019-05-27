<?php

namespace srag\IliasComponent\Context\Command\Command;

use srag\IliasComponent\Context\DomainRepository;

/**
 * Interface CommandHandler
 *
 * @package srag\IliasComponent\Command
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface CommandHandler {

	/**
	 * CommandHandler constructor
	 *
	 * @param DomainRepository $repository
	 */
	public function __construct(DomainRepository $repository);


	/**
	 * @param Command $command
	 */
	public function __invoke(Command $command): void;
}
