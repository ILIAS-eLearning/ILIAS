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

readonly class ilForumNotificationTaskProcessor
{
    private TaskManager $task_manager;
    private TaskFactory $task_factory;
    private ilLanguage $lng;
    private ilLogger $logger;
    private ilObjUser $user;

    public function __construct(ilLogger $logger)
    {
        global $DIC;
        $this->task_manager = $DIC->backgroundTasks()->taskManager();
        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->logger = $logger ;
    }

    /**
     * @param int[] $recipients
     */
    public function run(
        ilForumNotificationMailData $provider,
        int $notification_type,
        array $recipients,
    ): void {
        if ($recipients === []) {
            return;
        }

        $push_job = $this->task_factory->createTask(ilForumPushNotificationJob::class, [
            json_encode([
                'post_id' => $provider->getPostId(),
                'ref_id' => $provider->getRefId(),
                'notification_type' => $notification_type,
                'recipients' => array_values($recipients),
            ], JSON_THROW_ON_ERROR),
        ]);
        $interaction = $this->task_factory->createTask(
            ilMailDeliveryJobUserInteraction::class,
            [$push_job, $this->user->getId()],
        );

        $bucket = new BasicBucket();
        $bucket->setUserId($this->user->getId());
        $bucket->setTask($interaction);
        $this->lng->loadLanguageModule('forum');
        $bucket->setTitle($this->lng->txt('frm_notification_bg_task_title'));

        $this->logger->info('Delegated forum notification delivery to background task.');
        $this->task_manager->run($bucket);
    }
}
