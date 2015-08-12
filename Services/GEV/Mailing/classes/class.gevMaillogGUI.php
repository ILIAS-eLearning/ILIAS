<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevMaillogGUI {

	public function __construct($a_parent, $a_obj_id) {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilTabs;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->parent = $a_parent;
		$this->ilTabs = $ilTabs;

		if(isset($_GET["obj_id"])) {
			$this->obj_id = $_GET["obj_id"];
		}

		if(isset($_GET["crs_id"])) {
			$this->obj_id = $_GET["crs_id"];
		}

		$this->tpl->getStandardTemplate();
		$this->lng->loadLanguageModule("mailing");

		$this->setTabs();
		$tpl->setTitle(gevCourseUtils::getInstance($this->obj_id)->getTitle());
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();

		switch($cmd) {
			case "showMaillog":
			case "showLoggedMail":
			case "resendMail":
				$this->$cmd();
		}
	}

	protected function showMaillog() {
		require_once("Services/Mailing/classes/class.ilMailLogTableGUI.php");
		$log_gui = new ilMailLogTableGUI($this->getMailLog(), $this, $this->ctrl->getCmd());

		$this->tpl->setContent($log_gui->getHTML());
	}

	protected function getMailLog() {
		if ($this->mail_log == null) {
			$this->initMailLog();

			if ($this->mail_log == null) {
				throw new Exception("Member mail_log still unitialized after ".
									"call to initMailLog. Did you forget ".
									"to call setMailLog in you implementation ".
									"of initMailLog?");
			}
		}

		return $this->mail_log;
	}

	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs()
	{
		$this->ilTabs->clearTargets();
		
		switch($this->parent) {
			case "iltepgui":
				$back_target = "ilias.php?baseClass=ilTEPGUI";
				break;
			case "mytrainigsapgui":
				$back_target = "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAp";
				break;
		}

		$this->ilTabs->setBackTarget(
			$this->lng->txt("back")
			, $back_target === null ? $this->ctrl->getLinkTarget($this, "returnToParent")
									: $back_target
			);
		
		$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		$this->ilTabs->addTab("maillog",
			$this->lng->txt("gev_mail_log"),
			$this->ctrl->getLinkTarget($this, "showMaillog"));
		$this->ctrl->setParameter($this, "obj_id", null);
		
		$this->ilTabs->activateTab(true);
	}

	protected function initMailLog() {
		require_once("Services/Mailing/classes/class.ilMailLog.php");

		if ($this->mail_log === null) {
			

			$this->mail_log = new ilMailLog($this->obj_id);
		}
	}

	protected function showLoggedMail() {
		if($_GET["mail_id"] === null or !is_numeric($_GET["mail_id"])) {
			$this->ctrl->redirect($this, "showLog");
			exit();
		}

		$mail_id = intval($_GET["mail_id"]);

		$mail = $this->getMailLog()->getEntry($mail_id);

		require_once("Services/Mailing/classes/class.ilMailViewGUI.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$moment = ilDatePresentation::formatDate($mail["moment"], false);

		$count = count($mail["attachments"]);
		foreach ($mail["attachments"] as $key => $attachment) {
			$this->ctrl->setParameter($this, "filename", $attachment["name"]);
			$this->ctrl->setParameter($this, "hash", $attachment["hash"]);
			$mail["attachments"][$key]["link"] = $this->ctrl->getLinkTarget($this, "deliverMailLogAttachment");
			$this->ctrl->clearParametersByClass("vfCrsMailingGUI");
		}
		
		if ($mail["mail_id"] && $mail["recipient_id"]) {
			$this->ctrl->setParameter($this, "log_id", $mail["id"]);
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
			$resend_link = $this->ctrl->getLinkTarget($this, "resendMail");
			$this->ctrl->clearParameters($this);
		}
		else {
			$resend_link = null;
		}
		
		$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		$view_gui = new ilMailViewGUI( $mail["occasion"]." ".($this->lng->txt("mailing_on"))." ".$moment
									 , $this->ctrl->getLinkTarget($this, "showMaillog")
									 , $mail["subject"]
									 , $mail["message"]
									 , null
									 , null
									 , null
									 , $mail["attachments"]
									 , $mail["to"]
									 , $mail["cc"]
									 , $mail["bcc"]
									 , $resend_link
									 );
		
		$this->ctrl->setParameter($this, "obj_id", null);
		
		$this->tpl->setContent($view_gui->getHTML());
	}
	
	protected function resendMail() {
		if($_GET["log_id"] === null or !is_numeric($_GET["log_id"])) {
			$this->ctrl->redirect($this, "showLog");
			exit();
		}
		
		$mail_id = intval($_GET["log_id"]);
		$mail = $this->getMailLog()->getEntry($mail_id);
		
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$auto_mails = new gevCrsAutoMails($this->obj_id);
		$res = $auto_mails->send($mail["mail_id"], array($mail["recipient_id"]), $auto_mails->getUserOccasion());
		
		if ($res === true) {
			ilUtil::sendSuccess($this->lng->txt("auto_mail_send_successfully"));
		}
		else {
			ilUtil::sendFailure($res);
		}
		
		$this->showMaillog();
	}
}

?>
