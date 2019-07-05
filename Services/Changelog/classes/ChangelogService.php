<?php

namespace ILIAS\Changelog;


use ILIAS\Changelog\Logger\Logger;
use ILIAS\Changelog\Membership\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Membership\Repository\ilDBMembershipRepository;

/**
 * Class ChangelogService
 * @package ILIAS\Changelog
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogService {

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * ChangelogService constructor.
	 * @param Logger|null $logger
	 */
	public function __construct(Logger $logger = null) {
		$this->logger = $logger ?: new ilDBLogger();
	}


	/**
	 * @param Event $event
	 * @throws EventHandlerNotFoundException
	 */
	public function logEvent(Event $event): void {
		$this->logger->logEvent($event);
	}


}