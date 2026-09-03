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

namespace ILIAS\Registration\DualOptIn\Cron;

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;
use ILIAS\Registration\DualOptIn\Service\DualOptInService;
use ilLanguage;

/**
 * Cronjob to delete user accounts for which the registration (dual opt-in) has never been finalized.
 */
final class DeleteExpiredPendingRegistrationsCronJob extends CronJob
{
    private ilLanguage $lng;
    private DualOptInService $dual_opt_in_service;

    public function init(): void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->dual_opt_in_service = new \ILIAS\Registration\DualOptIn\Service\DualOptInServiceImpl(
            new \ilRegistrationSettings(),
            new \ILIAS\Registration\DualOptIn\Repository\PendingRegistrationDatabaseRepository($DIC->database()),
            $DIC->database(),
            $DIC->logger()->user(),
            (new \ILIAS\Data\Factory())->clock()
        );
    }

    public function getId(): string
    {
        return 'reg_delete_expired_pending_registrations';
    }

    public function getTitle(): string
    {
        $this->init();

        return $this->lng->txt('reg_delete_expired_pending_registrations');
    }

    public function getDescription(): string
    {
        $this->init();

        return $this->lng->txt('reg_delete_expired_pending_registrations_desc');
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function run(): JobResult
    {
        $this->init();

        $num_deleted_users = $this->dual_opt_in_service->deleteExpiredUserObjects();

        $result = new JobResult();
        $result->setStatus(JobResult::STATUS_OK);
        $result->setMessage(sprintf('%d inactive user objects with expired confirmation hash values (dual opt-in) deleted.', $num_deleted_users));

        return $result;
    }
}
