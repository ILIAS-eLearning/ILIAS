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
 
/**
 * Class ilADNDismiss
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNDismiss extends ActiveRecord
{
    public const TABLE_NAME = 'il_adn_dismiss';

    public function getConnectorContainerName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * @deprecated
     */
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    protected static array $request_cache = array();

    public static function hasDimissed(ilObjUser $ilObjUser, ilADNNotification $ilADNNotification) : bool
    {
        $not_id = $ilADNNotification->getId();
        $usr_id = $ilObjUser->getId();
        if (!isset(self::$request_cache[$usr_id][$not_id])) {
            self::$request_cache[$usr_id][$not_id] = self::where(array(
                'usr_id' => $usr_id,
                'notification_id' => $not_id,
            ))->hasSets();
        }

        return (bool) self::$request_cache[$usr_id][$not_id];
    }

    public static function dismiss(ilObjUser $ilObjUser, ilADNNotification $ilADNNotification) : void
    {
        if (!self::hasDimissed($ilObjUser, $ilADNNotification) && $ilADNNotification->isUserAllowedToDismiss($ilObjUser)) {
            $obj = new self();
            $obj->setNotificationId($ilADNNotification->getId());
            $obj->setUsrId($ilObjUser->getId());
            $obj->create();
        }
    }

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
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected ?int $id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $usr_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $notification_id = 0;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function setUsrId(int $usr_id) : void
    {
        $this->usr_id = $usr_id;
    }

    public function getNotificationId() : int
    {
        return $this->notification_id;
    }

    public function setNotificationId(int $notification_id) : void
    {
        $this->notification_id = $notification_id;
    }
}
