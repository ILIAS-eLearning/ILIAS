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

use ilLogger;
use ilObjUser;
use Throwable;
use ilLanguage;
use ilMailError;
use ilFormatMail;
use ilLoggerFactory;
use MailDeliveryData;
use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Folder\OutboxRepository;
use ILIAS\Mail\Message\MailRecordMapper;
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
                new MailRecordMapper()
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

        $this->getLogger()->info('Start sending scheduled mails from all users.');

        $mails = $this->outbox_repository->getOutboxMails();
        $sent_count = 0;
        /** @var list<string> $problem_summaries */
        $problem_summaries = [];
        /** @var array<int, ilFormatMail> $format_mails_by_owner */
        $format_mails_by_owner = [];

        foreach ($mails as $mail) {
            /** @var MailDeliveryData $mail */
            $owner_id = $mail->getUserId();
            $internal_mail_id = $mail->getInternalMailId() ?? 0;

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
                    $this->getLogger()->error(
                        'Scheduled mail {mail_id} could not be sent: {errors}',
                        [
                            'mail_id' => $internal_mail_id,
                            'errors' => $this->formatMailErrorsForLog($errors),
                        ]
                    );
                    $problem_summaries[] = $this->buildShortProblemSummary($internal_mail_id, 'not sent');

                    continue;
                }

                if ($internal_mail_id > 0) {
                    $this->outbox_repository->markAsDelivered($owner_id, $internal_mail_id);
                    $mailer->deleteMails([$internal_mail_id]);
                }
                $sent_count++;
            } catch (Throwable $e) {
                $this->getLogger()->error(
                    'Scheduled mail {mail_id} could not be sent: {message}',
                    [
                        'mail_id' => $internal_mail_id,
                        'message' => $e->getMessage(),
                    ]
                );
                $this->getLogger()->error(
                    'Scheduled mail {mail_id} stack trace: {trace}',
                    [
                        'mail_id' => $internal_mail_id,
                        'trace' => $e->getTraceAsString(),
                    ]
                );
                $problem_summaries[] = $this->buildShortProblemSummary($internal_mail_id, 'error');
            } finally {
                if ($mailer !== null) {
                    $mailer->autoresponder()->disableAutoresponder();
                }
            }
        }

        $this->getLogger()->info(
            'Sent {sent_count} scheduled mails, marked them as delivered and removed them from outbox.',
            ['sent_count' => $sent_count]
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

    private function buildShortProblemSummary(int $mail_id, string $reason): string
    {
        return 'Mail ' . ($mail_id > 0 ? (string) $mail_id : 'unknown') . ': ' . $reason;
    }

    /**
     * @param list<ilMailError> $errors
     */
    private function formatMailErrorsForLog(array $errors): string
    {
        $formatted_errors = [];
        foreach ($errors as $error) {
            $formatted_error = $error->getLanguageVariable();
            if ($error->getPlaceHolderValues() !== []) {
                $formatted_error .= ' (' . implode(', ', $error->getPlaceHolderValues()) . ')';
            }
            $formatted_errors[] = $formatted_error;
        }

        return implode('; ', $formatted_errors);
    }

    private function getLogger(): ilLogger
    {
        return ilLoggerFactory::getLogger('mail');
    }
}
