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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard;
use ILIAS\AdministrativeNotification\Table;
use ILIAS\Filesystem\Stream\Streams;

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
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    private readonly Factory $ui_factory;
    private readonly Renderer $ui_renderer;
    protected Table $table;

    public function __construct(ilADNTabHandling $tab_handling)
    {
        global $DIC;
        parent::__construct($tab_handling);
        $this->table = new Table($this);
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

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
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DEFAULT:
            default:
                return $this->index();
        }

        return "";
    }

    protected function index(): string
    {
        $this->table = new Table($this);
        if ($this->access->hasUserPermissionTo('write')) {
            $btn_add_msg = $this->ui->factory()->button()->standard(
                $this->lng->txt('common_add_msg'),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD)
            );
            $this->toolbar->addComponent($btn_add_msg);
        }
        return $this->table->getHTML();
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
        foreach ($this->getNotificationsFromRequest() as $notification) {
            $notification->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_deleted'), true);
        $this->cancel();
    }

    protected function reset(): void
    {
        foreach ($this->getNotificationsFromRequest() as $notification) {
            $notification->resetForAllUsers();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_reset'), true);
        $this->cancel();
    }

    protected function confirmDelete(): void
    {
        $adns = $this->getNotificationsFromRequest();

        $stream = Streams::ofString(
            $this->ui_renderer->render(
                $this->ui_factory->modal()->interruptive(
                    $this->lng->txt('action_confirm_delete'),
                    $this->lng->txt('action_confirm_delete_msg'),
                    $this->ctrl->getLinkTarget($this, self::CMD_DELETE)
                )->withAffectedItems(
                    array_map(fn(ilADNNotification $adn): Standard => $this->ui_factory->modal()->interruptiveItem()->standard(
                        $adn->getId(),
                        $adn->getTitle()
                    ), $adns)
                )
            )
        );
        $this->http->saveResponse($this->http->response()->withBody($stream));
        $this->http->sendResponse();
        $this->http->close();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function getNotificationFromRequest(): ilADNNotification
    {
        return $this->getNotificationsFromRequest()[0];
    }

    /**
     * @return ilADNNotification[]
     */
    protected function getNotificationsFromRequest(): array
    {
        $query_params = $this->http->request()->getQueryParams(); // aka $_GET
        $name = $this->table->getIdToken()->getName(); // name of the query parameter from the table
        $field_ids = $query_params[$name] ?? []; // array of field ids

        // all objects
        if (($field_ids[0] ?? null) === 'ALL_OBJECTS') {
            return ilADNNotification::get();
        }
        // check ilADNAbstractGUI::IDENTIFIER
        if (($field_id = $this->http->request()->getQueryParams()[ilADNAbstractGUI::IDENTIFIER] ?? false)) {
            return [ilADNNotification::findOrFail((int) $field_id)];
        }

        $return = [];
        foreach ($field_ids as $field_id) {
            $return[] = ilADNNotification::findOrFail((int) $field_id);
        }

        // check interruptive items
        if (($interruptive_items = $this->http->request()->getParsedBody()['interruptive_items'] ?? false)) {
            foreach ($interruptive_items as $interruptive_item) {
                $return[] = ilADNNotification::findOrFail((int) $interruptive_item);
            }
        }

        return $return;
    }

}
