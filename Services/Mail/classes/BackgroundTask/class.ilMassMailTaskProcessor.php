<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMassMailTaskProcessor
{
    private TaskManager $taskManager;
    private TaskFactory $taskFactory;
    private ilLanguage $language;
    private ilLogger $logger;
    private ilMailValueObjectJsonService $objectJsonService;
    private int $anonymousUserId;

    public function __construct(
        TaskManager $taskManager = null,
        TaskFactory $taskFactory = null,
        ilLanguage $language = null,
        ilLogger $logger = null,
        Container $dic = null,
        ilMailValueObjectJsonService $objectJsonService = null,
        int $anonymousUserId = ANONYMOUS_USER_ID
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

        $this->anonymousUserId = $anonymousUserId;
    }

    /**
     * @param ilMailValueObject[] $mailValueObjects - One MailValueObject = One Task
     * @param int $userId - User ID of the user who executes the background task
     * @param string $contextId - context ID of the Background task
     * @param array $contextParameters - context parameters for the background tasks
     * @param int $mailsPerTask - Defines how many mails will be added before a background task is executed
     * @throws ilException
     */
    public function run(
        array $mailValueObjects,
        int $userId,
        string $contextId,
        array $contextParameters,
        int $mailsPerTask = 100
    ): void {
        $objectsServiceSize = count($mailValueObjects);

        if ($objectsServiceSize <= 0) {
            throw new ilException('First parameter must contain at least 1 array element');
        }

        if ($mailsPerTask <= 0) {
            throw new ilException(
                sprintf(
                    'The mails per task MUST be a positive integer, "%s" given',
                    $mailsPerTask
                )
            );
        }

        foreach ($mailValueObjects as $mailValueObject) {
            if (!($mailValueObject instanceof ilMailValueObject)) {
                throw new ilException('Array MUST contain ilMailValueObjects ONLY');
            }
        }

        $lastTask = null;
        $taskCounter = 0;

        $remainingObjects = [];
        foreach ($mailValueObjects as $mailValueObject) {
            $taskCounter++;

            $remainingObjects[] = $mailValueObject;
            if ($taskCounter === $mailsPerTask) {
                $interaction = $this->createInteraction($userId, $contextId, $contextParameters, $remainingObjects);

                $this->runTask($interaction, $userId);

                $taskCounter = 0;
                $remainingObjects = [];
            }
        }

        if ([] !== $remainingObjects) {
            $interaction = $this->createInteraction($userId, $contextId, $contextParameters, $remainingObjects);

            $this->runTask($interaction, $userId);
        }
    }

    private function runTask(\ILIAS\BackgroundTasks\Task $task, int $userId): void
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($userId);

        $bucket->setTask($task);
        $bucket->setTitle($this->language->txt('mail_bg_task_title'));

        $this->logger->info('Delegated delivery to background task');
        $this->taskManager->run($bucket);
    }

    private function createInteraction(
        int $userId,
        string $contextId,
        array $contextParameters,
        $remainingObjects
    ): ILIAS\BackgroundTasks\Task {
        $jsonString = $this->objectJsonService->convertToJson($remainingObjects);

        $task = $this->taskFactory->createTask(ilMassMailDeliveryJob::class, [
            $userId,
            $jsonString,
            $contextId,
            serialize($contextParameters),
        ]);

        // Important: Don't return the task (e.g. as an early return for anonymous user id) https://mantis.ilias.de/view.php?id=33618

        $parameters = [$task, $userId];

        return $this->taskFactory->createTask(
            ilMailDeliveryJobUserInteraction::class,
            $parameters
        );
    }
}
