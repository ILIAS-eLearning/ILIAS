<?php

namespace ILIAS\Changelog;


use ILIAS\Changelog\Logger\ilDBLogger;
use ILIAS\Changelog\Logger\Logger;
use ILIAS\Changelog\Membership\Exception\EventHandlerNotFoundException;

/**
 * Class ChangelogService
 * @package ILIAS\Changelog
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogService {

	/**
	 * @var Logger[]
	 */
	protected $loggers;

	/**
	 * ChangelogService constructor.
	 */
	public function __construct() {
		$this->registerLogger(new ilDBLogger());
	}

	/**
	 * Use to add additional loggers to store the event. Default is the ilDBLogger.
	 *
	 * @param Logger $logger
	 */
	public function registerLogger(Logger $logger): void {
		$this->loggers[] = $logger;
	}

	/**
	 * @param Event $event
	 * @throws EventHandlerNotFoundException
	 */
	public function logEvent(Event $event): void {
		foreach ($this->loggers as $logger) {
			$logger->logEvent($event);
		}
	}


}