<?php

namespace ILIAS\Changelog;


use ILIAS\Changelog\Exception\EventHandlerNotFoundException;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Logger\ilDBLogger;
use ILIAS\Changelog\Logger\Logger;
use ILIAS\Changelog\Query\QueryService;

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
	public function registerLogger(Logger $logger) {
		$this->loggers[] = $logger;
	}

	/**
	 * @param Event $event
	 * @throws EventHandlerNotFoundException
	 */
	public function logEvent(Event $event) {
		foreach ($this->loggers as $logger) {
			$logger->logEvent($event);
		}
	}

	/**
	 * @return QueryService
	 */
	public function query(): QueryService {

	}

}