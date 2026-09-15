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

namespace ILIAS\Mail\Cron;

use ilMail;
use ilContext;
use ilObjUser;
use Throwable;
use ilLanguage;
use ilFormatMail;
use ilLoggerFactory;
use MailDeliveryData;
use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Folder\OutboxRepository;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Mail\Folder\OutboxDatabaseRepository;

class ScheduledMailsCron extends CronJob
{
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private bool $init_done = false;
    private OutboxRepository $outbox_repository;

    private function init(): void
    {
        global $DIC;

        if (!$this->init_done) {
            $this->lng = $DIC->language();
            $this->user = $DIC->user();

            $this->lng->loadLanguageModule('mail');
            $this->init_done = true;
            $this->outbox_repository = new OutboxDatabaseRepository(
                $DIC->database(),
                (new DataFactory())->clock(),
                new ilMail($this->user->getId())
            );
        }
    }

    public function getId(): string
    {
        return 'mail_scheduled_mails';
    }

    public function getTitle(): string
    {
        $this->init();

        return $this->lng->txt('mail_cron_scheduled_mails');
    }

    public function getDescription(): string
    {
        $this->init();

        return $this->lng->txt('mail_cron_scheduled_mails_desc');
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): JobResult
    {
        $this->init();

        $job_result = new JobResult();
        $job_result->setStatus(JobResult::STATUS_OK);

        ilLoggerFactory::getLogger('mail')->info('Start sending scheduled mails from all users.');

        $mails = $this->outbox_repository->getOutboxMails();
        $sent_count = 0;
        foreach ($mails as $mail) {
            /** @var MailDeliveryData $mail */
            $owner_id = $mail->getUserId();
            if ($owner_id === null) {
                ilLoggerFactory::getLogger('mail')->error(
                    'Scheduled mail with id ' . (string) ($mail->getInternalMailId() ?? 'unknown') . ' has no owner user_id.'
                );
                $job_result->setStatus(JobResult::STATUS_FAIL);

                continue;
            }

            $mailer = null;

            try {
                $mailer = (new ilFormatMail($owner_id))
                    ->withContextId(ilContext::CONTEXT_CRON);

                $mailer->setSaveInSentbox(true);
                $mailer->autoresponder()->enableAutoresponder();

                $errors = $mailer->enqueue(
                    $mail->getTo(),
                    $mail->getCc(),
                    $mail->getBcc(),
                    $mail->getSubject(),
                    $mail->getMessage(),
                    $mail->getAttachments(),
                    $mail->isUsePlaceholder()
                );

                if (!empty($errors)) {
                    ilLoggerFactory::getLogger('mail')->error(
                        'Error sending scheduled mail with id ' . (string) ($mail->getInternalMailId() ?? 'unknown') . ': ' .
                        implode(', ', $errors)
                    );
                    $job_result->setStatus(JobResult::STATUS_FAIL);

                    continue;
                }

                $internal_mail_id = $mail->getInternalMailId();
                if ($internal_mail_id !== null) {
                    (new ilMail($owner_id))->deleteMails([$internal_mail_id]);
                }
                $sent_count++;
            } catch (Throwable $e) {
                $job_result->setStatus(JobResult::STATUS_FAIL);
                ilLoggerFactory::getLogger('mail')->error(
                    'Error sending scheduled mail with id ' . (string) ($mail->getInternalMailId() ?? 'unknown') . ': ' .
                    $e->getMessage() . "\n" . $e->getTraceAsString()
                );

                continue;
            } finally {
                if ($mailer !== null) {
                    $mailer->autoresponder()->disableAutoresponder();
                }
            }
        }
        ilLoggerFactory::getLogger('mail')->info(
            'Sent ' . $sent_count . ' scheduled mails and removed them from outbox.'
        );
        $job_result->setMessage('Processed ' . $sent_count . ' mails.');

        return $job_result;
    }
}
