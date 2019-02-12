<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailTaskProcessor
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
	 * @var ilMailValueObjectJsonService|null
	 */
	private $objectJsonService;

	/**
	 * @param TaskManager $taskManager
	 * @param TaskFactory|null $taskFactory
	 * @param ilLanguage|null $language
	 * @param ilLogger|null $logger
	 * @param Container|null $dic
	 * @param ilMailValueObjectJsonService|null $objectJsonService
	 */
	public function __construct(
		TaskManager $taskManager = null,
		TaskFactory $taskFactory = null,
		ilLanguage  $language = null,
		ilLogger $logger = null,
		Container $dic = null,
		ilMailValueObjectJsonService $objectJsonService = null
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

		if (null === $objectJsonService) {
			$objectJsonService = new ilMailValueObjectJsonService();
		}
		$this->objectJsonService = $objectJsonService;
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
		$objectsServiceSize = sizeof($mailValueObjects);

		if ($objectsServiceSize <= 0) {
			throw new ilException('First parameter must contain at least 1 array element');
		}

		$lastTask = null;
		$taskCounter = 0;

		$remainingObjects = array();
		foreach ($mailValueObjects as $mailValueObject) {
			if (false === ($mailValueObject instanceof ilMailValueObject)) {
				throw new ilException('Array MUST contain ilMailValueObjects ONLY');
			}

			$taskCounter++;

			$remainingObjects[] = $mailValueObject;
			if ($taskCounter === $tasksBeforeExecution) {
				$jsonString = $this->objectJsonService->convertToJson($remainingObjects);

				$task = $this->taskFactory->createTask(\ilMassMailDeliveryJob::class, [
					(int) $userId,
					(string) $jsonString,
					(string) $contextId,
					serialize($contextParameters),
				]);
				$parameters = [$task, (int) $userId];

				$interaction = $this->taskFactory->createTask(
					\ilMailDeliveryJobUserInteraction::class,
					$parameters
				);

				$this->runTask($interaction, $userId);

				$taskCounter = 0;
				$remainingObjects = array();
			}
		}

		if (array() !== $remainingObjects) {
			$jsonString = $this->objectJsonService->convertToJson($remainingObjects);

			$task = $this->taskFactory->createTask(\ilMassMailDeliveryJob::class, [
				(int) $userId,
				(string) $jsonString,
				(string) $contextId,
				serialize($contextParameters),
			]);

			$parameters = [$task, (int) $userId];

			$interaction = $this->taskFactory->createTask(
				\ilMailDeliveryJobUserInteraction::class,
				$parameters
			);

			$this->runTask($interaction, $userId);
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
