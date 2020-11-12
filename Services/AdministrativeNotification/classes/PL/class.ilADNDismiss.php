<?php

/**
 * Class ilADNDismiss
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNDismiss extends ActiveRecord
{

    const TABLE_NAME = 'il_adn_dismiss';

    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @var array
     */
    protected static $request_cache = array();

    /**
     * @param ilObjUser         $ilObjUser
     * @param ilADNNotification $ilADNNotification
     * @return bool
     */
    public static function hasDimissed(ilObjUser $ilObjUser, ilADNNotification $ilADNNotification) : bool
    {
        $not_id = $ilADNNotification->getId();
        $usr_id = $ilObjUser->getId();
        if (!isset(self::$request_cache[$usr_id][$not_id])) {
            self::$request_cache[$usr_id][$not_id] = self::where(array(
                'usr_id'          => $usr_id,
                'notification_id' => $not_id,
            ))->hasSets();
        }

        return (bool) self::$request_cache[$usr_id][$not_id];
    }

    /**
     * @param ilObjUser         $ilObjUser
     * @param ilADNNotification $ilADNNotification
     */
    public static function dismiss(ilObjUser $ilObjUser, ilADNNotification $ilADNNotification) : void
    {
        if (!self::hasDimissed($ilObjUser, $ilADNNotification) and $ilADNNotification->isUserAllowedToDismiss($ilObjUser)) {
            $obj = new self();
            $obj->setNotificationId($ilADNNotification->getId());
            $obj->setUsrId($ilObjUser->getId());
            $obj->create();
        }
    }

    /**
     * @param ilADNNotification $ilADNNotification
     */
    public static function reactivateAll(ilADNNotification $ilADNNotification) : void
    {
        /**
         * @var ilADNDismiss $dismiss
         */
        foreach (self::where(array('notification_id' => $ilADNNotification->getId())) as $dismiss) {
            $dismiss->delete();
        }
    }

    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $usr_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $notification_id = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * @param int $usr_id
     */
    public function setUsrId($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    /**
     * @return int
     */
    public function getNotificationId()
    {
        return $this->notification_id;
    }

    /**
     * @param int $notification_id
     */
    public function setNotificationId($notification_id)
    {
        $this->notification_id = $notification_id;
    }
}
