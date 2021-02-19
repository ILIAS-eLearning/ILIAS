<?php /** @noinspection ALL */

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
            return date(self::DATE_FORMAT, $this->getEventEnd()) . ', ' . date(self::TIME_FORMAT,
                    $this->getEventStart()) . " - "
                . date(self::TIME_FORMAT, $this->getEventEnd());
        } else {
            return date(self::DATE_TIME_FORMAT, $this->getEventStart()) . ' - ' . date(self::DATE_TIME_FORMAT,
                    $this->getEventEnd());
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
        if ($this->isPermanent()) {
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
        if ($this->isPermanent()) {
            return true;
        }
        $hasEventStarted = $this->hasEventStarted();
        $hasDisplayStarted = $this->hasDisplayStarted();
        $hasEventEnded = !$this->hasEventEnded();
        $hasDisplayEnded = !$this->hasDisplayEnded();

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

        return $DIC->rbac()->review()->isAssignedToAtLeastOneGivenRole($ilObjUser->getId(),
            $this->getLimitedToRoleIds());
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
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $event_start;
    /**
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $event_end;
    /**
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $display_start;
    /**
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
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
    protected $dismissable = true;
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
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $create_date;
    /**
     * @var DateTimeImmutable
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
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
    protected $limited_to_role_ids = [];
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
                return (new DateTimeImmutable())->setTimestamp((int) $field_value);

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
                /**
                 * @var $datetime DateTimeImmutable
                 */
                $datetime = $this->{$field_name} ?? new DateTimeImmutable();
                return $datetime->getTimestamp();
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
        $this->setCreateDate(new DateTimeImmutable());
        $this->setCreatedBy($DIC->user()->getId());
        parent::create();
    }

    public function setBody(string $body) : void
    {
        $this->body = $body;
    }

    public function getBody() : string
    {
        return (string) $this->body;
    }

    public function setDisplayEnd(DateTimeImmutable $display_end) : void
    {
        $this->display_end = $display_end;
    }

    public function getDisplayEnd() : DateTimeImmutable
    {
        return $this->display_end ?? new DateTimeImmutable();
    }

    public function setDisplayStart(DateTimeImmutable $display_start) : void
    {
        $this->display_start = $display_start;
    }

    public function getDisplayStart() : DateTimeImmutable
    {
        return $this->display_start ?? new DateTimeImmutable();
    }

    public function setEventEnd(DateTimeImmutable $event_end) : void
    {
        $this->event_end = $event_end;
    }

    public function getEventEnd() : DateTimeImmutable
    {
        return $this->event_end ?? new DateTimeImmutable();
    }

    public function setEventStart(DateTimeImmutable $event_start) : void
    {
        $this->event_start = $event_start;
    }

    public function getEventStart() : DateTimeImmutable
    {
        return $this->event_start ?? new DateTimeImmutable();
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return (int) $this->id;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return (string) $this->title;
    }

    public function setType(int $type) : void
    {
        $this->type = $type;
    }

    public function getType() : int
    {
        return (int) $this->type;
    }

    public function setTypeDuringEvent(int $type_during_event) : void
    {
        $this->type_during_event = $type_during_event;
    }

    public function getTypeDuringEvent() : int
    {
        return (int) $this->type_during_event;
    }

    public function setDismissable(bool $dismissable) : void
    {
        $this->dismissable = $dismissable;
    }

    public function getDismissable() : bool
    {
        return (bool) $this->dismissable;
    }

    protected function hasEventStarted() : bool
    {
        return $this->getTime() > $this->getEventStart();
    }

    protected function hasDisplayStarted() : bool
    {
        return $this->getTime() > $this->getDisplayStart();
    }

    protected function hasEventEnded() : bool
    {
        return $this->getTime() > $this->getEventEnd();
    }

    protected function hasDisplayEnded() : bool
    {
        return $this->getTime() > $this->getDisplayEnd();
    }

    public function setPermanent(bool $permanent) : void
    {
        $this->permanent = $permanent;
    }

    public function isPermanent() : bool
    {
        return (bool) $this->permanent;
    }

    public function isDuringEvent() : bool
    {
        return $this->hasEventStarted() && !$this->hasEventEnded();
    }

    public function setCreateDate(DateTimeImmutable $create_date) : void
    {
        $this->create_date = $create_date;
    }

    public function getCreateDate() : DateTimeImmutable
    {
        return $this->create_date ?? new DateTimeImmutable();
    }

    public function setCreatedBy(int $created_by) : void
    {
        $this->created_by = $created_by;
    }

    public function getCreatedBy() : int
    {
        return (int) $this->created_by;
    }

    protected function getTime() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }

    public function getLimitedToRoleIds() : array
    {
        return (array) $this->limited_to_role_ids;
    }

    public function setLimitedToRoleIds(array $limited_to_role_ids) : void
    {
        $this->limited_to_role_ids = $limited_to_role_ids;
    }

    public function isLimitToRoles() : bool
    {
        return (bool) $this->limit_to_roles;
    }

    public function setLimitToRoles(bool $limit_to_roles) : void
    {
        $this->limit_to_roles = $limit_to_roles;
    }

}
