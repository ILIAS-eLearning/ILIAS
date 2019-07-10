<?php

namespace ILIAS\Changelog\Events\Membership\Handlers;



use ILIAS\Changelog\Infrastructure\Repository\MembershipRepository;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventHandler;

/**
 * Class MembershipEventHandler
 * @package ILIAS\Changelog\Membership
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipEventHandler implements EventHandler {

	/**
	 * @var MembershipRepository
	 */
	protected $repository;

	/**
	 * MembershipEventHandler constructor.
	 * @param MembershipRepository $repository
	 */
	public function __construct(MembershipRepository $repository) {
		$this->repository = $repository;
	}

	/**
	 * @param Event $changelogEvent
	 */
	abstract public function handle(Event $changelogEvent);

}