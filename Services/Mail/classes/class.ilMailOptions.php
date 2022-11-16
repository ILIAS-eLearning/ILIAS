<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory as DataFactory;

/**
 * Class ilMailOptions
 * this class handles user mails
 * @author    Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMailOptions
{
    const INCOMING_LOCAL = 0;
    const INCOMING_EMAIL = 1;
    const INCOMING_BOTH = 2;

    const FIRST_EMAIL = 3;
    const SECOND_EMAIL = 4;
    const BOTH_EMAIL = 5;

    const DEFAULT_LINE_BREAK = 60;

    /** @var ILIAS */
    protected $ilias;
    /** @var ilDBInterface */
    protected $db;
    /** @var int */
    protected $usrId = 0;
    /** @var ilSetting */
    protected $settings;
    /** @var string */
    protected $table_mail_options = 'mail_options';
    /** @var int */
    protected $linebreak = 0;
    /** @var string */
    protected $signature = '';
    /** @var bool */
    protected $isCronJobNotificationEnabled = false;
    /** @var int */
    protected $incomingType = self::INCOMING_LOCAL;
    /** @var int */
    protected $emailAddressMode = self::FIRST_EMAIL;
    /** @var ilMailTransportSettings */
    private $mailTransportSettings;
    /** @var string */
    protected $firstEmailAddress = '';
    /** @var string */
    protected $secondEmailAddress = '';

    public const ABSENCE_STATUS_PRESENT = false;
    public const ABSENCE_STATUS_ABSENT = true;
    /** @var bool $absence_status */
    protected $absence_status = self::ABSENCE_STATUS_PRESENT;
    /** @var int $absent_from */
    protected $absent_from = 0;
    /** @var int $absent_until */
    protected $absent_until = 0;
    /** @var string $absence_auto_responder_body */
    protected $absence_auto_responder_body = '';
    /** @var string $absence_auto_responder_subject */
    protected $absence_auto_responder_subject = '';

    /**
     * @param int $usrId
     * @param ilMailTransportSettings|null $mailTransportSettings
     */
    public function __construct(int $usrId, ilMailTransportSettings $mailTransportSettings = null)
    {
        global $DIC;

        $this->usrId = $usrId;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();

        if ($mailTransportSettings === null) {
            $mailTransportSettings = new ilMailTransportSettings($this);
        }
        $this->mailTransportSettings = $mailTransportSettings;

        $this->read();
    }

    /**
     * create entry in table_mail_options for a new user
     * this method should only be called from createUser()
     */
    public function createMailOptionsEntry() : void
    {
        $this->incomingType = self::INCOMING_LOCAL;
        if (strlen($this->settings->get('mail_incoming_mail', '')) > 0) {
            $this->incomingType = (int) $this->settings->get('mail_incoming_mail');
        }

        $this->emailAddressMode = self::FIRST_EMAIL;
        if (strlen($this->settings->get('mail_address_option', '')) > 0) {
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
                'cronjob_notification' => ['integer', (int) $this->isCronJobNotificationEnabled]
            ]
        );
    }

    protected function read() : void
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

    public function updateOptions()
    {
        $data = [
            'signature' => ['text', $this->getSignature()],
            'linebreak' => ['integer', (int) $this->getLinebreak()],
            'incoming_type' => ['integer', $this->getIncomingType()],
            'mail_address_option' => ['integer', $this->getEmailAddressMode()]
        ];

        if ($this->settings->get('mail_notification')) {
            $data['cronjob_notification'] = ['integer', (int) $this->isCronJobNotificationEnabled()];
        } else {
            $data['cronjob_notification'] = ['integer', (int) self::lookupNotificationSetting($this->usrId)];
        }

        $data['absence_status'] = ['integer', (int) $this->getAbsenceStatus()];
        $data['absent_from'] = ['integer', $this->getAbsentFrom()];
        $data['absent_until'] = ['integer', $this->getAbsentUntil()];
        $data['absence_ar_subject'] = ['text', $this->getAbsenceAutoresponderSubject()];
        $data['absence_ar_body'] = ['clob', $this->getAbsenceAutoresponderBody()];

        return $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usrId]
            ],
            $data
        );
    }

    /**
     * @return int
     */
    public function getLinebreak() : int
    {
        return $this->linebreak;
    }

    /**
     * @return string
     */
    public function getSignature() : string
    {
        return $this->signature;
    }

    /**
     * @return int
     */
    public function getIncomingType() : int
    {
        return $this->incomingType;
    }

    /**
     * @param int $linebreak
     */
    public function setLinebreak(int $linebreak) : void
    {
        $this->linebreak = $linebreak;
    }

    /**
     * @param string $signature
     */
    public function setSignature(string $signature) : void
    {
        $this->signature = $signature;
    }

    /**
     * @param int $incomingType
     */
    public function setIncomingType(int $incomingType) : void
    {
        $this->incomingType = $incomingType;
    }

    /**
     * @param bool $isCronJobNotificationEnabled
     */
    public function setIsCronJobNotificationStatus(bool $isCronJobNotificationEnabled) : void
    {
        $this->isCronJobNotificationEnabled = $isCronJobNotificationEnabled;
    }

    /**
     * @return bool
     */
    public function isCronJobNotificationEnabled() : bool
    {
        return $this->isCronJobNotificationEnabled;
    }

    /**
     * @return int
     */
    public function getEmailAddressMode() : int
    {
        return $this->emailAddressMode;
    }

    /**
     * @param int $emailAddressMode
     */
    public function setEmailAddressMode(int $emailAddressMode) : void
    {
        $this->emailAddressMode = $emailAddressMode;
    }

    public function getUsrId() : int
    {
        return $this->usrId;
    }

    /**
     * @param int $usrId
     * @return int
     */
    private static function lookupNotificationSetting(int $usrId) : int
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
    public function getExternalEmailAddresses() : array
    {
        $emailAddresses = [];

        switch ($this->getEmailAddressMode()) {
            case self::SECOND_EMAIL:
                if (strlen($this->secondEmailAddress)) {
                    $emailAddresses[] = $this->secondEmailAddress;
                } elseif (strlen($this->firstEmailAddress)) {
                    // fallback, use first email address
                    $emailAddresses[] = $this->firstEmailAddress;
                }
                break;

            case self::BOTH_EMAIL:
                if (strlen($this->firstEmailAddress)) {
                    $emailAddresses[] = $this->firstEmailAddress;
                }
                if (strlen($this->secondEmailAddress)) {
                    $emailAddresses[] = $this->secondEmailAddress;
                }
                break;

            case self::FIRST_EMAIL:
            default:
                if (strlen($this->firstEmailAddress)) {
                    $emailAddresses[] = $this->firstEmailAddress;
                } elseif (strlen($this->secondEmailAddress)) {
                    // fallback, use first email address
                    $emailAddresses[] = $this->secondEmailAddress;
                }
                break;
        }

        return $emailAddresses;
    }

    public function setAbsenceAutoresponderBody(string $absence_auto_responder_body) : void
    {
        $this->absence_auto_responder_body = $absence_auto_responder_body;
    }

    public function getAbsenceAutoresponderBody() : string
    {
        return $this->absence_auto_responder_body;
    }

    public function setAbsenceStatus(bool $absence_status) : void
    {
        $this->absence_status = $absence_status;
    }

    public function getAbsenceStatus() : bool
    {
        return $this->absence_status;
    }

    public function setAbsentFrom(int $absent_from) : void
    {
        $this->absent_from = $absent_from;
    }

    public function getAbsentFrom() : int
    {
        return $this->absent_from;
    }

    public function setAbsentUntil(int $absent_until) : void
    {
        $this->absent_until = $absent_until;
    }

    public function getAbsentUntil() : int
    {
        return $this->absent_until;
    }

    public function setAbsenceAutoresponderSubject(string $absence_auto_responder_subject) : void
    {
        $this->absence_auto_responder_subject = $absence_auto_responder_subject;
    }

    public function getAbsenceAutoresponderSubject() : string
    {
        return $this->absence_auto_responder_subject;
    }

    public function isAbsent() : bool
    {
        return
            $this->getAbsenceStatus() &&
            $this->getAbsentFrom() &&
            $this->getAbsentUntil() &&
            $this->getAbsentFrom() <= (new DateTimeImmutable('NOW', new DateTimeZone('UTC')))->getTimestamp() &&
            $this->getAbsentUntil() >= (new DateTimeImmutable('NOW', new DateTimeZone('UTC')))->getTimestamp();
    }
}
