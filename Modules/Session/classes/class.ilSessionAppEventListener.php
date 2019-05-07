<?php
// thkoeln-patch: begin
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilSessionAppEventListener implements ilAppEventListener
{
	/**
	 * @var ilDBInterface
	 */
	private $database;

	/**
	 * @var ilObjectDataCache
	 */
	private $objectDataCache;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @var string
	 */
	private $component;

	/**
	 * @var string
	 */
	private $event;

	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * @param ilDBInterface $db
	 * @param ilObjectDataCache $objectDataCache
	 * @param ilLogger $logger
	 */
	public function __construct(
		\ilDBInterface $db,
		\ilObjectDataCache $objectDataCache,
		\ilLogger $logger
	) {
		$this->database = $db;
		$this->objectDataCache = $objectDataCache;
		$this->logger = $logger;
	}

	/**
	 * @param string $component
	 * @return \ilSessionAppEventListener
	 */
	public function withComponent($component)
	{
		$clone = clone $this;

		$clone->component = $component;

		return $clone;
	}

	/**
	 * @param string $event
	 * @return \ilSessionAppEventListener
	 */
	public function withEvent($event)
	{
		$clone = clone $this;

		$clone->event = $event;

		return $clone;
	}

	/**
	 * @param array $parameters
	 * @return \ilSessionAppEventListener
	 */
	public function withParameters(array $parameters)
	{
		$clone = clone $this;

		$clone->parameters = $parameters;

		return $clone;
	}

	/**
	 * Handle an event in a listener.
	 *
	 * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
	 * @param    string $a_event event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param    array $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 */
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		global $DIC;

		$listener = new static(
			$DIC->database(),
			$DIC['ilObjDataCache'],
			$DIC->logger()->cert()
		);

		$listener
			->withComponent($a_component)
			->withEvent($a_event)
			->withParameters($a_parameter)
			->handle();
	}

	public function handle()
	{
		if ('Modules/Session' !== $this->component) {
			return;
		}

		try {
			if ('register' === $this->event) {
				$this->handleRegisterEvent();
			} elseif ('enter' === $this->event) {
				$this->handleEnteredEvent();
			} elseif ('deleteParticipant' === $this->event) {
				$this->handleDeleteEvent();
			}
		} catch (\ilException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	private function handleRegisterEvent()
	{
		$recipients = $this->fetchRecipientParticipants();
		if (array() !== $recipients) {
			$notification = new ilSessionMailNotification();
			$notification->setRecipients($recipients);

			$notification->send(ilSessionMailNotification::REGISTERED_EVENT, $this->parameters['usr_id'], $this->parameters['obj_id']);
		}
	}

	private function handleEnteredEvent()
	{
		$recipients = $this->fetchRecipientParticipants();
		if (array() !== $recipients) {
			$notification = new ilSessionMailNotification();
			$notification->setRecipients($recipients);

			$notification->send(ilSessionMailNotification::ENTERED_EVENT, $this->parameters['usr_id'], $this->parameters['obj_id']);
		}
	}

	private function handleDeleteEvent()
	{
		$recipients = $this->fetchRecipientParticipants();
		if (array() !== $recipients) {
			$notification = new ilSessionMailNotification();
			$notification->setRecipients($recipients);

			$notification->send(ilSessionMailNotification::DELETION_EVENT, $this->parameters['usr_id'], $this->parameters['obj_id']);
		}
	}

	private function fetchRecipientParticipants()
	{
		$object = new ilEventParticipants($this->parameters['obj_id']);

		$recipients = array();
		$participants = $object->getParticipants();
		foreach ($participants as $id => $participant) {
			if ($participant['notification_enabled'] === true) {
				$recipients[] = $id;
			}
		}

		return $recipients;
	}
}
// thkoeln-patch: end
