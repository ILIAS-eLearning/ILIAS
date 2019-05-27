<?php

namespace srag\IliasComponent\Context\Command\Command;

use SimpleBus\Message\Handler\Resolver\MessageHandlerResolver;
use srag\IliasComponent\Context\Command\WriteOnlyDomainRepository;

/**
 * Interface Resolver
 *
 * @package srag\IliasComponent\Command
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Resolver extends MessageHandlerResolver {

	/**
	 * Resolver constructor
	 *
	 * @param WriteOnlyDomainRepository $repository
	 */
	public function __construct(WriteOnlyDomainRepository $repository);
}
