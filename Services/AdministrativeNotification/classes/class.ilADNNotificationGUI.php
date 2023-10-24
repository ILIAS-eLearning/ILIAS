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
    public const CMD_DUPLICATE = 'duplicate';
    public const CMD_CANCEL = 'cancel';
    public const CMD_DELETE = 'delete';
    public const CMD_RESET = 'reset';

    protected function dispatchCommand($cmd): string
    {
        $this->tab_handling->initTabs(
            ilObjAdministrativeNotificationGUI::TAB_MAIN,
            ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS,
            true
        );
        switch ($cmd) {
            case self::CMD_ADD:
                return $this->add();
            case self::CMD_CREATE:
                return $this->create();
            case self::CMD_EDIT:
                return $this->edit();
            case self::CMD_DUPLICATE:
                $this->duplicate();
                break;
            case self::CMD_UPDATE:
                return $this->update();
            case self::CMD_DELETE:
                $this->delete();
                break;
            case self::CMD_RESET:
                $this->reset();
                break;
            case self::CMD_DEFAULT:
            default:
                return $this->index();
        }

        return "";
    }

    protected function index(): string
    {
        if ($this->access->hasUserPermissionTo('write')) {
            $btn_add_msg = $this->ui->factory()->button()->standard(
                $this->lng->txt('common_add_msg'),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD)
            );
            $this->toolbar->addComponent($btn_add_msg);
        }

        return (new ilADNNotificationTableGUI($this, self::CMD_DEFAULT))->getHTML();
    }

    protected function add(): string
    {
        $form = new ilADNNotificationUIFormGUI(
            new ilADNNotification(),
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE)
        );
        return $form->getHTML();
    }

    protected function create(): string
    {
        $form = new ilADNNotificationUIFormGUI(
            new ilADNNotification(),
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE)
        );
        $form->setValuesByPost();
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_created'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        return $form->getHTML();
    }

    protected function cancel(): void
    {
        $this->ctrl->setParameter($this, self::IDENTIFIER, null);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function edit(): string
    {
        $notification = $this->getNotificationFromRequest();
        $this->ctrl->setParameter($this, ilADNAbstractGUI::IDENTIFIER, $notification->getId());

        $form = new ilADNNotificationUIFormGUI($notification, $this->ctrl->getLinkTarget($this, self::CMD_UPDATE));
        return $form->getHTML();
    }

    protected function duplicate(): void
    {
        $notification = $this->getNotificationFromRequest();
        $notification->setId(0);
        $notification->create();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_duplicated'), true);
        $this->cancel();
    }

    protected function update(): string
    {
        $notification = $this->getNotificationFromRequest();
        $form = new ilADNNotificationUIFormGUI($notification, $this->ctrl->getLinkTarget($this, self::CMD_UPDATE));
        $form->setValuesByPost();
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_updated'), true);
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        return $form->getHTML();
    }

    protected function delete(): void
    {
        $notification = $this->getNotificationFromRequest();
        $notification->delete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_deleted'), true);
        $this->cancel();
    }

    protected function reset(): void
    {
        $notification = $this->getNotificationFromRequest();

        $notification->resetForAllUsers();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_reset'), true);
        $this->cancel();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function getNotificationFromRequest(): ilADNNotification
    {
        if (isset($this->http->request()->getParsedBody()[self::IDENTIFIER])) {
            $identifier = $this->http->request()->getParsedBody()[self::IDENTIFIER];
        } elseif (isset($this->http->request()->getParsedBody()['interruptive_items'][0])) {
            $identifier = $this->http->request()->getParsedBody()['interruptive_items'][0];
        } else {
            $identifier = $this->http->request()->getQueryParams()[self::IDENTIFIER] ?? null;
        }

        return ilADNNotification::findOrFail($identifier);
    }
}
