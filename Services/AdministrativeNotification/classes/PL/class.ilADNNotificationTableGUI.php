<?php

use ILIAS\UI\Component\MainControls\SystemInfo;

/**
 * Class ilADNNotificationTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotificationTableGUI extends ilTable2GUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

    /**
     * ilADNNotificationTableGUI constructor.
     * @param ilADNNotificationGUI $a_parent_obj
     * @param                      $a_parent_cmd
     */
	public function __construct(ilADNNotificationGUI $a_parent_obj, $a_parent_cmd) {
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();

        $this->setId('msg_msg_table');
        $this->setRowTemplate('Services/AdministrativeNotification/templates/default/tpl.row.html');
        $this->setTitle($this->lng->txt('msg_table_title'));
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        //
        // Columns
		$this->addColumn($this->lng->txt('msg_title'));
		$this->addColumn($this->lng->txt('msg_type'));
		$this->addColumn($this->lng->txt('msg_type_during_event'));
		$this->addColumn($this->lng->txt('msg_event_start', 'event_start_unix'));
		$this->addColumn($this->lng->txt('msg_event_end', 'event_end_unix'));
		$this->addColumn($this->lng->txt('msg_display_start', 'display_start_unix'));
		$this->addColumn($this->lng->txt('msg_display_end', 'display_end_unix'));
		$this->addColumn($this->lng->txt('common_actions'));

		$this->initData();
	}


	protected function initData() {
		$ilADNNotificationList = ilADNNotification::getCollection();
		$ilADNNotificationList->dateFormat();
		$this->setData($ilADNNotificationList->getArray());
	}


	protected function fillRow($a_set) {
		/**
		 * @var ilADNNotification $ilADNNotification
		 */
		$ilADNNotification = ilADNNotification::find($a_set['id']);
		$this->tpl->setVariable('TITLE', $ilADNNotification->getTitle());
		$this->tpl->setVariable('TYPE', $this->lng->txt('msg_type_' . $ilADNNotification->getType()));
		$this->tpl->setVariable('TYPE_DURING_EVENT', $this->lng->txt('msg_type_' . $ilADNNotification->getTypeDuringEvent()));

		if (!$ilADNNotification->getPermanent()) {
			$this->tpl->setVariable('EVENT_START', $a_set['event_start']);
			$this->tpl->setVariable('EVENT_END', $a_set['event_end']);
			$this->tpl->setVariable('DISPLAY_START', $a_set['display_start']);
			$this->tpl->setVariable('DISPLAY_END', $a_set['display_end']);
		}

		$this->ctrl->setParameter($this->parent_obj, ilADNNotificationGUI::IDENTIFIER, $ilADNNotification->getId());
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($this->lng->txt('common_actions'));
		$actions->setId('msg_' . $ilADNNotification->getId());
		$actions->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_EDIT));
		$actions->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_CONFIRM_DELETE));
		if ($ilADNNotification->getDismissable()) {
			$actions->addItem($this->lng->txt('msg_reset_dismiss'), '', $this->ctrl->getLinkTarget($this->parent_obj, ilADNNotificationGUI::CMD_CONFIRM_RESET));
		}
		$this->tpl->setVariable('ACTIONS', $actions->getHTML());
	}
}
