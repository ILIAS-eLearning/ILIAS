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
    private const int RESULT_MESSAGE_MAX_LENGTH = 400;
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
        $problem_summaries = [];
        /** @var array<int, ilFormatMail> $format_mails_by_owner */
        $format_mails_by_owner = [];
        /** @var array<int, ilMail> $mails_by_owner */
        $mails_by_owner = [];

        foreach ($mails as $mail) {
            /** @var MailDeliveryData $mail */
            $owner_id = $mail->getUserId();
            $internal_mail_id = $mail->getInternalMailId();
            $mail_id_label = (string) ($internal_mail_id ?? 'unknown');

            if ($owner_id === null) {
                $problem = 'Scheduled mail ' . $mail_id_label . ': missing owner user_id.';
                ilLoggerFactory::getLogger('mail')->error($problem);
                if ($internal_mail_id !== null) {
                    $this->deleteOrphanScheduledMail($internal_mail_id);
                }
                $problem_summaries[] = $problem;

                continue;
            }

            $mailer = null;

            try {
                $mailer = $format_mails_by_owner[$owner_id] ??= new ilFormatMail($owner_id);
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

                if ($errors !== []) {
                    $problem = 'Scheduled mail ' . $mail_id_label . ': ' . implode(', ', $errors);
                    ilLoggerFactory::getLogger('mail')->error($problem);
                    $problem_summaries[] = $problem;

                    continue;
                }

                if ($internal_mail_id !== null) {
                    ($mails_by_owner[$owner_id] ??= new ilMail($owner_id))->deleteMails([$internal_mail_id]);
                }
                $sent_count++;
            } catch (Throwable $e) {
                $problem = 'Scheduled mail ' . $mail_id_label . ': ' . $e->getMessage();
                ilLoggerFactory::getLogger('mail')->error(
                    $problem . "\n" . $e->getTraceAsString()
                );
                $problem_summaries[] = $problem;
            } finally {
                if ($mailer !== null) {
                    $mailer->autoresponder()->disableAutoresponder();
                }
            }
        }

        ilLoggerFactory::getLogger('mail')->info(
            'Sent ' . $sent_count . ' scheduled mails and removed them from outbox.'
        );
        $job_result->setMessage($this->buildResultMessage($sent_count, $problem_summaries));

        return $job_result;
    }

    /**
     * @param list<string> $problem_summaries
     */
    private function buildResultMessage(int $sent_count, array $problem_summaries): string
    {
        if ($problem_summaries === []) {
            return 'Processed ' . $sent_count . ' mails.';
        }

        return mb_substr(
            'Processed ' . $sent_count . ' mails. Problems: ' . implode('; ', $problem_summaries),
            0,
            self::RESULT_MESSAGE_MAX_LENGTH
        );
    }

    private function deleteOrphanScheduledMail(int $mail_id): void
    {
        global $DIC;

        $DIC->database()->manipulateF(
            'DELETE FROM mail WHERE mail_id = %s',
            ['integer'],
            [$mail_id]
        );
    }
}
