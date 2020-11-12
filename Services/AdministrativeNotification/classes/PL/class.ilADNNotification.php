<?php

/**
 * Class ilADNNotification
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotification extends ActiveRecord
{

    const POS_TOP = 1;
    const POS_RIGHT = 2;
    const POST_LEFT = 3;
    const POS_BOTTOM = 4;
    const DATE_FORMAT = 'd.m.Y';
    const TIME_FORMAT = 'H:i';
    const DATE_TIME_FORMAT = 'd.m.Y H:i';
    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;
    const TABLE_NAME = 'il_adn_notifications';
    const LINK_TYPE_NONE = 0;
    const LINK_TYPE_REF_ID = 1;
    const LINK_TYPE_URL = 2;
    /**
     * @var array
     */
    protected static $allowed_user_ids = array(0, 13, 6);

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
     * @param ilObjUser $ilObjUser
     */
    public function dismiss(ilObjUser $ilObjUser)
    {
        if ($this->isUserAllowedToDismiss($ilObjUser)) {
            ilADNDismiss::dismiss($ilObjUser, $this);
        }
    }

    /**
     * @param ilObjUser $ilObjUser
     * @return bool
     */
    protected function hasUserDismissed(ilObjUser $ilObjUser)
    {
        if (!$this->getDismissable()) {
            return false;
        }

        return ilADNDismiss::hasDimissed($ilObjUser, $this);
    }

    public function resetForAllUsers()
    {
        foreach (ilADNDismiss::where(array('notification_id' => $this->getId()))->get() as $not) {
            $not->delete();
        }
    }

    /**
     * @return string
     */
    public function getFullTimeFormated() : string
    {
        if ($this->getEventStart() == 0 && $this->getEventEnd() == 0) {
            return '';
        }
        if (date(self::DATE_FORMAT, $this->getEventStart()) == date(self::DATE_FORMAT, $this->getEventEnd())) {
            return date(self::DATE_FORMAT, $this->getEventEnd()) . ', ' . date(self::TIME_FORMAT, $this->getEventStart()) . " - "
                . date(self::TIME_FORMAT, $this->getEventEnd());
        } else {
            return date(self::DATE_TIME_FORMAT, $this->getEventStart()) . ' - ' . date(self::DATE_TIME_FORMAT, $this->getEventEnd());
        }
    }

    /**
     * @param ilObjUser $ilUser
     * @return bool
     */
    public function isUserAllowedToDismiss(ilObjUser $ilUser)
    {
        return ($this->getDismissable() and $ilUser->getId() != 0 and $ilUser->getId() != ANONYMOUS_USER_ID);
    }

    /**
     * @return int
     */
    public function getActiveType()
    {
        if ($this->getPermanent()) {
            return $this->getType();
        }
        if ($this->hasEventStarted() and !$this->hasEventEnded()) {
            return $this->getTypeDuringEvent();
        }
        if ($this->hasDisplayStarted() and !$this->hasDisplayEnded()) {
            return $this->getType();
        }
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        if ($this->getPermanent()) {
            return true;
        }
        $hasEventStarted   = $this->hasEventStarted();
        $hasDisplayStarted = $this->hasDisplayStarted();
        $hasEventEnded     = !$this->hasEventEnded();
        $hasDisplayEnded   = !$this->hasDisplayEnded();

        return ($hasEventStarted or $hasDisplayStarted) and ($hasEventEnded or $hasDisplayEnded);
    }

    /**
     * @param ilObjUser $ilObjUser
     * @return bool
     */
    public function isVisibleForUser(ilObjUser $ilObjUser)
    {
        if ($ilObjUser->getId() == 0 && $this->isInterruptive()) {
            return false;
        }
        if (!$this->isVisible()) {

            return false;
        }
        if ($this->hasUserDismissed($ilObjUser)) {
            return false;
        }
        if (!$this->isVisibleRoleUserRoles($ilObjUser)) {
            return false;
        }

        return true;
    }

    /**
     * @param ilObjUser $ilObjUser
     * @return bool
     */
    protected function isVisibleRoleUserRoles(ilObjUser $ilObjUser)
    {
        if (!$this->isLimitToRoles()) {
            return true;
        }
        global $DIC;

        if ($ilObjUser->getId() === 0 && in_array(0, $this->getLimitedToRoleIds())) {
            return true;
        }

        return $DIC->rbac()->review()->isAssignedToAtLeastOneGivenRole($ilObjUser->getId(), $this->getLimitedToRoleIds());
    }

    /**
     * @param ilObjUser $ilObjUser
     * @return bool
     */
    public function isUserAllowed(ilObjUser $ilObjUser)
    {
        global $DIC;
        if (in_array($ilObjUser->getId(), self::$allowed_user_ids)) {
            return true;
        }
        if ($DIC->rbac()->review()->isAssigned($ilObjUser->getId(), 2)) {
            return true;
        }
        if ($this->getPreventLogin()) {
            if ($this->isDuringEvent() or $this->getPermanent()) {
                if (!in_array($ilObjUser->getId(), $this->getAllowedUsers())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @var int
     * @con_is_primary true
     * @con_sequence   true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $title = '';
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $body = '';
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $event_start;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $event_end;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $display_start;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $display_end;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $type = self::TYPE_INFO;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $type_during_event = self::TYPE_ERROR;
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $dismissable = false;
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $permanent = true;
    /**
     * @var array
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $allowed_users = array(0, 6, 13);
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $parent_id = null;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $create_date;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected $last_update;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $created_by = null;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $last_update_by = null;
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $active = true;
    /**
     * @var array
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected $limited_to_role_ids = array();
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $limit_to_roles = false;
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $interruptive = false;
    /**
     * @var string
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected $link = '';
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $link_type = self::LINK_TYPE_NONE;
    /**
     * @var string
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected $link_target = '_top';

    /**
     * @param string $field_name
     * @param string $field_value
     * @return int|mixed
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'event_start':
            case 'event_end':
            case 'display_end':
            case 'display_start':
            case 'create_date':
            case 'last_update':
                return strtotime($field_value);
                break;
            case 'allowed_users':
                if ($field_value === null) {
                    $array_unique = self::$allowed_user_ids;
                } else {
                    $json_decode = json_decode($field_value, true);
                    if (!is_array($json_decode)) {
                        $json_decode = self::$allowed_user_ids;
                    }
                    $array_unique = array_unique($json_decode);
                }

                sort($array_unique);

                return $array_unique;
                break;
            case 'limited_to_role_ids':
                return json_decode($field_value, true);
                break;
        }
    }

    /**
     * @param string $field_name
     * @return bool|mixed|string
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'event_start':
            case 'event_end':
            case 'display_end':
            case 'display_start':
            case 'create_date':
            case 'last_update':
                return date(DATE_ISO8601, $this->{$field_name});
                break;
            case 'allowed_users':
                $allowed_users = self::$allowed_user_ids;
                foreach ($this->allowed_users as $user_id) {
                    $allowed_users[] = (int) $user_id;
                }

                return json_encode(array_unique($allowed_users));
                break;
            case 'limited_to_role_ids':
                return json_encode($this->{$field_name});
                break;
        }
    }

    public function create()
    {
        global $DIC;
        $this->setCreateDate(time());
        $this->setCreatedBy($DIC->user()->getId());
        parent::create();
    }

    public function update()
    {
        global $DIC;
        $this->setLastUpdate(time());
        $this->setLastUpdateBy($DIC->user()->getId());
        parent::update();
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $display_end
     */
    public function setDisplayEnd($display_end)
    {
        $this->display_end = $display_end;
    }

    /**
     * @return int
     */
    public function getDisplayEnd()
    {
        return $this->display_end;
    }

    /**
     * @param int $display_start
     */
    public function setDisplayStart($display_start)
    {
        $this->display_start = $display_start;
    }

    /**
     * @return int
     */
    public function getDisplayStart()
    {
        return $this->display_start;
    }

    /**
     * @param int $event_end
     */
    public function setEventEnd($event_end)
    {
        $this->event_end = $event_end;
    }

    /**
     * @return int
     */
    public function getEventEnd()
    {
        return $this->event_end;
    }

    /**
     * @param int $event_start
     */
    public function setEventStart($event_start)
    {
        $this->event_start = $event_start;
    }

    /**
     * @return int
     */
    public function getEventStart()
    {
        return $this->event_start;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type_during_event
     */
    public function setTypeDuringEvent($type_during_event)
    {
        $this->type_during_event = $type_during_event;
    }

    /**
     * @return int
     */
    public function getTypeDuringEvent()
    {
        return $this->type_during_event;
    }

    /**
     * @param boolean $dismissable
     */
    public function setDismissable($dismissable)
    {
        $this->dismissable = $dismissable;
    }

    /**
     * @return boolean
     */
    public function getDismissable()
    {
        return $this->dismissable;
    }

    /**
     * @return bool
     */
    protected function hasEventStarted()
    {
        return $this->getTime() > $this->getEventStart();
    }

    /**
     * @return bool
     */
    protected function hasDisplayStarted()
    {
        return $this->getTime() > $this->getDisplayStart();
    }

    /**
     * @return bool
     */
    protected function hasEventEnded()
    {
        return $this->getTime() > $this->getEventEnd();
    }

    /**
     * @return bool
     */
    protected function hasDisplayEnded()
    {
        return $this->getTime() > $this->getDisplayEnd();
    }

    /**
     * @param boolean $permanent
     */
    public function setPermanent($permanent)
    {
        $this->permanent = $permanent;
    }

    /**
     * @return boolean
     */
    public function getPermanent()
    {
        return $this->permanent;
    }

    /**
     * @param boolean $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return boolean
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $additional_classes
     */
    public function setAdditionalClasses($additional_classes)
    {
        $this->additional_classes = $additional_classes;
    }

    /**
     * @return string
     */
    public function getAdditionalClasses()
    {
        return $this->additional_classes;
    }

    /**
     * @param boolean $prevent_login
     */
    public function setPreventLogin($prevent_login)
    {
        $this->prevent_login = $prevent_login;
    }

    /**
     * @return boolean
     */
    public function getPreventLogin()
    {
        return $this->prevent_login;
    }

    /**
     * @param array $allowed_users
     */
    public function setAllowedUsers($allowed_users)
    {
        $this->allowed_users = $allowed_users;
    }

    /**
     * @return array
     */
    public function getAllowedUsers()
    {
        return $this->allowed_users;
    }

    /**
     * @return bool
     */
    protected function isDuringEvent()
    {
        return $this->hasEventStarted() and !$this->hasEventEnded();
    }

    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param int $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * @param int $last_update_by
     */
    public function setLastUpdateBy($last_update_by)
    {
        $this->last_update_by = $last_update_by;
    }

    /**
     * @return int
     */
    public function getLastUpdateBy()
    {
        return $this->last_update_by;
    }

    /**
     * @return int
     */
    protected function getTime()
    {
        return time();
        //		return strtotime('2014-11-25 06:15:00');
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getLimitedToRoleIds()
    {
        return $this->limited_to_role_ids;
    }

    /**
     * @param array $limited_to_role_ids
     */
    public function setLimitedToRoleIds($limited_to_role_ids)
    {
        $this->limited_to_role_ids = $limited_to_role_ids;
    }

    /**
     * @return boolean
     */
    public function isLimitToRoles()
    {
        return $this->limit_to_roles;
    }

    /**
     * @param boolean $limit_to_roles
     */
    public function setLimitToRoles($limit_to_roles)
    {
        $this->limit_to_roles = $limit_to_roles;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return int
     */
    public function getLinkType()
    {
        return $this->link_type;
    }

    /**
     * @param int $link_type
     */
    public function setLinkType($link_type)
    {
        $this->link_type = $link_type;
    }

    /**
     * @return string
     */
    public function getLinkTarget()
    {
        return $this->link_target;
    }

    /**
     * @param string $link_target
     */
    public function setLinkTarget($link_target)
    {
        $this->link_target = $link_target;
    }

    /**
     * @return array
     */
    public static function getAllowedUserIds()
    {
        return self::$allowed_user_ids;
    }

    /**
     * @param array $allowed_user_ids
     */
    public static function setAllowedUserIds($allowed_user_ids)
    {
        self::$allowed_user_ids = $allowed_user_ids;
    }

    /**
     * @return bool
     */
    public function isInterruptive()
    {
        return $this->interruptive;
    }

    /**
     * @param bool $interruptive
     */
    public function setInterruptive($interruptive)
    {
        $this->interruptive = $interruptive;
    }
}
