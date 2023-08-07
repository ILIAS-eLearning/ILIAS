<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    protected $default_incoming_type = self::INCOMING_LOCAL;
    /** @var int */
    protected $emailAddressMode = self::FIRST_EMAIL;
    /** @var int */
    protected $default_email_address_mode = self::FIRST_EMAIL;
    /** @var ilMailTransportSettings */
    protected $mailTransportSettings;
    /** @var string */
    protected $firstEmailAddress = '';
    /** @var string */
    protected $secondEmailAddress = '';

    /**
     * @param int $usrId
     * @param ilMailTransportSettings|null $mailTransportSettings
     */
    public function __construct(
        int $usrId,
        ilMailTransportSettings $mailTransportSettings = null,
        ilSetting $settings = null,
        ilDBInterface $db = null
    ) {
        global $DIC;

        $this->usrId = $usrId;

        $this->db = $db ?? $DIC->database();
        $this->settings = $settings ?? $DIC->settings();

        $this->incomingType = self::INCOMING_LOCAL;
        $default_incoming_type = $this->settings->get('mail_incoming_mail', '');
        if ($default_incoming_type !== '') {
            $this->default_incoming_type = (int) $default_incoming_type;
            $this->incomingType = $this->default_incoming_type;
        }

        $this->emailAddressMode = self::FIRST_EMAIL;
        $default_email_address_mode = $this->settings->get('mail_address_option', '');
        if ($default_email_address_mode !== '') {
            $this->default_email_address_mode = (int) $default_email_address_mode;
            $this->emailAddressMode = $this->default_email_address_mode;
        }

        $this->linebreak = self::DEFAULT_LINE_BREAK;
        $this->isCronJobNotificationEnabled = false;
        $this->signature = '';

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
        $this->db->replace(
            $this->table_mail_options,
            [
                'user_id' => ['integer', $this->usrId],
            ],
            [
                'linebreak' => ['integer', $this->linebreak],
                'signature' => ['text', $this->signature],
                'incoming_type' => ['integer', $this->default_incoming_type],
                'mail_address_option' => ['integer', $this->default_email_address_mode],
                'cronjob_notification' => ['integer', (int) $this->isCronJobNotificationEnabled]
            ]
        );
    }
    
    public function mayModifyIndividualTransportSettings() : bool
    {
        return (
            $this->mayManageInvididualSettings() &&
            $this->maySeeIndividualTransportSettings() &&
            $this->settings->get('usr_settings_disable_mail_incoming_mail') !== '1'
        );
    }

    public function maySeeIndividualTransportSettings() : bool
    {
        return $this->settings->get('usr_settings_hide_mail_incoming_mail') !== '1';
    }

    public function mayManageInvididualSettings() : bool
    {
        return $this->settings->get('show_mail_settings') === '1';
    }

    protected function read() : void
    {
        $query = implode(' ', [
            'SELECT mail_options.cronjob_notification,',
            'mail_options.signature, mail_options.linebreak, mail_options.incoming_type,',
            'mail_options.mail_address_option, usr_data.email, usr_data.second_email',
            'FROM mail_options',
            'INNER JOIN usr_data ON mail_options.user_id = usr_data.usr_id',
            'WHERE mail_options.user_id = %s',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->usrId]
        );
        $row = $this->db->fetchObject($res);
        if ($row === null) {
            $this->mailTransportSettings->adjust($this->firstEmailAddress, $this->secondEmailAddress, false);
            return;
        }

        $this->firstEmailAddress = (string) $row->email;
        $this->secondEmailAddress = (string) $row->second_email;

        if ($this->mayManageInvididualSettings()) {
            $this->signature = (string) $row->signature;
            $this->linebreak = (int) $row->linebreak;
            $this->isCronJobNotificationEnabled = (bool) $row->cronjob_notification;
        }

        if ($this->mayModifyIndividualTransportSettings()) {
            $this->incomingType = (int) $row->incoming_type;
            $this->emailAddressMode = (int) $row->mail_address_option;

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
        }

        $this->mailTransportSettings->adjust($this->firstEmailAddress, $this->secondEmailAddress);
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
            $data['cronjob_notification'] = ['integer', $this->lookupNotificationSetting($this->usrId)];
        }

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

    /**
     * @param int $usrId
     * @return int
     */
    protected function lookupNotificationSetting(int $usrId) : int
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
}
