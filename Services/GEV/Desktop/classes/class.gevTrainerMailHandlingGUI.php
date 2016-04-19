<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */#

require_once("Services/Mailing/classes/class.ilMailingGUI.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

/**
 * Mailhandling for trainers
 *
 * @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version	$Id$
 *
 */
class gevTrainerMailHandlingGUI extends ilMailingGUI {

	public function __construct($parent) {
		assert('is_object($parent)');
		assert('array_key_exists("obj_id", $_GET)');

		$this->parent = $parent;
		$this->obj_id = $_GET["obj_id"];
		$this->ref_id = gevObjectUtils::getRefId($this->obj_id);

		parent::__construct($this->obj_id, $this->ref_id, $this->parent);

		$this->addTabs();
		$this->lng->loadLanguageModule("mailing");
	}

	protected function showLog() {
		$this->tabs->activateTab("showMaillog");
		require_once("Services/Mailing/classes/class.ilMailLogTableGUI.php");
		$log_gui = new ilMailLogTableGUI($this->getMailLog(), $this, $this->ctrl->getCmd());
		$this->tpl->setContent($log_gui->getHTML());
	}

	function addTabs() {
		$this->tabs->clearTargets();

		$this->tabs->setBackTarget($this->lng->txt("back")
			, $this->ctrl->getLinkTarget($this->parent)
		);

		$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		$this->tabs->addTab("showMaillog"
			,$this->lng->txt("gev_mail_log")
			, $this->ctrl->getLinkTarget($this, "showLog")
		);

		$this->tabs->addTab("selectMailToMembersRecipients"
			,$this->lng->txt("gev_send_free_text_mail")
			, $this->ctrl->getLinkTarget($this, "selectMailToMembersRecipients")
		);
		$this->ctrl->clearParameters($this);
	}

	/**
	 * @inheritdoc
	 */
	protected function attachmentsSubtabVisible() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function autoMailsSubtabVisible() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function mailToMembersSubtabVisible() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function maillogSubtabVisible() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function getMemberUserIds() {
		return $this->getCourse()->getMembersObject()->getParticipants();
	}

	/**
	 * @inheritdoc
	 */
	protected function initMailAttachments() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
		$this->setMailAttachments(new gevCrsMailAttachments($this->obj_id));
	}

	/**
	 * @inheritdoc
	 */
	protected function initAutoMails() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$this->setAutoMails(new gevCrsAutoMails($this->obj_id));
	}

	/**
	 * @inheritdoc
	 */
	protected function initMailLog() {
		require_once("Services/Mailing/classes/class.ilMailLog.php");

		if ($this->mail_log === null) {
			$this->mail_log = new ilMailLog($this->obj_id);
		}
	}

	protected function getCourse() {
		if ($this->crs === null) {
			$this->crs = new ilObjCourse($this->obj_id, false);
		}

		return $this->crs;
	}

	protected function setSubTabs() {}

	/**
	 * @inheritdoc
	 */
	protected function selectMailToMembersRecipients() {
		$this->tabs->activateTab("selectMailToMembersRecipients");
		$user_ids = $this->getMemberUserIds();
		
		$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		$command_buttons = array( array("showMailToMembersMailInput", $this->lng->txt("continue"))
								);

		$this->ctrl->setParameter($this, "cmd", "selectMailToMembersRecipients");
		$table_gui = $this->getRecipientUserSelectionTable(
							  $this->getUserData($user_ids)
							, $this->lng->txt("select_mail_recipients")
							, $this->ctrl->getFormAction($this)
							, $command_buttons
							);
		
		$this->tpl->setContent($table_gui->getHTML());
		$this->ctrl->clearParameters($this);
	}

	/**
	 * @inheritdoc
	 */
	protected function showMailToMembersMailInput() {
		$recipients = $_POST["recipients"];
		
		if (count($recipients) == 0) {
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->selectMailToMembersRecipients();
			return;
		}

		$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		$form = $this->getMailToMembersForm($recipients);

		$this->tpl->setContent($form->getHTML());
		$this->ctrl->clearParameters($this);
	}
}