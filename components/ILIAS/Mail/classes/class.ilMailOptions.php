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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Clock\ClockInterface;

class ilMailOptions
{
    final public const int INCOMING_LOCAL = 0;
    final public const int INCOMING_EMAIL = 1;
    final public const int INCOMING_BOTH = 2;
    final public const int FIRST_EMAIL = 3;
    final public const int SECOND_EMAIL = 4;
    final public const int BOTH_EMAIL = 5;
    final public const bool ABSENCE_STATUS_PRESENT = false;
    final public const bool ABSENCE_STATUS_ABSENT = true;

    protected ILIAS $ilias;
    protected ilDBInterface $db;
    protected ilSetting $settings;
    protected string $table_mail_options = 'mail_options';
    protected string $signature = '';
    protected bool $is_cron_notification_enabled = false;
    protected int $incoming_type = self::INCOMING_LOCAL;
    protected int $default_incoming_type = self::INCOMING_LOCAL;
    protected int $email_address_mode = self::FIRST_EMAIL;
    protected int $default_email_address_mode = self::FIRST_EMAIL;
    protected ilMailTransportSettings $mail_transport_settings;
    protected string $first_mail_address = '';
    protected string $second_mail_address = '';
    protected bool $absence_status = self::ABSENCE_STATUS_PRESENT;
    protected int $absent_from = 0;
    protected int $absent_until = 0;
    protected string $absence_auto_responder_body = '';
    protected string $absence_auto_responder_subject = '';
    protected ClockInterface $clock_service;

    public function __construct(
        protected int $usr_id,
        ?ilMailTransportSettings $mail_transport_settings = null,
        ?ClockInterface $clock_service = null,
        ?ilSetting $settings = null,
        ?ilDBInterface $db = null
    ) {
        global $DIC;
        $this->db = $db ?? $DIC->database();
        $this->settings = $settings ?? $DIC->settings();
        $this->mail_transport_settings = $mail_transport_settings ?? new ilMailTransportSettings($this);
        $this->clock_service = $clock_service ?? (new DataFactory())->clock()->utc();

        $this->incoming_type = self::INCOMING_LOCAL;
        $default_incoming_type = $this->settings->get('mail_incoming_mail', '');
        if ($default_incoming_type !== '') {
            $this->default_incoming_type = (int) $default_incoming_type;
            $this->incoming_type = $this->default_incoming_type;
        }

        $this->email_address_mode = self::FIRST_EMAIL;
        $default_email_address_mode = $this->settings->get('mail_address_option', '');
        if ($default_email_address_mode !== '') {
            $this->default_email_address_mode = (int) $default_email_address_mode;
            $this->email_address_mode = $this->default_email_address_mode;
        }

        $this->is_cron_notification_enabled = false;
        $this->signature = '';

        $this->read();
    }

    /**
     * create entry in table_mail_options for a new user
     * this method should only be called from createUser()
     */
    public function createMailOptionsEntry(): void
    {
        $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usr_id],
            ],
            [
                'signature' => ['text', $this->signature],
                'incoming_type' => ['integer', $this->default_incoming_type],
                'mail_address_option' => ['integer', $this->default_email_address_mode],
                'cronjob_notification' => ['integer', (int) $this->is_cron_notification_enabled]
            ]
        );
    }

    public function mayModifyIndividualTransportSettings(): bool
    {
        return (
            $this->mayManageInvididualSettings() &&
            $this->maySeeIndividualTransportSettings() &&
            $this->settings->get('usr_settings_disable_mail_incoming_mail') !== '1'
        );
    }

    public function maySeeIndividualTransportSettings(): bool
    {
        return $this->settings->get('usr_settings_hide_mail_incoming_mail') !== '1';
    }

    public function mayManageInvididualSettings(): bool
    {
        return $this->settings->get('show_mail_settings') === '1';
    }

    protected function read(): void
    {
        $query = 'SELECT mail_options.cronjob_notification,
					mail_options.signature,
					
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
			 INNER JOIN usr_data ON mail_options.user_id = usr_data.usr_id
			 WHERE mail_options.user_id = %s';
        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->usr_id]
        );
        $row = $this->db->fetchObject($res);
        if ($row === null) {
            $this->mail_transport_settings->adjust($this->first_mail_address, $this->second_mail_address, false);
            return;
        }

        $this->first_mail_address = (string) $row->email;
        $this->second_mail_address = (string) $row->second_email;
        if ($this->mayManageInvididualSettings()) {
            $this->is_cron_notification_enabled = (bool) $row->cronjob_notification;
            $this->signature = (string) $row->signature;
            $this->setAbsenceStatus((bool) $row->absence_status);
            $this->setAbsentFrom((int) $row->absent_from);
            $this->setAbsentUntil((int) $row->absent_until);
            $this->setAbsenceAutoresponderSubject($row->absence_ar_subject ?? '');
            $this->setAbsenceAutoresponderBody($row->absence_ar_body ?? '');
        }

        if ($this->mayModifyIndividualTransportSettings()) {
            $this->incoming_type = (int) $row->incoming_type;
            $this->email_address_mode = (int) $row->mail_address_option;

            if (filter_var(
                $this->incoming_type,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => self::INCOMING_LOCAL, 'max_range' => self::INCOMING_BOTH]]
            ) === false) {
                $this->incoming_type = self::INCOMING_LOCAL;
            }

            if (filter_var(
                $this->email_address_mode,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => self::FIRST_EMAIL, 'max_range' => self::BOTH_EMAIL]]
            ) === false) {
                $this->email_address_mode = self::FIRST_EMAIL;
            }
        }

        $this->mail_transport_settings->adjust($this->first_mail_address, $this->second_mail_address);
    }

    public function updateOptions(): int
    {
        $data = [
            'signature' => ['text', $this->getSignature()],
            'incoming_type' => ['integer', $this->getIncomingType()],
            'mail_address_option' => ['integer', $this->getEmailAddressMode()],
        ];

        if ($this->settings->get('mail_notification', '0')) {
            $data['cronjob_notification'] = ['integer', (int) $this->isCronJobNotificationEnabled()];
        } else {
            $data['cronjob_notification'] = ['integer', self::lookupNotificationSetting($this->usr_id)];
        }

        $data['absence_status'] = ['integer', (int) $this->getAbsenceStatus()];
        $data['absent_from'] = ['integer', $this->getAbsentFrom()];
        $data['absent_until'] = ['integer', $this->getAbsentUntil()];
        $data['absence_ar_subject'] = ['text', $this->getAbsenceAutoresponderSubject()];
        $data['absence_ar_body'] = ['clob', $this->getAbsenceAutoresponderBody()];

        return $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usr_id],
            ],
            $data
        );
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getIncomingType(): int
    {
        return $this->incoming_type;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function setIncomingType(int $incoming_type): void
    {
        $this->incoming_type = $incoming_type;
    }

    public function setIsCronJobNotificationStatus(bool $is_cron_notification_enabled): void
    {
        $this->is_cron_notification_enabled = $is_cron_notification_enabled;
    }

    public function isCronJobNotificationEnabled(): bool
    {
        return $this->is_cron_notification_enabled;
    }

    public function getEmailAddressMode(): int
    {
        return $this->email_address_mode;
    }

    public function setEmailAddressmode(int $email_address_mode): void
    {
        $this->email_address_mode = $email_address_mode;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    private static function lookupNotificationSetting(int $usr_id): int
    {
        global $DIC;

        $row = $DIC->database()->fetchAssoc($DIC->database()->queryF(
            'SELECT cronjob_notification FROM mail_options WHERE user_id = %s',
            ['integer'],
            [$usr_id]
        ));

        return (int) $row['cronjob_notification'];
    }

    /**
     * @return string[]
     */
    public function getExternalEmailAddresses(): array
    {
        $email_addresses = [];
        switch ($this->getEmailAddressMode()) {
            case self::SECOND_EMAIL:
                if ($this->second_mail_address !== '') {
                    $email_addresses[] = $this->second_mail_address;
                } elseif ($this->first_mail_address !== '') {
                    // fallback, use first email address
                    $email_addresses[] = $this->first_mail_address;
                }
                break;

            case self::BOTH_EMAIL:
                if ($this->first_mail_address !== '') {
                    $email_addresses[] = $this->first_mail_address;
                }
                if ($this->second_mail_address !== '') {
                    $email_addresses[] = $this->second_mail_address;
                }
                break;

            case self::FIRST_EMAIL:
            default:
                if ($this->first_mail_address !== '') {
                    $email_addresses[] = $this->first_mail_address;
                } elseif ($this->second_mail_address !== '') {
                    // fallback, use first email address
                    $email_addresses[] = $this->second_mail_address;
                }
                break;
        }

        return $email_addresses;
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
            $this->getAbsentFrom() <= $this->clock_service->now()->getTimestamp() &&
            $this->getAbsentUntil() >= $this->clock_service->now()->getTimestamp();
    }
}
