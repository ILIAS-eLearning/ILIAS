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
use Generator;
use ilContext;
use ilObjUser;
use Throwable;
use ilLanguage;
use DateTimeZone;
use ilFormatMail;
use ilDBConstants;
use ilDBInterface;
use ilLoggerFactory;
use DateTimeImmutable;
use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Mail\Folder\MailFolderType;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use MailDeliveryData;
use ILIAS\Mail\Folder\OutboxDatabaseRepository;
use ILIAS\Mail\Folder\OutboxRepository;
use ILIAS\Mail\Folder\MailScheduleData;

class ScheduledMailsCron extends CronJob
{
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private bool $init_done = false;
    private readonly ilMail $mail;
    private readonly ilFormatMail $umail;
    private OutboxRepository $outbox_repository;

    private function init(): void
    {
        global $DIC;

        if (!$this->init_done) {
            $this->lng = $DIC->language();
            $this->user = $DIC->user();
            $this->mail = new ilMail($this->user->getId());
            $this->umail = new ilFormatMail($this->user->getId());

            $this->lng->loadLanguageModule('mail');
            $this->init_done = true;
            $this->outbox_repository = new OutboxDatabaseRepository(
                $DIC->database(),
                (new DataFactory())->clock(),
                $this->mail
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
        $sent_mail_ids = [];
        foreach ($mails as $mail) {
            /** @var MailDeliveryData $mail */
            try {
                $mailer = $this->umail
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

                if (empty($errors)) {
                    $sent_mail_ids[] = $mail->getInternalMailId();
                }
            } catch (Throwable $e) {
                $job_result->setStatus(JobResult::STATUS_FAIL);
                ilLoggerFactory::getLogger('mail')->error(
                    'Error sending scheduled mail with id ' . ((string) ($mail->getInternalMailId() ?? 'unknown')) . ': ' .
                    $e->getMessage() . '\n' . $e->getTraceAsString()
                );
                $job_result->setMessage(substr($e->getMessage() . ' ' . $e->getTraceAsString(), 0, 4000));

                return $job_result;
            }
        }
        $this->mail->deleteMails($sent_mail_ids);
        ilLoggerFactory::getLogger('mail')->info(
            'Sent ' . count($sent_mail_ids) . ' scheduled mails and removed them from outbox.'
        );
        $job_result->setMessage('Processed ' . count($sent_mail_ids) . ' mails.');

        return $job_result;
    }
}
