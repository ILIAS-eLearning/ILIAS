<?php

/**
 * Class ilADNNotificationFormGUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotificationFormGUI extends ilPropertyFormGUI
{

    const F_TITLE = 'title';
    const F_BODY = 'body';
    const F_TYPE = 'type';
    const F_TYPE_DURING_EVENT = 'type_during_event';
    const F_EVENT_DATE = 'event_date';
    const F_DISPLAY_DATE = 'display_date';
    const F_PERMANENT = 'permanent';
    const F_POSITION = 'position';
    const F_ADDITIONAL_CLASSES = 'additional_classes';
    const F_PREVENT_LOGIN = 'prevent_login';
    const F_INTERRUPTIVE = 'interruptive';
    const F_ALLOWED_USERS = 'allowed_users';
    const F_DISMISSABLE = 'dismissable';
    const F_LIMIT_TO_ROLES = 'limit_to_roles';
    const F_LIMITED_TO_ROLE_IDS = 'limited_to_role_ids';
    /**
     * @var ilADNNotification
     */
    protected $notification;
    /**
     * @var array
     */
    protected static $tags = array(
        'a',
        'strong',
        'ol',
        'ul',
        'li',
        'p',
    );
    /**
     * @var bool
     */
    protected $is_new;
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilADNNotificationGUI $parent_gui
     * @param ilADNNotification    $notification
     */
    public function __construct(ilADNNotificationGUI $parent_gui, ilADNNotification $notification)
    {
        parent::__construct();
        global $DIC;
        $this->ctrl         = $DIC->ctrl();
        $this->lng          = $DIC->language();
        $this->notification = $notification;
        $this->is_new       = (int) $notification->getId() === 0;
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->initForm();
    }

    /**
     * @param string $var
     * @return string
     */
    protected function txt(string $var) : string
    {
        return $this->lng->txt('msg_' . $var);
    }

    /**
     * @param string $var
     * @return string
     */
    protected function infoTxt(string $var) : string
    {
        return $this->lng->txt('msg_' . $var . '_info');
    }

    public function initForm():void
    {
        $this->setTitle($this->txt('form_title'));

        $type = new ilSelectInputGUI($this->txt(self::F_TYPE), self::F_TYPE);
        $type->setOptions(array(
            ilADNNotification::TYPE_INFO    => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_INFO),
            ilADNNotification::TYPE_WARNING => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_WARNING),
            ilADNNotification::TYPE_ERROR   => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_ERROR),

        ));
        $this->addItem($type);

        $title = new ilTextInputGUI($this->txt(self::F_TITLE), self::F_TITLE);
        $this->addItem($title);

        $body = new ilTextAreaInputGUI($this->txt(self::F_BODY), self::F_BODY);
        $body->setUseRte(true);
        $body->setRteTags(self::$tags);
        $this->addItem($body);

        $permanent = new ilRadioGroupInputGUI($this->txt(self::F_PERMANENT), self::F_PERMANENT);

        $permanent_yes = new ilRadioOption($this->txt(self::F_PERMANENT . '_yes'), 1);
        $permanent->addOption($permanent_yes);
        $this->addItem($permanent);

        $dismissable = new ilCheckboxInputGUI($this->txt(self::F_DISMISSABLE), self::F_DISMISSABLE);
        $dismissable->setInfo($this->infoTxt(self::F_DISMISSABLE));
        $this->addItem($dismissable);

        $limit_to_roles      = new ilCheckboxInputGUI($this->txt(self::F_LIMIT_TO_ROLES), self::F_LIMIT_TO_ROLES);
        $limited_to_role_ids = new ilMultiSelectInputGUI($this->txt(self::F_LIMITED_TO_ROLE_IDS), self::F_LIMITED_TO_ROLE_IDS);
        $limited_to_role_ids->setOptions(self::getRoles(ilRbacReview::FILTER_ALL_GLOBAL));
        $limit_to_roles->addSubItem($limited_to_role_ids);
        $limit_to_roles->setInfo($this->infoTxt(self::F_LIMIT_TO_ROLES));
        $this->addItem($limit_to_roles);

        $permanent_no = new ilRadioOption($this->txt(self::F_PERMANENT . '_no'), 0);
        $display_time = new ilDateDurationInputGUI($this->txt(self::F_DISPLAY_DATE), self::F_DISPLAY_DATE);
        $display_time->setShowTime(true);
        $display_time->setMinuteStepSize(1);
        $permanent_no->addSubItem($display_time);
        $event_time = new ilDateDurationInputGUI($this->txt(self::F_EVENT_DATE), self::F_EVENT_DATE);
        $event_time->setShowTime(true);
        $event_time->setMinuteStepSize(1);
        $permanent_no->addSubItem($event_time);
        $type_during_event = new ilSelectInputGUI($this->txt(self::F_TYPE_DURING_EVENT), self::F_TYPE_DURING_EVENT);
        $type_during_event->setOptions(array(
            ilADNNotification::TYPE_INFO    => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_INFO),
            ilADNNotification::TYPE_WARNING => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_WARNING),
            ilADNNotification::TYPE_ERROR   => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_ERROR),

        ));
        $permanent_no->addSubItem($type_during_event);

        $permanent->addOption($permanent_no);

        $this->addButtons();
    }

    public function fillForm():void
    {
        $array = array(
            self::F_TITLE               => $this->notification->getTitle(),
            self::F_BODY                => $this->notification->getBody(),
            self::F_TYPE                => $this->notification->getType(),
            self::F_TYPE_DURING_EVENT   => $this->notification->getTypeDuringEvent(),
            self::F_PERMANENT           => (int) $this->notification->getPermanent(),
            self::F_DISMISSABLE         => $this->notification->getDismissable(),
            self::F_LIMIT_TO_ROLES      => $this->notification->isLimitToRoles(),
            self::F_LIMITED_TO_ROLE_IDS => $this->notification->getLimitedToRoleIds(),
        );
        $this->setValuesByArray($array);
        /**
         * @var ilDateDurationInputGUI $f_event_date
         * @var ilDateDurationInputGUI $f_display_date
         */
        if ($eventStart = $this->notification->getEventStart()) {
            $f_event_date = $this->getItemByPostVar(self::F_EVENT_DATE);
            $f_event_date->setStart(new ilDateTime($eventStart, IL_CAL_UNIX));
            $f_event_date->setEnd(new ilDateTime($this->notification->getEventEnd(), IL_CAL_UNIX));
        }

        if ($displayStart = $this->notification->getDisplayStart()) {
            $f_display_date = $this->getItemByPostVar(self::F_DISPLAY_DATE);
            $f_display_date->setStart(new ilDateTime($displayStart, IL_CAL_UNIX));
            $f_display_date->setEnd(new ilDateTime($this->notification->getDisplayEnd(), IL_CAL_UNIX));
        }
    }

    /**
     * @return bool
     */
    protected function fillObject():bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->notification->setTitle($this->getInput(self::F_TITLE));
        $this->notification->setBody($this->getInput(self::F_BODY));
        $this->notification->setType($this->getInput(self::F_TYPE));
        $this->notification->setTypeDuringEvent($this->getInput(self::F_TYPE_DURING_EVENT));
        $this->notification->setPermanent($this->getInput(self::F_PERMANENT));
        $this->notification->setDismissable($this->getInput(self::F_DISMISSABLE));
        $this->notification->setLimitToRoles($this->getInput(self::F_LIMIT_TO_ROLES));
        $this->notification->setLimitedToRoleIds($this->getInput(self::F_LIMITED_TO_ROLE_IDS));

        /**
         * @var ilDateDurationInputGUI $f_event_date
         * @var ilDateDurationInputGUI $f_display_date
         */
        $f_event_date = $this->getItemByPostVar(self::F_EVENT_DATE);
        if ($f_event_date->getStart() instanceof ilDateTime) {
            $this->notification->setEventStart($f_event_date->getStart()->get(IL_CAL_UNIX));
        }
        if ($f_event_date->getEnd() instanceof ilDateTime) {
            $this->notification->setEventEnd($f_event_date->getEnd()->get(IL_CAL_UNIX));
        }

        $f_display_date = $this->getItemByPostVar(self::F_DISPLAY_DATE);
        if ($f_display_date->getStart() instanceof ilDateTime) {
            $this->notification->setDisplayStart($f_display_date->getStart()->get(IL_CAL_UNIX));
        }
        if ($f_display_date->getEnd() instanceof ilDateTime) {
            $this->notification->setDisplayEnd($f_display_date->getEnd()->get(IL_CAL_UNIX));
        }

        return true;
    }

    /**
     * @param ilDateTime $ilDate_start
     * @param ilDateTime $ilDate_end
     * @return array
     */
    public function getDateArray(ilDateTime $ilDate_start, ilDateTime $ilDate_end) : array
    {
        $return               = array();
        $timestamp            = $ilDate_start->get(IL_CAL_UNIX);
        $return['start']['d'] = date('d', $timestamp);
        $return['start']['m'] = date('m', $timestamp);
        $return['start']['y'] = date('Y', $timestamp);
        $timestamp            = $ilDate_end->get(IL_CAL_UNIX);
        $return['end']['d']   = date('d', $timestamp);
        $return['end']['m']   = date('m', $timestamp);
        $return['end']['y']   = date('Y', $timestamp);

        return $return;
    }

    /**
     * @return bool false when unsuccessful or int request_id when successful
     */
    public function saveObject() : int
    {
        if (!$this->fillObject()) {
            return false;
        }
        if ($this->notification->getId() > 0) {
            $this->notification->update();
        } else {
            $this->notification->create();
        }

        return (int) $this->notification->getId();
    }

    protected function addButtons() : void
    {
        if ($this->is_new) {
            $this->addCommandButton(ilADNNotificationGUI::CMD_CREATE, $this->txt('form_button_' . ilADNNotificationGUI::CMD_CREATE));
        } else {
            $this->addCommandButton(ilADNNotificationGUI::CMD_UPDATE, $this->txt('form_button_'
                . ilADNNotificationGUI::CMD_UPDATE));
        }
        $this->addCommandButton(ilADNNotificationGUI::CMD_CANCEL, $this->txt('form_button_' . ilADNNotificationGUI::CMD_CANCEL));
    }

    /**
     * @param int  $filter
     * @param bool $with_text
     * @return array
     */
    public static function getRoles($filter, $with_text = true) : array
    {
        global $DIC;
        $opt      = array(0 => 'Login');
        $role_ids = array(0);
        foreach ($DIC->rbac()->review()->getRolesByFilter($filter) as $role) {
            $opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
            $role_ids[]           = $role['obj_id'];
        }
        if ($with_text) {
            return $opt;
        } else {
            return $role_ids;
        }
    }
}
