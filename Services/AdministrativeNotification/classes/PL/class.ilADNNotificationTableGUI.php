<?php

use ILIAS\DI\Container;
use ILIAS\Data\Factory;

/**
 * Class ilADNNotificationTableGUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotificationTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var \ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * ilADNNotificationTableGUI constructor.
     * @param ilADNNotificationGUI $a_parent_obj
     * @param                      $a_parent_cmd
     */
    public function __construct(ilADNNotificationGUI $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->data_factory = new Factory();

        $this->setId('msg_msg_table');
        $this->setRowTemplate('Services/AdministrativeNotification/templates/default/tpl.row.html');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        //
        // Columns
        $this->addColumn($this->lng->txt('msg_title'));
        $this->addColumn($this->lng->txt('msg_type'));
        $this->addColumn($this->lng->txt('msg_type_during_event'));
        $this->addColumn($this->lng->txt('msg_event_date_start'));
        $this->addColumn($this->lng->txt('msg_event_date_end'));
        $this->addColumn($this->lng->txt('msg_display_date_start'));
        $this->addColumn($this->lng->txt('msg_display_date_end'));
        $this->addColumn($this->lng->txt('common_actions'));

        $this->initData();
    }

    protected function initData()
    {
        $ilADNNotificationList = ilADNNotification::getCollection();
        $ilADNNotificationList->dateFormat();
        $this->setData($ilADNNotificationList->getArray());
    }

    protected function formatDate(DateTimeImmutable $timestamp) : string
    {
        return $timestamp->format($this->data_factory->dateFormat()->germanLong()->toString() . ' - H:i:s') ?? '';
    }

    protected function fillRow($a_set)
    {
        /**
         * @var ilADNNotification $notification
         */
        $notification = ilADNNotification::find($a_set['id']);
        $this->tpl->setVariable('TITLE', $notification->getTitle());
        $this->tpl->setVariable('TYPE', $this->lng->txt('msg_type_' . $notification->getType()));

        if (!$notification->getPermanent()) {
            $this->tpl->setVariable('TYPE_DURING_EVENT',
                $this->lng->txt('msg_type_' . $notification->getTypeDuringEvent()));
            $this->tpl->setVariable('EVENT_START', $this->formatDate($notification->getEventStart()));
            $this->tpl->setVariable('EVENT_END', $this->formatDate($notification->getEventEnd()));
            $this->tpl->setVariable('DISPLAY_START', $this->formatDate($notification->getDisplayStart()));
            $this->tpl->setVariable('DISPLAY_END', $this->formatDate($notification->getDisplayEnd()));
        }

        $this->ctrl->setParameter($this->parent_obj, ilADNNotificationGUI::IDENTIFIER, $notification->getId());
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->lng->txt('common_actions'));
        $actions->setId('msg_' . $notification->getId());
        $actions->addItem($this->lng->txt('edit'), '',
            $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_EDIT));
        $actions->addItem($this->lng->txt('delete'), '',
            $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_CONFIRM_DELETE));
        if ($notification->getDismissable()) {
            $actions->addItem($this->lng->txt('msg_reset_dismiss'), '',
                $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_CONFIRM_RESET));
        }
        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
    }
}
