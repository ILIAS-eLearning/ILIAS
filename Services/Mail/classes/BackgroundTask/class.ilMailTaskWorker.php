<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailTaskWorker
{
	/**
	 * @var TaskManager
	 */
	private $taskManager;

	/**
	 * @var TaskFactory
	 */
	private $taskFactory;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var ilLogger|null
	 */
	private $logger;

	/**
	 * @param TaskManager|null $taskManager
	 * @param TaskFactory|null $taskFactory
	 * @param ilLanguage|null $language
	 * @param ilLogger|null $logger
	 * @param \ILIAS\DI\Container|null $dic
	 */
	public function __construct(
		\ILIAS\BackgroundTasks\TaskManager $taskManager = null,
		TaskFactory $taskFactory = null,
		ilLanguage  $language = null,
		ilLogger $logger = null,
		\ILIAS\DI\Container $dic = null
	) {

		if (null === $dic) {
			global $DIC;
			$dic = $DIC;
		}

		if (null === $taskManager) {
			$taskManager = $dic->backgroundTasks()->taskManager();
		}
		$this->taskManager = $taskManager;

		if (null === $taskFactory) {
			$taskFactory = $dic->backgroundTasks()->taskFactory();
		}
		$this->taskFactory = $taskFactory;

		if (null === $language) {
			$language = $dic->language();
		}
		$this->language = $language;

		if (null === $logger) {
			$logger = ilLoggerFactory::getLogger('mail');
		}
		$this->logger = $logger;
	}

	/**
	 * @param ilMailValueObject[] $mailValueObjects
	 * @param int $userId
	 * @param int $contextId
	 * @param array $contextParameters
	 * @param int $tasksBeforeExecution
	 * @throws ilException
	 */
	public function run(
		array $mailValueObjects,
		int $userId,
		int $contextId,
		array $contextParameters,
		int $tasksBeforeExecution = 100
	) {
		$taskSize = sizeof($mailValueObjects);

		if ($taskSize <= 0) {
			throw new ilException('First parameter must contain at least 1 array element');
		}

		$lastTask = null;
		$taskCounter = 0;

		foreach ($mailValueObjects as $mailValueObject) {
			if (false === ($mailValueObject instanceof ilMailValueObject)) {
				throw new ilException('Array MUST contain ilMailValueObjects ONLY');
			}

			$task = $this->taskFactory->createTask(\ilMailDeliveryJob::class, [
				(int)$userId,
				(string)$mailValueObject->getRecipients(),
				(string)$mailValueObject->getRecipientsCC(),
				(string)$mailValueObject->getRecipientsBCC(),
				(string)$mailValueObject->getSubject(),
				(string)$mailValueObject->getBody(),
				serialize($mailValueObject->getAttachment()),
				(bool)$mailValueObject->isUsingPlaceholders(),
				(bool)$mailValueObject->shouldSaveInbox(),
				(string)$contextId,
				serialize($contextParameters),
				serialize($mailValueObject->getTypes()),
			]);

			$parameters = [$task, (int)$userId];
			if (null !== $lastTask) {
				$parameters = [$lastTask, $task, (int)$userId];
			}

			$interaction = $this->taskFactory->createTask(
				\ilMailDeliveryJobUserInteraction::class,
				$parameters
			);

			$lastTask = $interaction;

			$taskCounter++;

			if ($taskCounter === $tasksBeforeExecution) {
				$this->runTask($lastTask, $userId);

				$taskCounter = 0;
				$lastTask = null;
			}
		}

		if (null !== $lastTask) {
			$this->runTask($lastTask, $userId);
		}
	}

	private function runTask(\ILIAS\BackgroundTasks\Task $task, int $userId)
	{
		$bucket = new BasicBucket();
		$bucket->setUserId($userId);

		$bucket->setTask($task);
		$bucket->setTitle($this->language->txt('mail_bg_task_title'));

		$this->logger->info('Delegated delivery to background task');
		$this->taskManager->run($bucket);
	}
}
