<?php

namespace ILIAS\Changelog\Events\GlobalEvents\Handlers;


use ILIAS\Changelog\Infrastructure\Repository\GlobalEventRepository;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventHandler;

/**
 * Class GlobalEventHandler
 * @package ILIAS\Changelog\Events\GlobalEvents\Handlers
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class GlobalEventHandler implements EventHandler {

	/**
	 * @var GlobalEventRepository
	 */
	protected $repository;

	/**
	 * GlobalEventHandler constructor.
	 * @param GlobalEventRepository $repository
	 */
	public function __construct(GlobalEventRepository $repository) {
		$this->repository = $repository;
	}

	/**
	 * @param Event $changelogEvent
	 */
	abstract public function handle(Event $changelogEvent);
}