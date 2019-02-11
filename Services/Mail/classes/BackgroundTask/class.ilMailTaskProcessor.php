<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\DI\Container;

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
	 * @param TaskManager $taskManager
	 * @param TaskFactory|null $taskFactory
	 * @param ilLanguage|null $language
	 * @param ilLogger|null $logger
	 * @param Container|null $dic
	 */
	public function __construct(
		TaskManager $taskManager = null,
		TaskFactory $taskFactory = null,
		ilLanguage  $language = null,
		ilLogger $logger = null,
		Container $dic = null
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
	 * @param ilMailValueObject[] $mailValueObjects - One MailValueObject = One Task
	 * @param int $userId - User ID of the user who executes the background task
	 * @param string $contextId - context ID of the Background task
	 * @param array $contextParameters - context parameters for the background tasks
	 * @param int $tasksBeforeExecution - Defines how many tasks will be added consecutive before running
	 * @throws ilException
	 */
	public function run(
		array $mailValueObjects,
		int $userId,
		string $contextId,
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

			$recipients = $mailValueObject->getRecipients();
			$recipientsCC = $mailValueObject->getRecipientsCC();
			$recipientsBCC = $mailValueObject->getRecipientsBCC();
			$subject = $mailValueObject->getSubject();
			$body = $mailValueObject->getBody();
			$value = $mailValueObject->getAttachment();
			$isUsingPlaceholders = $mailValueObject->isUsingPlaceholders();
			$shouldSaveInSentBox = $mailValueObject->shouldSaveInSentBox();

			$task = $this->taskFactory->createTask(\ilMailDeliveryJob::class, [
				(int)$userId,
				(string)$recipients,
				(string)$recipientsCC,
				(string)$recipientsBCC,
				(string)$subject,
				(string)$body,
				serialize($value),
				(bool)$isUsingPlaceholders,
				(bool)$shouldSaveInSentBox,
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
