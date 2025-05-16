<?php

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

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\DI\Container;

class ilMassMailTaskProcessor
{
    private readonly TaskManager $task_manager;
    private readonly TaskFactory $task_factory;
    private readonly ilLanguage $language;
    private readonly ilLogger $logger;
    private readonly ilMailValueObjectJsonService $object_json_service;

    public function __construct(
        private readonly int $anonymous_user_id = ANONYMOUS_USER_ID,
        ?TaskManager $task_manager = null,
        ?TaskFactory $task_factory = null,
        ?ilLanguage $language = null,
        ?ilLogger $logger = null,
        ?Container $dic = null,
        ?ilMailValueObjectJsonService $object_json_service = null
    ) {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }

        $this->task_manager = $task_manager ?? $dic->backgroundTasks()->taskManager();
        $this->task_factory = $task_factory ?? $dic->backgroundTasks()->taskFactory();
        $this->language = $language ?? $dic->language();
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->object_json_service = $object_json_service ?? new ilMailValueObjectJsonService();
    }

    /**
     * @param list<ilMailValueObject> $value_objects      - One MailValueObject = One Task
     * @param int                     $usr_id             - User ID of the user who executes the background task
     * @param string                  $context_id         - context ID of the Background task
     * @param array                   $context_parameters - context parameters for the background tasks
     * @param int                     $mails_per_task     - Defines how many mails will be added before a background task is executed
     * @throws ilMailException
     */
    public function run(
        array $value_objects,
        int $usr_id,
        string $context_id,
        array $context_parameters,
        int $mails_per_task = 100
    ): void {
        $num_value_objects = count($value_objects);

        if ($num_value_objects <= 0) {
            throw new ilMailException('First parameter must contain at least 1 array element');
        }

        if ($mails_per_task <= 0) {
            throw new ilMailException(
                sprintf(
                    'The mails per task MUST be a positive integer, "%s" given',
                    $mails_per_task
                )
            );
        }

        foreach ($value_objects as $value_object) {
            if (!($value_object instanceof ilMailValueObject)) {
                throw new ilMailException('Array MUST contain ilMailValueObjects ONLY');
            }
        }

        $task_counter = 0;
        $remaining_objects = [];
        foreach ($value_objects as $value_object) {
            $task_counter++;

            $remaining_objects[] = $value_object;
            if ($task_counter === $mails_per_task) {
                $interaction = $this->createInteraction($usr_id, $context_id, $context_parameters, $remaining_objects);

                $this->runTask($interaction, $usr_id);

                $task_counter = 0;
                $remaining_objects = [];
            }
        }

        if ([] !== $remaining_objects) {
            $interaction = $this->createInteraction($usr_id, $context_id, $context_parameters, $remaining_objects);

            $this->runTask($interaction, $usr_id);
        }
    }

    private function runTask(\ILIAS\BackgroundTasks\Task $task, int $usr_id): void
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($usr_id);

        $bucket->setTask($task);
        $bucket->setTitle($this->language->txt('mail_bg_task_title'));

        $this->logger->info('Delegated delivery to background task');
        $this->task_manager->run($bucket);
    }

    private function createInteraction(
        int $usr_id,
        string $context_id,
        array $context_parameters,
        $remaining_objects
    ): ILIAS\BackgroundTasks\Task {
        $json_string = $this->object_json_service->convertToJson($remaining_objects);

        $task = $this->task_factory->createTask(ilMassMailDeliveryJob::class, [
            $usr_id,
            $json_string,
            $context_id,
            serialize($context_parameters),
        ]);

        // Important: Don't return the task (e.g. as an early return for anonymous user id) https://mantis.ilias.de/view.php?id=33618

        $parameters = [$task, $usr_id];

        return $this->task_factory->createTask(
            ilMailDeliveryJobUserInteraction::class,
            $parameters
        );
    }
}
