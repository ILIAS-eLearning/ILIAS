<?php

/**
 * Class ilADNNotificationGUI
 * @ilCtrl_IsCalledBy ilADNNotificationGUI: ilObjAdministrativeNotificationGUI
 * @ilCtrl_IsCalledBy ilADNNotificationGUI: ilObjAdministrativeNotificationGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilADNNotificationGUI extends ilADNAbstractGUI
{
    public const TAB_TABLE = 'notifications';
    public const CMD_DEFAULT = 'index';
    public const CMD_ADD = 'add';
    public const CMD_CREATE = 'save';
    public const CMD_UPDATE = 'update';
    public const CMD_EDIT = 'edit';
    public const CMD_CANCEL = 'cancel';
    public const CMD_DELETE = 'delete';
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    public const CMD_CONFIRM_RESET = 'confirmReset';
    public const CMD_RESET = 'reset';

    protected function dispatchCommand($cmd) : string
    {
        $this->tab_handling->initTabs(ilObjAdministrativeNotificationGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS,
            true, self::class);
        switch ($cmd) {
            case self::CMD_ADD:
                return $this->add();
            case self::CMD_CREATE:
                return $this->create();
            case self::CMD_EDIT:
                return $this->edit();
            case self::CMD_UPDATE:
                return $this->update();
            case self::CMD_CONFIRM_DELETE:
                return $this->confirmDelete();
            case self::CMD_DELETE:
                $this->delete();
                break;
            case self::CMD_CONFIRM_RESET:
                return $this->confirmReset();
            case self::CMD_RESET:
                return $this->reset();
            case self::CMD_DEFAULT:
            default:
                return $this->index();

        }

        return "";
    }

    protected function index() : string
    {
        if($this->access->hasUserPermissionTo('write')) {
            $button = ilLinkButton::getInstance();
            $button->setCaption($this->lng->txt('common_add_msg'), false);
            $button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD));
            $this->toolbar->addButtonInstance($button);
        }

        $notMessageTableGUI = new ilADNNotificationTableGUI($this, self::CMD_DEFAULT);
        return $notMessageTableGUI->getHTML();
    }

    protected function add() : string
    {
        $form = new ilADNNotificationUIFormGUI(new ilADNNotification(),
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE));
        $form->fillForm();
        return $form->getHTML();
    }

    protected function create() : string
    {
        $form = new ilADNNotificationUIFormGUI(new ilADNNotification(),
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE));
        $form->setValuesByPost();
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_success_created'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        return $form->getHTML();
    }

    protected function cancel() : string
    {
        $this->ctrl->setParameter($this, self::IDENTIFIER, null);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function edit() : string
    {
        $notification = $this->getNotificationFromRequest();
        $this->ctrl->setParameter($this, ilADNNotificationGUI::IDENTIFIER, $notification->getId());

        $form = new ilADNNotificationUIFormGUI($notification, $this->ctrl->getLinkTarget($this, self::CMD_UPDATE));
        $form->fillForm();
        return $form->getHTML();
    }

    protected function update() : string
    {
        $notification = $this->getNotificationFromRequest();
        $form = new ilADNNotificationUIFormGUI($notification, $this->ctrl->getLinkTarget($this, self::CMD_UPDATE));
        $form->setValuesByPost();
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_success_updated'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        return $form->getHTML();
    }

    protected function confirmDelete() : string
    {
        $notification = $this->getNotificationFromRequest();
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->addItem(self::IDENTIFIER, $notification->getId(), $notification->getTitle());
        $confirmation->setCancel($this->lng->txt('msg_form_button_cancel'), self::CMD_CANCEL);
        $confirmation->setConfirm($this->lng->txt('msg_form_button_delete'), self::CMD_DELETE);

        return $confirmation->getHTML();
    }

    protected function delete() : void
    {
        $notification = $this->getNotificationFromRequest();
        $notification->delete();
        ilUtil::sendSuccess($this->lng->txt('msg_success_deleted'), true);
        $this->cancel();
    }

    protected function confirmReset() : string
    {
        $notification = $this->getNotificationFromRequest();
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->addItem(self::IDENTIFIER, $notification->getId(), $notification->getTitle());
        $confirmation->setCancel($this->lng->txt('msg_form_button_cancel'), self::CMD_CANCEL);
        $confirmation->setConfirm($this->lng->txt('msg_form_button_reset'), self::CMD_RESET);

        return $confirmation->getHTML();
    }

    protected function reset()
    {
        $notification = $this->getNotificationFromRequest();

        $notification->resetForAllUsers();
        ilUtil::sendInfo($this->lng->txt('msg_success_reset'), true);
        $this->cancel();
    }

    /**
     * @return ilADNNotification
     */
    protected function getNotificationFromRequest() : ActiveRecord
    {
        if (isset($this->http->request()->getParsedBody()[self::IDENTIFIER])) {
            $identifier = $this->http->request()->getParsedBody()[self::IDENTIFIER];
        } elseif (isset($this->http->request()->getParsedBody()['interruptive_items'][0])) {
            $identifier = $this->http->request()->getParsedBody()['interruptive_items'][0];
        } else {
            $identifier = $this->http->request()->getQueryParams()[self::IDENTIFIER];
        }

        return ilADNNotification::findOrFail($identifier);
    }
}
