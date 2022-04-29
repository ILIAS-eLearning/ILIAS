<?php declare(strict_types=1);

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
 * Class ilForumNotificationTableGUI
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup ModulesForum
 */
class ilForumNotificationTableGUI extends ilTable2GUI
{
    private ilGlobalTemplateInterface $mainTemplate;
    private array $notification_modals = [];
    private \ILIAS\UI\Factory $ui_factory;
    private ILIAS\UI\Renderer $ui_renderer;
    private int $ref_id;

    public function __construct(ilForumSettingsGUI $cmd_class_instance, string $cmd, string $type)
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->ref_id = $cmd_class_instance->ref_id;

        $this->setId('frmevents_' . $this->ref_id . '_' . $type);

        parent::__construct($cmd_class_instance, $cmd);

        $this->setTitle($this->lng->txt(strtolower($type)));
        $this->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
        $this->setFormAction($this->ctrl->getFormAction($cmd_class_instance, 'showMembers'));

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

    private function getIcon(int $user_toggle_noti) : string
    {
        $icon_ok = $this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('icon_ok.svg'),
            $this->lng->txt('enabled')
        );
        $icon_not_ok = $this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('icon_not_ok.svg'),
            $this->lng->txt('disabled')
        );
        $icon = $user_toggle_noti === 0 ? $icon_ok : $icon_not_ok;

        return $this->ui_renderer->render($icon);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_USER_ID', $a_set['user_id']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
        $this->tpl->setVariable('VAL_FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('VAL_LASTNAME', $a_set['lastname']);

        $icon_ok = $this->getIcon((int) $a_set['user_toggle_noti']);
        $this->tpl->setVariable('VAL_USER_TOGGLE_NOTI', $icon_ok);

        $this->populateWithModal($a_set);
    }

    private function getNotificationSettingsForm(array $row) : ilPropertyFormGUI
    {
        $form = new ilForumNotificationEventsFormGUI($this->parent_obj, $this->ref_id, 0);
        $form->setFormAction($this->ctrl->getFormAction($this->parent_obj, 'saveEventsForUser'));
        $form->setId(uniqid('frm_ntf_usr_set_' . $row['login'], true));

        return $form;
    }

    private function populateWithModal(array $row) : void
    {
        $interested_events = $row['interested_events'];
        $form = $this->getNotificationSettingsForm($row);
        $hidden_value = [
            'ref_id' => $this->parent_obj->getRefId(),
            'notification_id' => $row['notification_id'],
            'usr_id_events' => $row['usr_id_events'],
            'forum_id' => $row['forum_id'],
        ];

        $event_values = [
            'hidden_value' => json_encode($hidden_value, JSON_THROW_ON_ERROR),
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
                ->withOnLoadCode(function (string $id) use ($form) : string {
                    return "$('#$id').click(function() { $('#form_{$form->getId()}').submit(); return false; });";
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

    public function render() : string
    {
        $url = $this->ctrl->getLinkTarget($this->parent_obj, 'saveEventsForUser', '', true, false);

        $this->mainTemplate->addJavaScript('Modules/Forum/js/ilFrmEvents.js');
        $this->mainTemplate->addOnLoadCode('il.FrmEvents.init("' . $url . '");');

        return parent::render() . $this->ui_renderer->render($this->notification_modals);
    }
}
