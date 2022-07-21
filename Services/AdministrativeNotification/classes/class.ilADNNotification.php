<?php /**
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
 * Class ilADNNotification
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotification extends ActiveRecord
{
    public const POS_TOP = 1;
    public const POS_RIGHT = 2;
    public const POST_LEFT = 3;
    public const POS_BOTTOM = 4;
    public const DATE_FORMAT = 'd.m.Y';
    public const TIME_FORMAT = 'H:i';
    public const DATE_TIME_FORMAT = 'd.m.Y H:i';
    public const TYPE_INFO = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;
    public const TABLE_NAME = 'il_adn_notifications';
    public const LINK_TYPE_NONE = 0;
    public const LINK_TYPE_REF_ID = 1;
    public const LINK_TYPE_URL = 2;
    protected static array $allowed_user_ids = array(0, 13, 6);
    
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
    
    public function dismiss(ilObjUser $ilObjUser) : void
    {
        if ($this->isUserAllowedToDismiss($ilObjUser)) {
            ilADNDismiss::dismiss($ilObjUser, $this);
        }
    }
    
    protected function hasUserDismissed(ilObjUser $ilObjUser) : bool
    {
        if (!$this->getDismissable()) {
            return false;
        }
        
        return ilADNDismiss::hasDimissed($ilObjUser, $this);
    }
    
    public function resetForAllUsers() : void
    {
        foreach (ilADNDismiss::where(array('notification_id' => $this->getId()))->get() as $not) {
            $not->delete();
        }
    }
    
    public function getFullTimeFormated() : string
    {
        if ($this->getEventStart() == 0 && $this->getEventEnd() == 0) {
            return '';
        }
        if (date(self::DATE_FORMAT, $this->getEventStart()) === date(self::DATE_FORMAT, $this->getEventEnd())) {
            return date(self::DATE_FORMAT, $this->getEventEnd()) . ', ' . date(
                self::TIME_FORMAT,
                $this->getEventStart()
            ) . " - "
                . date(self::TIME_FORMAT, $this->getEventEnd());
        } else {
            return date(self::DATE_TIME_FORMAT, $this->getEventStart()) . ' - ' . date(
                self::DATE_TIME_FORMAT,
                $this->getEventEnd()
            );
        }
    }
    
    public function isUserAllowedToDismiss(ilObjUser $user) : bool
    {
        return ($this->getDismissable() && $user->getId() !== 0 && $user->getId() !== ANONYMOUS_USER_ID);
    }
    
    public function getActiveType() : int
    {
        if ($this->isPermanent()) {
            return $this->getType();
        }
        if ($this->hasEventStarted() && !$this->hasEventEnded()) {
            return $this->getTypeDuringEvent();
        }
        if ($this->hasDisplayStarted() && !$this->hasDisplayEnded()) {
            return $this->getType();
        }
        return self::TYPE_INFO;
    }
    

    protected function isVisible() : bool
    {
        if ($this->isPermanent()) {
            return true;
        }
        $hasEventStarted = $this->hasEventStarted();
        $hasDisplayStarted = $this->hasDisplayStarted();
        $hasEventEnded = !$this->hasEventEnded();
        $hasDisplayEnded = !$this->hasDisplayEnded();
        
        return ($hasEventStarted || $hasDisplayStarted)
            && ($hasEventEnded || $hasDisplayEnded);
    }
    
    public function isVisibleForUser(ilObjUser $ilObjUser) : bool
    {
        if ($ilObjUser->getId() === 0 && $this->interruptive) {
            return false;
        }
        if (!$this->isVisible()) {
            return false;
        }
        if ($this->hasUserDismissed($ilObjUser)) {
            return false;
        }
        return $this->isVisibleRoleUserRoles($ilObjUser);
    }
    
 
    protected function isVisibleRoleUserRoles(ilObjUser $ilObjUser) : bool
    {
        if (!$this->isLimitToRoles()) {
            return true;
        }
        global $DIC;
        
        if ($ilObjUser->getId() === 0 && in_array(0, $this->getLimitedToRoleIds())) {
            return true;
        }
        
        return $DIC->rbac()->review()->isAssignedToAtLeastOneGivenRole(
            $ilObjUser->getId(),
            $this->getLimitedToRoleIds()
        );
    }
    
    /**
     * @con_is_primary true
     * @con_sequence   true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $title = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected string $body = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?\DateTimeImmutable $event_start = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?\DateTimeImmutable $event_end = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?\DateTimeImmutable $display_start = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?\DateTimeImmutable $display_end = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $type = self::TYPE_INFO;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $type_during_event = self::TYPE_ERROR;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $dismissable = true;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $permanent = true;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected array $allowed_users = array(0, 6, 13);
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $parent_id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?\DateTimeImmutable $create_date = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected \DateTimeImmutable $last_update;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $created_by = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $last_update_by = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $active = true;
    /**
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected array $limited_to_role_ids = [];
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $limit_to_roles = false;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $interruptive = false;
    /**
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected string $link = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $link_type = self::LINK_TYPE_NONE;
    /**
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected string $link_target = '_top';
    
    /**
     * @param string $field_name
     * @param mixed $field_value
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
            case 'limited_to_role_ids':
                return json_decode($field_value, true);
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
            case 'allowed_users':
                $allowed_users = self::$allowed_user_ids;
                foreach ($this->allowed_users as $user_id) {
                    $allowed_users[] = (int) $user_id;
                }
                
                return json_encode(array_unique($allowed_users), JSON_THROW_ON_ERROR);
            case 'limited_to_role_ids':
                return json_encode($this->{$field_name}, JSON_THROW_ON_ERROR);
        }
    }
    
    public function create() : void
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
        return $this->body;
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
        return $this->title;
    }
    
    public function setType(int $type) : void
    {
        $this->type = $type;
    }
    
    public function getType() : int
    {
        return $this->type;
    }
    
    public function setTypeDuringEvent(int $type_during_event) : void
    {
        $this->type_during_event = $type_during_event;
    }
    
    public function getTypeDuringEvent() : int
    {
        return in_array(
            $this->type_during_event,
            [self::TYPE_WARNING, self::TYPE_ERROR, self::TYPE_INFO]
        ) ? $this->type_during_event : self::TYPE_INFO;
    }
    
    public function setDismissable(bool $dismissable) : void
    {
        $this->dismissable = $dismissable;
    }
    
    public function getDismissable() : bool
    {
        return $this->dismissable;
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
        return $this->permanent;
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
        return $this->limited_to_role_ids;
    }
    
    public function setLimitedToRoleIds(array $limited_to_role_ids) : void
    {
        $this->limited_to_role_ids = $limited_to_role_ids;
    }
    
    public function isLimitToRoles() : bool
    {
        return $this->limit_to_roles;
    }
    
    public function setLimitToRoles(bool $limit_to_roles) : void
    {
        $this->limit_to_roles = $limit_to_roles;
    }
}
