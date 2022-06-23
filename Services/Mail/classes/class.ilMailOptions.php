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
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Clock\ClockInterface;

/**
 * Class ilMailOptions
 * this class handles user mails
 * @author    Stefan Meyer <meyer@leifos.com>
 */
class ilMailOptions
{
    public const INCOMING_LOCAL = 0;
    public const INCOMING_EMAIL = 1;
    public const INCOMING_BOTH = 2;
    public const FIRST_EMAIL = 3;
    public const SECOND_EMAIL = 4;
    public const BOTH_EMAIL = 5;
    public const DEFAULT_LINE_BREAK = 60;
    protected ILIAS $ilias;
    protected ilDBInterface $db;
    protected int $usrId = 0;
    protected ilSetting $settings;
    protected string $table_mail_options = 'mail_options';
    protected int $linebreak = 0;
    protected string $signature = '';
    protected bool $isCronJobNotificationEnabled = false;
    protected int $incomingType = self::INCOMING_LOCAL;
    protected int $emailAddressMode = self::FIRST_EMAIL;
    private ilMailTransportSettings $mailTransportSettings;
    protected string $firstEmailAddress = '';
    protected string $secondEmailAddress = '';
    public const ABSENCE_STATUS_PRESENT = false;
    public const ABSENCE_STATUS_ABSENT = true;
    protected bool $absence_status = self::ABSENCE_STATUS_PRESENT;
    protected int $absent_from = 0;
    protected int $absent_until = 0;
    protected string $absence_auto_responder_body = '';
    protected string $absence_auto_responder_subject = '';
    protected ClockInterface $clockService;

    public function __construct(int $usrId, ilMailTransportSettings $mailTransportSettings = null, ClockInterface $clockService = null)
    {
        global $DIC;
        $this->usrId = $usrId;
        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->mailTransportSettings = $mailTransportSettings ?? new ilMailTransportSettings($this);
        $this->clockService = $clockService ?? (new DataFactory())->clock()->utc();

        $this->read();
    }

    /**
     * create entry in table_mail_options for a new user
     * this method should only be called from createUser()
     */
    public function createMailOptionsEntry(): void
    {
        $this->incomingType = self::INCOMING_LOCAL;
        if ($this->settings->get('mail_incoming_mail', '') !== '') {
            $this->incomingType = (int) $this->settings->get('mail_incoming_mail');
        }

        $this->emailAddressMode = self::FIRST_EMAIL;
        if ($this->settings->get('mail_address_option', '') !== '') {
            $this->emailAddressMode = (int) $this->settings->get('mail_address_option');
        }

        $this->linebreak = self::DEFAULT_LINE_BREAK;
        $this->isCronJobNotificationEnabled = false;
        $this->signature = '';

        $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usrId],
            ],
            [
                'linebreak' => ['integer', $this->linebreak],
                'signature' => ['text', $this->signature],
                'incoming_type' => ['integer', $this->incomingType],
                'mail_address_option' => ['integer', $this->emailAddressMode],
                'cronjob_notification' => ['integer', (int) $this->isCronJobNotificationEnabled],
            ]
        );
    }

    protected function read(): void
    {
        $query = 'SELECT mail_options.cronjob_notification,
					mail_options.signature,
					mail_options.linebreak,
					mail_options.incoming_type,
					mail_options.mail_address_option,
					mail_options.absence_status,
					mail_options.absent_from,
					mail_options.absent_until,
					mail_options.absence_ar_subject,
					mail_options.absence_ar_body,
					usr_data.email,
					usr_data.second_email
			 FROM mail_options 
			 LEFT JOIN usr_data ON mail_options.user_id = usr_data.usr_id
			 WHERE mail_options.user_id = %s';
        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->usrId]
        );
        $row = $this->db->fetchObject($res);
        if ($row !== null) {
            $this->isCronJobNotificationEnabled = (bool) $row->cronjob_notification;
            $this->signature = (string) $row->signature;
            $this->linebreak = (int) $row->linebreak;
            $this->incomingType = (int) $row->incoming_type;
            $this->emailAddressMode = (int) $row->mail_address_option;

            $this->setAbsenceStatus((bool) $row->absence_status);
            $this->setAbsentFrom((int) $row->absent_from);
            $this->setAbsentUntil((int) $row->absent_until);
            $this->setAbsenceAutoresponderSubject($row->absence_ar_subject ?? '');
            $this->setAbsenceAutoresponderBody($row->absence_ar_body ?? '');

            if (false === filter_var(
                $this->incomingType,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => self::INCOMING_LOCAL, 'max_range' => self::INCOMING_BOTH]]
            )) {
                $this->incomingType = self::INCOMING_LOCAL;
            }

            if (false === filter_var(
                $this->emailAddressMode,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => self::FIRST_EMAIL, 'max_range' => self::BOTH_EMAIL]]
            )) {
                $this->emailAddressMode = self::FIRST_EMAIL;
            }

            $this->firstEmailAddress = (string) $row->email;
            $this->secondEmailAddress = (string) $row->second_email;

            $this->mailTransportSettings->adjust($this->firstEmailAddress, $this->secondEmailAddress);
        }
    }

    public function updateOptions(): int
    {
        $data = [
            'signature' => ['text', $this->getSignature()],
            'linebreak' => ['integer', $this->getLinebreak()],
            'incoming_type' => ['integer', $this->getIncomingType()],
            'mail_address_option' => ['integer', $this->getEmailAddressMode()],
        ];

        if ($this->settings->get('mail_notification', '0')) {
            $data['cronjob_notification'] = ['integer', (int) $this->isCronJobNotificationEnabled()];
        } else {
            $data['cronjob_notification'] = ['integer', self::lookupNotificationSetting($this->usrId)];
        }

        $data['absence_status'] = ['integer', (int) $this->getAbsenceStatus()];
        $data['absent_from'] = ['integer', $this->getAbsentFrom()];
        $data['absent_until'] = ['integer', $this->getAbsentUntil()];
        $data['absence_ar_subject'] = ['text', $this->getAbsenceAutoresponderSubject()];
        $data['absence_ar_body'] = ['clob', $this->getAbsenceAutoresponderBody()];

        return $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usrId],
            ],
            $data
        );
    }

    public function getLinebreak(): int
    {
        return $this->linebreak;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getIncomingType(): int
    {
        return $this->incomingType;
    }

    public function setLinebreak(int $linebreak): void
    {
        $this->linebreak = $linebreak;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function setIncomingType(int $incomingType): void
    {
        $this->incomingType = $incomingType;
    }

    public function setIsCronJobNotificationStatus(bool $isCronJobNotificationEnabled): void
    {
        $this->isCronJobNotificationEnabled = $isCronJobNotificationEnabled;
    }

    public function isCronJobNotificationEnabled(): bool
    {
        return $this->isCronJobNotificationEnabled;
    }

    public function getEmailAddressMode(): int
    {
        return $this->emailAddressMode;
    }

    public function setEmailAddressMode(int $emailAddressMode): void
    {
        $this->emailAddressMode = $emailAddressMode;
    }

    public function getUsrId(): int
    {
        return $this->usrId;
    }

    private static function lookupNotificationSetting(int $usrId): int
    {
        global $DIC;

        $row = $DIC->database()->fetchAssoc($DIC->database()->queryF(
            'SELECT cronjob_notification FROM mail_options WHERE user_id = %s',
            ['integer'],
            [$usrId]
        ));

        return (int) $row['cronjob_notification'];
    }

    /**
     * @return string[]
     */
    public function getExternalEmailAddresses(): array
    {
        $emailAddresses = [];

        switch ($this->getEmailAddressMode()) {
            case self::SECOND_EMAIL:
                if ($this->secondEmailAddress !== '') {
                    $emailAddresses[] = $this->secondEmailAddress;
                } elseif ($this->firstEmailAddress !== '') {
                    // fallback, use first email address
                    $emailAddresses[] = $this->firstEmailAddress;
                }
                break;

            case self::BOTH_EMAIL:
                if ($this->firstEmailAddress !== '') {
                    $emailAddresses[] = $this->firstEmailAddress;
                }
                if ($this->secondEmailAddress !== '') {
                    $emailAddresses[] = $this->secondEmailAddress;
                }
                break;

            case self::FIRST_EMAIL:
            default:
                if ($this->firstEmailAddress !== '') {
                    $emailAddresses[] = $this->firstEmailAddress;
                } elseif ($this->secondEmailAddress !== '') {
                    // fallback, use first email address
                    $emailAddresses[] = $this->secondEmailAddress;
                }
                break;
        }

        return $emailAddresses;
    }

    public function setAbsenceAutoresponderBody(string $absence_auto_responder_body): void
    {
        $this->absence_auto_responder_body = $absence_auto_responder_body;
    }

    public function getAbsenceAutoresponderBody(): string
    {
        return $this->absence_auto_responder_body;
    }

    public function setAbsenceStatus(bool $absence_status): void
    {
        $this->absence_status = $absence_status;
    }

    public function getAbsenceStatus(): bool
    {
        return $this->absence_status;
    }

    public function setAbsentFrom(int $absent_from): void
    {
        $this->absent_from = $absent_from;
    }

    public function getAbsentFrom(): int
    {
        return $this->absent_from;
    }

    public function setAbsentUntil(int $absent_until): void
    {
        $this->absent_until = $absent_until;
    }

    public function getAbsentUntil(): int
    {
        return $this->absent_until;
    }

    public function setAbsenceAutoresponderSubject(string $absence_auto_responder_subject): void
    {
        $this->absence_auto_responder_subject = $absence_auto_responder_subject;
    }

    public function getAbsenceAutoresponderSubject(): string
    {
        return $this->absence_auto_responder_subject;
    }

    public function isAbsent(): bool
    {
        return
            $this->getAbsenceStatus() &&
            $this->getAbsentFrom() &&
            $this->getAbsentUntil() &&
            $this->getAbsentFrom() <= $this->clockService->now()->getTimestamp() &&
            $this->getAbsentUntil() >= $this->clockService->now()->getTimestamp();
    }
}
