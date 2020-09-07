<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Mime/Sender/interface.ilMailMimeSender.php';

/**
 * Class ilMailMimeSenderSystem
 */
class ilMailMimeSenderUser implements ilMailMimeSender
{
    /**
     * @var \ilObjUser[]
     */
    protected static $userInstances = array();

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * ilMailMimeSenderSystem constructor.
     * @param \ilSetting $settings
     * @param \ilObjUser ilObjUser
     */
    public function __construct(\ilSetting $settings, \ilObjUser $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * @param \ilSetting $settings
     * @param int $usrId
     * @return self
     */
    public static function byUsrId(\ilSetting $settings, $usrId)
    {
        if (!array_key_exists($usrId, self::$userInstances)) {
            self::$userInstances[$usrId] = new \ilObjUser($usrId);
        }

        return new self($settings, self::$userInstances[$usrId]);
    }

    /**
     * @param int $usrId
     * @param \ilObjUser $user
     */
    public static function addUserToCache($usrId, \ilObjUser $user)
    {
        self::$userInstances[$usrId] = $user;
    }

    /**
     * @param \ilSetting $settings
     * @param string $emailAddress
     * @return self
     */
    public static function byEmailAddress(\ilSetting $settings, $emailAddress)
    {
        $user = new \ilObjUser();
        $user->setEmail($emailAddress);

        return new self($settings, $user);
    }

    /**
     * @inheritdoc
     */
    public function hasReplyToAddress()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToAddress()
    {
        return $this->user->getEmail();
    }

    /**
     * @inheritdoc
     */
    public function getReplyToName()
    {
        return $this->user->getFullname();
    }

    /**
     * @inheritdoc
     */
    public function hasEnvelopFromAddress()
    {
        return strlen($this->settings->get('mail_system_usr_env_from_addr')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getEnvelopFromAddress()
    {
        return $this->settings->get('mail_system_usr_env_from_addr');
    }

    /**
     * @inheritdoc
     */
    public function getFromAddress()
    {
        return $this->settings->get('mail_system_usr_from_addr');
    }

    /**
     * @inheritdoc
     */
    public function getFromName()
    {
        $from = $this->settings->get('mail_system_usr_from_name');
        if (0 == strlen($from)) {
            return $this->user->getFullname();
        }

        $name = str_ireplace('[FULLNAME]', $this->user->getFullname(), $from);
        $name = str_ireplace('[FIRSTNAME]', $this->user->getFirstname(), $name);
        $name = str_ireplace('[LASTNAME]', $this->user->getLastname(), $name);
        if ($name != $from) {
            return $name;
        }

        return $from;
    }
}
