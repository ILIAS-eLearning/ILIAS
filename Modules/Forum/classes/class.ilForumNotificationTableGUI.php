<?php
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumNotificationTableGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup ModulesForum
 */
class ilForumNotificationTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    
    /**
     * @var \ilTemplate
     */
    protected $mainTemplate;
    
    protected $notification_modals = [];
    
    protected $ui_factory;
    protected $ui_renderer;
    
    private $ref_id;
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        global $DIC;
        
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        
        $this->parent_cmd = $a_parent_cmd;
        $this->setId('frmevents_'. uniqid());
        
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        $this->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'showMembers'));
    
        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), '', '20%');
        $this->addColumn($this->lng->txt('firstname'), '', '20%');
        $this->addColumn($this->lng->txt('lastname'), '', '20%');
        $this->addColumn($this->lng->txt('allow_user_toggle_noti'), '', '20%');
        $this->addColumn($this->lng->txt('actions'), '', '20%');
        $this->setSelectAllCheckbox('user_id');
    
    
        $this->addMultiCommand('enableHideUserToggleNoti', $this->lng->txt('enable_hide_user_toggle'));
        $this->addMultiCommand('disableHideUserToggleNoti', $this->lng->txt('disable_hide_user_toggle'));

    }
    
    /**
     * @param $user_toggle_noti
     * @return string
     */
    protected function getIcon($user_toggle_noti)
    {
        $icon = $user_toggle_noti
            ? "<img src=\"" . ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"" . $this->lng->txt("enabled") . "\" title=\"" . $this->lng->txt("enabled") . "\" border=\"0\" vspace=\"0\"/>"
            : "<img src=\"" . ilUtil::getImagePath("icon_not_ok.svg") . "\" alt=\"" . $this->lng->txt("disabled") . "\" title=\"" . $this->lng->txt("disabled") . "\" border=\"0\" vspace=\"0\"/>";
        return $icon;
    }
    
    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_USER_ID', $a_set['user_id']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
        $this->tpl->setVariable('VAL_FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('VAL_LASTNAME', $a_set['lastname']);
        
        $icon_ok = $this->getIcon(!$a_set['user_toggle_noti']);
        $this->tpl->setVariable('VAL_USER_TOGGLE_NOTI', $icon_ok);
        
        $notification_id = $a_set['notification_id'];
       
        
        $button = $this->ui_factory->button()->standard($this->lng->txt("notification_settings"), "#")
            ->withOnLoadCode(function ($id) use ($notification_id) {
                return "$('#$id').on('click', function() {il.FrmEvents.showEvents('$notification_id'); return false;})";
            });
        $this->generateModal($a_set);
        
        $this->tpl->setVariable('VAL_NOTIFICATION', $this->ui_renderer->render($button));
    }
    
    /**
     * @return ilPropertyFormGUI
     */
    private function initUserNotificationForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this->parent_obj, 'saveEventsForUser'));
        $form->addCommandButton('saveEventsForUser', $this->lng->txt('save'));
        
        $notify_modified = new ilCheckboxInputGUI($this->lng->txt('notify_modified'), 'notify_modified');
        $notify_modified->setValue(\ilForumNotificationEvents::UPDATED);
        $form->addItem($notify_modified);
        
        $notify_censored = new ilCheckboxInputGUI($this->lng->txt('notify_censored'), 'notify_censored');
        $notify_censored->setValue(\ilForumNotificationEvents::CENSORED);
        $form->addItem($notify_censored);
        
        $notify_uncensored = new ilCheckboxInputGUI($this->lng->txt('notify_uncensored'), 'notify_uncensored');
        $notify_uncensored->setValue(\ilForumNotificationEvents::UNCENSORED);
        $form->addItem($notify_uncensored);
        
        $notify_post_deleted = new ilCheckboxInputGUI($this->lng->txt('notify_post_deleted'), 'notify_post_deleted');
        $notify_post_deleted->setValue(\ilForumNotificationEvents::POST_DELETED);
        $form->addItem($notify_post_deleted);
        
        $notify_thread_deleted = new ilCheckboxInputGUI($this->lng->txt('notify_thread_deleted'),
            'notify_thread_deleted');
        $notify_thread_deleted->setValue(\ilForumNotificationEvents::THREAD_DELETED);
        $form->addItem($notify_thread_deleted);
        
        $hidden_value = new ilHiddenInputGUI('hidden_value');
        $form->addItem($hidden_value);
        
        return $form;
    }
    
    /**
     * @param $forum_noti
     */
    private function generateModal($row)
    {
        $modal = ilModalGUI::getInstance();
        $modal->setId($row['notification_id']);
        $modal->setHeading($this->lng->txt("notification_settings"));
        
        $interested_events = $row['interested_events'];
        $interested_noti_form = $this->initUserNotificationForm();
        $hidden_value = array(
            'ref_id' => $this->parent_obj->ref_id,
            'notification_id' => $row['notification_id'],
            'usr_id_events' => $row['usr_id_events'],
            'forum_id' => $row['forum_id'],
        );
        
        $event_values =
            [
                'hidden_value' => json_encode($hidden_value),
                'notify_modified' =>  $interested_events & \ilForumNotificationEvents::UPDATED,
                'notify_censored' =>  $interested_events & \ilForumNotificationEvents::CENSORED,
                'notify_uncensored' =>  $interested_events & \ilForumNotificationEvents::UNCENSORED,
                'notify_post_deleted' =>  $interested_events & \ilForumNotificationEvents::POST_DELETED,
                'notify_thread_deleted' =>  $interested_events & \ilForumNotificationEvents::THREAD_DELETED,
            ];
        $interested_noti_form->setValuesByArray($event_values);
        
        $modal->setBody($interested_noti_form->getHTML());
        
        $this->notification_modals[] = $modal->getHTML();
        unset($modal);
    }
    
    /**
     * @return string
     */
    public function render()
    {
        global $DIC;
        
        $tpl = $DIC->ui()->mainTemplate();
        
        $ctrl = $DIC->ctrl();
        
        
        $url = $ctrl->getLinkTarget($this->parent_obj, "saveEventsForUser", "", true, false);
        
        $tpl->addJavaScript("Modules/Forum/js/ilFrmEvents.js");
        $tpl->addOnLoadCode('il.FrmEvents.init("' . $url . '");');
        
        return parent::render() .
            implode("\n", $this->notification_modals);
    }
}