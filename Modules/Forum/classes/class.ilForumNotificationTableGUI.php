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
     * @var \ilTemplate
     */
    protected $mainTemplate;
    
    protected $notification_modals = [];
    
    protected $ui_factory;
    protected $ui_renderer;
    protected $user;
    protected $settings;
    
    private $ref_id;
    
    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->ref_id = $this->parent_obj->ref_id;
        $this->parent_cmd = $a_parent_cmd;
        $this->setId('frmevents_' . $this->ref_id . '_' . uniqid());
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
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

    protected function getIcon(string $user_toggle_noti) : string
    {
        $icon_ok = $this->ui_factory->symbol()->icon()->custom(\ilUtil::getImagePath("icon_ok.svg"), $this->lng->txt("enabled"));
        $icon_not_ok = $this->ui_factory->symbol()->icon()->custom(\ilUtil::getImagePath("icon_not_ok.svg"), $this->lng->txt("disabled"));
        $icon = $user_toggle_noti ? $icon_ok : $icon_not_ok;
        
        return $this->ui_renderer->render($icon);
    }
    
    /**
     * @ineritdoc
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_USER_ID', $a_set['user_id']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
        $this->tpl->setVariable('VAL_FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('VAL_LASTNAME', $a_set['lastname']);
        
        $icon_ok = $this->getIcon(!$a_set['user_toggle_noti']);
        $this->tpl->setVariable('VAL_USER_TOGGLE_NOTI', $icon_ok);

        $this->populateWithModal($a_set);
    }

    private function getNotificationSettingsForm(array $row) : ilPropertyFormGUI
    {
        $form = new ilForumNotificationEventsFormGUI($this->parent_obj, $this->ref_id, 0);
        $form->setFormAction($this->ctrl->getFormAction($this->parent_obj, 'saveEventsForUser'));
        $form->setId(uniqid('frm_ntf_usr_set_' . $row['login']));

        return $form;
    }

    private function populateWithModal(array $row) : void
    {
        $interested_events = $row['interested_events'];
        $form = $this->getNotificationSettingsForm($row);
        $hidden_value = array(
            'ref_id' => $this->parent_obj->ref_id,
            'notification_id' => $row['notification_id'],
            'usr_id_events' => $row['usr_id_events'],
            'forum_id' => $row['forum_id'],
        );

        $event_values =
            [
                'hidden_value' => json_encode($hidden_value),
                'notify_modified' => $interested_events & ilForumNotificationEvents::UPDATED,
                'notify_censored' => $interested_events & ilForumNotificationEvents::CENSORED,
                'notify_uncensored' => $interested_events & ilForumNotificationEvents::UNCENSORED,
                'notify_post_deleted' => $interested_events & ilForumNotificationEvents::POST_DELETED,
                'notify_thread_deleted' => $interested_events & ilForumNotificationEvents::THREAD_DELETED,
            ];
        $form->setValuesByArray($event_values);

        $notificationsModal = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('notification_settings'),
            $this->ui_factory->legacy($form->getHTML())
        )->withActionButtons([
            $this->ui_factory->button()
                ->primary($this->lng->txt('save'), '#')
                ->withOnLoadCode(function ($id) use ($form) {
                    return "$('#{$id}').click(function() { $('#form_{$form->getId()}').submit(); return false; });";
                })
        ]);
    
        $showNotificationSettingsBtn = $this->ui_factory->button()
            ->shy($this->lng->txt('notification_settings'), '#')
            ->withOnClick(
                $notificationsModal->getShowSignal()
            );

        $this->notification_modals[] = $notificationsModal;

        $this->tpl->setVariable('VAL_NOTIFICATION', $this->ui_renderer->render($showNotificationSettingsBtn));
    }
    
    /**
     * @return string
     */
    public function render()
    {
        $url = $this->ctrl->getLinkTarget($this->parent_obj, "saveEventsForUser", "", true, false);
    
        $this->mainTemplate->addJavaScript("Modules/Forum/js/ilFrmEvents.js");
        $this->mainTemplate->addOnLoadCode('il.FrmEvents.init("' . $url . '");');
        
        return parent::render() . $this->ui_renderer->render($this->notification_modals);
    }
}
