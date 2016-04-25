<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMailingGUI.
*
* Use it to build a Mailing tab into your ILIAS-Object-GUI. To make
* the GUI customizable for different objects there are some abstract
* methods which need to be implemented for object-specific versions
* of that GUI. All abstract methods that need to be reimplemented are
* at the top of the class definition.
*
* The Mailing-Tab has four subtabs: attachments, automails, mail to
* members, mail log, that could be switched on and off by a subclass.
* It is visible for users, who have a write permission at the object
* the GUI is belonging to. Specific permissions for subtabs can be
* implemented.
*
* The mail to member subtab is not implemented in the gui, since there
* are already GUIs in ILIAS implementing a generic mailing functionality.
*
* The GUI uses specific objects to retreive information for the different
* subtabs. To make it customizable, the creation of that objects is delegated
* to the final subclasses of the GUI.
*
* TODO: The sending mechanism could be unified between this GUI class and the
*       ilAutoMail-class.
*
*       The setting of a logger is not handled consistently. It feels like there
*       is some functionality in this gui class related to sending of mail to
*       members, that should be put elsewhere, maybe in a mail-to-members-class.
*       The gui class itself should not set the logger, since it's only a view
*       and should therefore not controll the functionality of mail-to-members.
*       -> Rethink, how the mail to members functionality should be integrated.
*
*       A mail is represented by an array atm. It would be nicer to replace that 
*       by some object, that would make it possible e.g. perform checks on creation
*       of that object and use type hinting. 
*
* @author Richard Klees <richard.klees@concepts-and-training>
* @version $Id$
*/

require_once("Services/Mailing/classes/class.ilMailAttachments.php");
require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

abstract class ilMailingGUI {
	// Those define, weather subtabs are visible. Sub specific RBAC checks
	// could be put here.

	/**
	 * Should the attachment subtab be visible to the current user?
	 *
	 * @return bool Is the tab visible?
	 */
	abstract protected function attachmentsSubtabVisible();

	/**
	 * Should the automail subtab be visible to the current user?
	 *
	 * @return bool Is the tab visible?
	 */
	abstract protected function autoMailsSubtabVisible();

	/**
	 * Should the mail to members subtab be visible to the current user?
	 *
	 * @return bool Is the tab visible?
	 */
	abstract protected function mailToMembersSubtabVisible();

	/**
	 * Should the maillog subtab be visible to the current user?
	 *
	 * @return bool Is the tab visible?
	 */
	abstract protected function maillogSubtabVisible();


	/**
	 * Get user ids of all users to be considered as members.
	 */
	abstract protected function getMemberUserIds();


	/**
	 * Create an attachments object derived from ilMailAttachments and pass
	 * it to setMailAttachments.
	 *
	 * Will only be called once per run.
	 */
	abstract protected function initMailAttachments();


	/**
	 * Create an automail object derived from ilAutoMails and pass
	 * it to setAutoMails.
	 *
	 * Will only be called once per run.
	 */
	abstract protected function initAutoMails();

	/**
	 * Create an maillog object derived from ilMailLog and pass
	 * it to setMailLog.
	 *
	 * Will only be called once per run.
	 */
	abstract protected function initMailLog();

	protected $obj_id;


	// ilMailAttachments-object

	private $attachments;

	/**
	 * Set the ilMailAttachments-object for the gui.
	 *
	 * @param ilMailAttachments $a_mail_attachments The object to be used.
	 */
	protected function setMailAttachments(ilMailAttachments $a_mail_attachments) {
		$this->attachments = $a_mail_attachments;
	}

	/**
	 * Get the ilMailAttachments-object of the gui.
	 *
	 * Call initMailAttachments if not done yet.
	 *
	 * @return ilMailAttachments The ilMailAttachments object of the GUI.
	 */
	protected function getMailAttachments() {
		if ($this->attachments == null) {
			$this->initMailAttachments();

			if ($this->attachments == null) {
				throw new Exception("Member attachments still unitialized after ".
									"call to initMailAttachments. Did you forget ".
									"to call setMailAttachments in you implementation ".
									"of initMailAttachments?");
			}
		}

		return $this->attachments;
	}


	// ilAutoMails-object

	private $auto_mails;

	/**
	 * Set the ilAutoMails-object for the gui.
	 *
	 * @param ilAutoMails $a_mail_attachments The object to be used.
	 */
	protected function setAutoMails(ilAutoMails $a_auto_mails) {
		$this->auto_mails = $a_auto_mails;
	}

	/**
	 * Get the ilAutoMails-object of the gui.
	 *
	 * Call initAutoMails if not done yet.
	 *
	 * @return ilMailAttachments The ilMailAttachments object of the GUI.
	 */
	protected function getAutoMails() {
		if ($this->auto_mails == null) {
			$this->initAutoMails();

			if ($this->auto_mails == null) {
				throw new Exception("Member auto_mails still unitialized after ".
									"call to initAutoMails. Did you forget ".
									"to call setAutoMails in you implementation ".
									"of initAutoMails");
			}
		}

		return $this->auto_mails;
	}

	// ilMailLog-object

	protected $mail_log;

	/**
	 * Set the ilMailLog-object for the gui.
	 *
	 * @param ilMailLog $a_mail_log The object to be used.
	 */
	protected function setMailLog(ilMailLog $a_mail_log) {
		$this->mail_log = $a_mail_log;
	}

	/**
	 * Get the ilAutoMails-object of the gui.
	 *
	 * Call initAutoMails if not done yet.
	 *
	 * @return ilMailAttachments The ilMailAttachments object of the GUI.
	 */
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

	// Used to cache the upload form.
	private $attachment_form;

	/**
	 * Construct a new MailingGUI-object.
	 *
	 * @param integer $a_obj_id The id of the object the MailingGUI acts on.
	 * @param integer $a_ref_id The ref id of the object the MailingGUI acts on.
 	 * @param any $a_parent_gui The GUI that uses this GUI.
	 */
	public function __construct($a_obj_id, $a_ref_id, $a_parent_gui) {
		$this->obj_id = $a_obj_id;
		$this->ref_id = $a_ref_id;
		$this->parent_gui = &$a_parent_gui;

		global $ilCtrl, $lng, $ilTabs, $tpl, $ilToolbar, $ilAccess, $ilUser;

		$this->ctrl = &$ilCtrl;
		$this->lng = &$lng;
		$this->tabs = &$ilTabs;
		$this->tpl = &$tpl;
		$this->toolbar = &$ilToolbar;
		$this->access = &$ilAccess;
		$this->user = &$ilUser;
		
		$this->lng->loadLanguageModule("mailing");

		$this->auto_mails = null;
		$this->mail_log = null;
		$this->attachments = null;

		$this->attachment_form = null;
	}


	// DISPATCHING AND SUBTABS

	/**
	 * Check weather current user has access to any of the subtabs and redirect
	 * to parent_gui if not.
	 *
	 * @return bool Does the user has access?
	 */
	protected function checkAccess() {
		if (!$this->access->checkAccess("write", "", $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt("msg_no_perm_write"), true);
			$this->ctrl->redirect($this->parent_gui);
		}
	}

	/**
	 * Execute command found in ilCtrl.
	 *
	 * Default command is retreived via getDefaultCommand.
	 * To implement custom commands for an object specific
	 * GUI use executeCustomCommand which will be called when
	 * command is unknown to the MailingGUI.
	 */
	final public function executeCommand() {
		$this->checkAccess();

		$this->setSubTabs();
		$cmd = $this->ctrl->getCmd();

		if ($cmd === "") {
			$cmd = $this->getDefaultCommand();
		}

		$this->activateSubTab($cmd);
		switch($cmd) {
			case "showAttachments":
			case "confirmRemoveAttachment":
			case "removeAttachment":
			case "uploadAttachment":
			case "confirmOverrideAttachment":
			case "deliverAttachment":

			case "showAutoMails":
			case "previewAutoMail":
			case "sendAutoMail":
			case "showAutoMailSendConfirmationUsers":
			case "showAutoMailSendConfirmationAddresses":
			case "completeSendAutoMail":
			case "deliverAutoMailAttachment":

			case "selectMailToMembersRecipients":
			case "showMailToMembersMailInput":
			case "sendMailToMembers":

			case "showLog":
			case "showLoggedMail":
			case "deliverMailLogAttachment":
			case "resendMail":
				$this->$cmd();
				break;

			default:
				$this->executeCustomCommand($cmd);
		}
	}

	/**
	 * Reimplement this in derived class to execute custom
	 * commands.
	 *
	 * ATM does nothing.
	 *
	 * @param string @a_cmd The command that should be executed.
	 */
	protected function executeCustomCommand($a_cmd) {
		throw new Exception("Unknown command: ".$a_cmd);
	}

	/**
	 * Get the command that should be used per default.
	 *
	 * @return string The default command.
	 */
	public function getDefaultCommand() {
		return "showLog";
	}

	/**
	 * Initialize the subtabs visible for the current user.
	 */
	protected function setSubTabs() {

		if ($this->attachmentsSubtabVisible()) {
			$this->tabs->addSubTab("attachments",
										$this->lng->txt("attachments"),
										$this->ctrl->getLinkTarget($this, "showAttachments"));
		}

		if ($this->autoMailsSubtabVisible()) {
			$this->tabs->addSubTab("auto_mails",
										  $this->lng->txt("auto_mails"),
										  $this->ctrl->getLinkTarget($this, "showAutoMails"));
		}

		if ($this->mailToMembersSubtabVisible()) {
			$this->tabs->addSubTab("mail_to_members",
										$this->lng->txt("mail_to_members"),
										$this->ctrl->getLinkTarget($this, "selectMailToMembersRecipients"));
		}

		if ($this->maillogSubtabVisible()) {
			$this->tabs->addSubTab("log",
										$this->lng->txt("mail_log"),
										$this->ctrl->getLinkTarget($this, "showLog"));
		}
	}

	/**
	 * Activate appropriate subtab for the given command.
	 *
	 * Call activateCustomSubTab if command is not known by
	 * ilMailingGUI.
	 *
	 * @param string $a_cmd The command to determine subtab for.
	 */
	final protected function activateSubTab($a_cmd) {
		switch ($a_cmd) {
			case "showAttachments":
			case "downloadAttachment":
			case "confirmRemoveAttachment":
			case "removeAttachment":
			case "uploadAttachment":
			case "confirmOverrideAttachment":
			case "deliverAttachment":
				$this->tabs->setSubTabActive("attachments");
				break;

			case "showAutoMails":
			case "previewAutoMail":
			case "sendAutoMail":
			case "showAutoMailSendConfirmationUsers":
			case "showAutoMailSendConfirmationAddresses":
			case "completeSendAutoMail":
			case "deliverAutoMailAttachment":
				$this->tabs->setSubTabActive("auto_mails");
				break;

			case "selectMailToMembersRecipients":
			case "showMailToMembersMailInput":
				$this->tabs->setSubTabActive("mail_to_members");
				break;
				
			// This goes here since mail log is shown after mail to 
			// members is send.
			case "sendMailToMembers":

			case "showLog":
			case "showLoggedMail":
			case "deliverMailLogAttachment":
			case "resendMail":
				$this->tabs->setSubTabActive("log");
				break;

			default:
				$this->activateCustomSubTab($a_cmd);
		}
	}

	/**
	 * Activate a custom subtab.
	 *
	 * ATM does nothing.
	 *
	 * @param string $a_cmd The command to activate the subtab for.
	 */
	protected function activateCustomSubTab($a_cmd) {
		throw new Exception("Unknown command: ".$a_cmd);
	}


	// ATTACHMENTS

	/**
	 * Build the form used to upload an attachment and attach it to
	 * the toolbar. Will only do that once.
	 *
	 * @return ilFileInputGUI The upload form.
	 */
	protected function createAttachmentUploadForm() {
		if ($this->attachment_form !== null) {
			return $this->attachement_form;
		}

		require_once("Services/Form/classes/class.ilFileInputGUI.php");

		$file_upload_form = new ilFileInputGUI();
		$file_upload_form->setPostVar("attachment_upload");
		$this->toolbar->setFormAction($this->ctrl->getFormAction($this), "true");
		$this->toolbar->addInputItem($file_upload_form);
		$this->toolbar->addFormButton($this->lng->txt("upload"), "uploadAttachment");

		$this->attachment_form = $file_upload_form;

		return $file_upload_form;
	}

	/**
	 * Show failure message and then call showAttachments.
	 *
	 * @param string $a_msg The message to show.
	 */
	protected function showAttachmentFailure($a_msg) {
			ilUtil::sendFailure($a_msg);
			$this->showAttachments();
	}

	/**
	 * Show all attachments in a table.
	 */
	protected function showAttachments() {
		require_once("Services/Mailing/classes/class.ilMailAttachmentsTableGUI.php");

		$available = $this->getMailAttachments()->getInfoList();

		$this->createAttachmentUploadForm();

		$table = new ilMailAttachmentsTableGUI($this, $this->ctrl->getCmd());
		$table->setData($available);

		$this->tpl->setContent($table->getHtml());
	}

	// Removal workflow

	/**
	 * Show confirmation screen for attachment removal.
	 *
	 * First step in the removal workflow.
	 */
	protected function confirmRemoveAttachment() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$filename = $_GET["filename"];

		if ($this->getMailAttachments()->isLocked($filename)) {
			$this->showAttachmentFailure($this->getAttachmentIsLockedFailure($filename));
			return;
		}

		$this->ctrl->saveParameter($this, "filename", $filename);

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt("confirm_delete_attachment"));
		$conf->addItem("filename", $filename, $filename);
		$conf->setConfirm($this->lng->txt("delete"), 'removeAttachment');
		$conf->setCancel($this->lng->txt("cancel"), "showAttachments");

		$this->tpl->setContent($conf->getHTML());

		$this->ctrl->clearParameters($this);
	}

	/**
	 * Remove the attachment and show other attachments.
	 *
	 * Second step on removal workflow.
	 */
	protected function removeAttachment() {
		$filename = $_GET["filename"];

		// Check this again, since removeAttachment will throw
		// if that fails.
		// That would not look nice to the user...
		if($this->getMailAttachments()->isLocked($filename)) {
			$this->showAttachmentFailure($this->getAttachmentIsLockedFailure($filename));
			return;
		}

		$this->getMailAttachments()->removeAttachment($filename);
		ilUtil::sendSuccess(sprintf($this->lng->txt("remove_attachment_success"), $filename));

		$this->showAttachments();
	}

	/**
	 * Get a failure message that attachment could not been removed because it is
	 * locked.
	 *
	 * This is meant to be overridden in a subclass to show a custom message for
	 * that case.
	 *
	 * @param string $a_filename Name of the attachment that could not
	 *                           be removed.
	 */
	protected function getAttachmentIsLockedFailure($a_filename) {
		return sprintf($this->lng->txt("attachment_remove_failure"), $a_filename);
	}

	// upload workflow

	/**
	 * Grab file from attachment upload form and either add it directly to the
	 * available attachments or prompt for confirmation of override if there
	 * already is a file with the same name.
	 */
	protected function uploadAttachment() {
		$upload_form = $this->createAttachmentUploadForm();

		if (!$upload_form->checkInput()) {
			$this->showAttachmentFailure($this->lng->txt("upload_attachment_failure"));
			return;
		}

		$file = $_FILES["attachment_upload"];

		if ($file["error"]) {
			$this->showAttachmentFailure($this->lng->txt("upload_attachment_failure"));
			return;
		}

		if ($file["size"] <= 0) {
			$this->showAttachmentFailure($this->lng->txt("upload_attachment_no_file"));
			return;
		}

		// Is this an override?
		if (in_array($file["name"], $this->getMailAttachments()->getList())) {
			$this->showOverrideAttachmentConfirmation($file);
		}
		else {
			$this->addUploadedAttachment($file);
		}
	}

	/**
	 * Add $a_file to the mail attachments. Then showAttachments.
	 *
	 * @param array $a_file Array containing file info.
	 */
	protected function addUploadedAttachment($a_file) {
		$this->getMailAttachments()->addAttachment($a_file["name"], $a_file["tmp_name"]);
		ilUtil::sendSuccess(sprintf($this->lng->txt("upload_attachment_success"), $a_file["name"]));
		$this->showAttachments();
	}

	/**
	 * Show confirmation screen for overriding attachment at seminar.
	 *
	 * @param array $a_file Array containing file info.
	 */
	protected function showOverrideAttachmentConfirmation($a_file) {
		require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");

		$this->toolbar->setHidden(true);

		// copy file to ilias-temp-directory to keep it available between
		// access.
		$tmp_name = ilUtil::ilTempnam();
		copy($a_file["tmp_name"], $tmp_name);
		$a_file["tmp_name"] = $tmp_name;

		$_SESSION["att_uploaded_file"] = $a_file;

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this, "uploadAttachment"));
		$conf->setHeaderText($this->lng->txt("confirm_override_attachment"));

		$conf->addItem("filename", $a_file["name"], $a_file["name"]);

		$conf->setConfirm($this->lng->txt("confirm"), 'confirmOverrideAttachment');
		$conf->setCancel($this->lng->txt("cancel"), "showAttachments");

		$this->tpl->setContent($conf->getHTML());
	}

	/**
	 * Confirm overriding of attachment. Adds the attachments, cleans up and then
	 * shows attachments again.
	 */
	protected function confirmOverrideAttachment() {
		$file = $_SESSION["att_uploaded_file"];
		$this->addUploadedAttachment($file);

		unset($_SESSION["att_uploaded_file"]);
		unlink($file["tmp_name"]);
	}

	// delivery

	/**
	 * Deliver attachment (ha!)
	 */
	protected function deliverAttachment() {
		$filename = $_GET["filename"];

		if (!$this->getMailAttachments()->isAttachment($filename)) {
			$this->showAttachmentFailure(sprintf($this->lng->txt("is_unknown_attachment"), $filename));
			return;
		}

		$this->deliverAttachmentFile($filename, $this->getMailAttachments()->getPathTo($filename));
	}


	/**
	 * Deliver file with correct mimetype (if that could be determined).
	 *
	 * ATTENTION: Exits after delivery.
	 *
	 * @param string $a_name The name for the delivery of the file.
	 * @param string $a_path The complete path to the file that should be
	 * 						 delivered.
	 */
	protected function deliverAttachmentFile($a_name, $a_path) {
		require_once("Services/Utilities/classes/class.ilFileUtils.php");

		$mimetype = ilFileUtils::_lookupMimeType($a_path);
		ilUtil::deliverFile($a_path, $a_name, $mimetype, false, false, true);
	}


	// AUTOMAILS

	/**
	 * Show failure message and then call showAutoMails.
	 *
	 * @param string $a_msg The message to show.
	 */
	protected function showAutoMailsFailure($a_msg) {
		ilUtil::sendFailure($a_msg);
		$this->showAutoMails();
	}

	/**
	 * Show table if automails.
	 */
	protected function showAutoMails() {
		require_once("Services/Mailing/classes/class.ilAutoMailsTableGUI.php");

		$table = new ilAutoMailsTableGUI($this, "showAutoMails",
										 $this->getAutoMails()->getTitle(),
										 $this->getAutoMails()->getSubtitle());

		$table->setData($this->getAutoMails()->getAllInfo());

		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Grab auto_mail_id from GET and redirect to showAutoMails if theres
	 * none.
	 *
	 * @return string The id of the automail from GET.
	 */
	protected function getAutoMailIdFromGET() {
		if($_GET["auto_mail_id"] === null) {
			$this->ctrl->redirect($this, "showAutoMails");
			exit();
		}

		return $_GET["auto_mail_id"];
	}

	/**
	 * Show a preview of the automail with id from get.
	 */
	protected function previewAutoMail() {
		$mail_id = $this->getAutoMailIdFromGET();

		$mail = $this->getAutoMails()->getPreview($mail_id);

		if ($mail === null) {
			ilUtil::sendFailure($this->lng->txt("no_mail_available"));
			return $this->showAutoMails();
		}
		
		foreach($mail["attachments"] as $key => $attachment) {
			$this->ctrl->setParameter($this, "auto_mail_id", $mail_id);
			$this->ctrl->setParameter($this, "filename", $attachment["name"]);
			$link = $this->ctrl->getLinkTarget($this, "deliverAutoMailAttachment");
			$this->ctrl->clearParameters($this);
			$mail["attachments"][$key]["link"] = $link;
		}

		require_once("Services/Mailing/classes/class.ilMailViewGUI.php");

		$is_html_message = strlen($mail["message_html"]) > 0;

		$view_gui = new ilMailViewGUI( $this->lng->txt("preview").": ".$mail["title"]
									 , $this->ctrl->getLinkTarget($this, "showAutoMails")
									 , $mail["subject"]
									 , $is_html_message ? $mail["message_html"] : $mail["message_plain"]
									 , $is_html_message ? $mail["frame_html"] : $mail["frame_plain"]
									 , $is_html_message ? $mail["image_path"] : null
									 , $is_html_message ? $mail["image_style"] : null
									 , $mail["attachments"]
									 );

		$this->tpl->setContent($view_gui->getHTML());
	}

	/**
	 * Deliver an attachment of the automail with id found in GET.
	 */
	protected function deliverAutoMailAttachment() {
		$mail_id = $this->getAutoMailIdFromGET();
		$filename = $_GET["filename"];

		$path = $this->getAutoMails()->getAttachmentPath($mail_id, $filename);
		$this->deliverAttachmentFile($filename, $path);
	}

	// workflow for sending automails.

	/**
	 * Send the automail with id found in GET.
	 *
	 * Branches depending on the flag "users_only" of automail to
	 * sendAutoMailUsers or sendAutoMailAddresses.
	 *
	 * Step 1 of automail sending workflow.
	 */
	protected function sendAutoMail() {
		$mail_id = $this->getAutoMailIdFromGET();

		$info = $this->getAutoMails()->getInfo($mail_id);

		if ($info["users_only"]) {
			$this->sendAutoMailUsers($mail_id);
		}
		else {
			$this->sendAutoMailAddresses($mail_id);
		}
	}

	/**
	 * Show user selection if there's more than one designated recipient,
	 * show confirmation if there's one recipient, show error message if
	 * there's no recipient.
	 *
	 * Step 2 of automail sending workflow.
	 */
	protected function sendAutoMailUsers($a_mail_id) {
		$users = $this->getAutoMails()->getRecipientUserIDs($a_mail_id);
		
		$cnt = count($users);

		if ($cnt == 0) {
			$this->showAutoMailsFailure($this->lng->txt("no_recipients"));
			return;
		}
		else if($cnt > 1) {
			$this->showRecipientUserSelection($users, $a_mail_id);
		}
		else {
			$this->showAutoMailSendConfirmationUsers($users);
		}
	}

	/**
	 * Show address selection if there's more than one designated recipient,
	 * show confirmation if there's one recipient, show error message if
	 * there's no recipient.
	 *
	 * Step 2 of automail sending workflow.
	 */
	protected function sendAutoMailAddresses($a_mail_id) {
		$addresses = $this->getAutoMails()->getRecipientAddresses($a_mail_id);

		$cnt = count($addresses);

		if ($cnt ==0) {
			ilUtil::sendFailure($this->lng->txt("no_recipients"));
			return $this->showAutoMails();
		}
		else if($cnt > 1) {
			$this->showRecipientAddressSelection($addresses, $a_mail_id);
		}
		else {
			$this->showAutoMailSendConfirmationAddresses($addresses);
		}
	}

	/**
	 * Helper for showRecipientUserSelection to transform user id to
	 * displayable record.
	 *
	 * @param Array $a_users Array with user ids to get records for.
	 * @return Array The array with displayable info about user.
	 */
	protected function getUserData($a_users) {
		$data = array();

		foreach ($a_users as $id) {
			$name = ilObjUser::_lookupName($id);

			$data[] = array( "id" => $id
						   , "lastname" => $name["lastname"]
						   , "firstname" => $name["firstname"]
						   , "email" => ilObjUser::_lookupEmail($id)
						   );
		}

		return $data;
	}

	/**
	 * Show Table to select users as recipients for automail.
	 *
	 * Step 2 of automail sending workflow.
	 */
	protected function showRecipientUserSelection($a_users, $a_mail_id) {
		$mail_info = $this->getAutoMails()->getInfo($a_mail_id);
		
		$this->ctrl->setParameter($this, "auto_mail_id", $a_mail_id);
		$form_action = $this->ctrl->getFormAction($this);
		$this->ctrl->clearParameters($this);

		$command_buttons = array( array("showAutoMailSendConfirmationUsers", $this->lng->txt("send"))
								, array("showAutoMails", $this->lng->txt("cancel"))
								);

		$table_gui = $this->getRecipientUserSelectionTable(
								  $this->getUserData($a_users)
								, sprintf($this->lng->txt("select_mail_recipients_for_auto_mail"), $mail_info["title"])
								, $form_action
								, $command_buttons
								);

		//gev-patch start
		$this->ctrl->setParameter($this, "auto_mail_id", $a_mail_id);
		$this->ctrl->setParameter($this, "cmd", "sendAutoMail");

		$this->tpl->setContent($table_gui->getHTML());

		$this->ctrl->clearParameters($this);
		//gev-patch end
	}
	
	/**
	 * Get a table to select users as recipients for an automail. You will
	 * need to set the form action, command buttons and title elsewhere to make
	 * the table reusable.
	 *
	 * @param Array $a_user_data The data to be displayed about the users.
	 * @param string $a_title The title to set.
	 * @param string $a_form_action The action to set.
	 * @param Array $a_command_buttons Every entry should contain array with fields
	 *								   command and text.
	 * @return ilTable2GUI The table to be displayed.
	 */
	protected function getRecipientUserSelectionTable($a_user_data, $a_title, $a_form_action, $a_command_buttons) {
		require_once("Services/Table/classes/class.ilTable2GUI.php");
		$table_gui = new ilTable2GUI($this);
		$table_gui->setFormName("recipients");
		$table_gui->setFormAction($a_form_action);

		$table_gui->addColumn('');
		$table_gui->addColumn($this->lng->txt("lastname"), 'lastname');
		$table_gui->addColumn($this->lng->txt("firstname"), 'firstname');
		$table_gui->addColumn($this->lng->txt("email"), 'email');
		$table_gui->setSelectAllCheckbox("recipients");
		$table_gui->setRowTemplate("tpl.recipient_user_selection_table_row.html", "Services/Mailing");

		foreach ($a_command_buttons as $button) {
			$table_gui->addCommandButton($button[0], $button[1]);
		}
		
		$table_gui->setTitle($a_title);

		$table_gui->setData($a_user_data);
		$table_gui->setLimit(1000000);

		return $table_gui;
	}

	/**
	 * Show Table to select addresses as recipients for automail.
	 *
	 * Step 2 of automail sending workflow.
	 */
	protected function showRecipientAddressSelection($a_addresses, $a_mail_id) {
		$mail_info = $this->getAutoMails()->getInfo($a_mail_id);

		$count = count($a_addresses);

		$addresses = array();

		foreach ($a_addresses as $address) {
			$address["key"] = $address["name"]."%%%".$address["email"];
			$addresses[] = $address;
		}

		$table_gui = $this->getRecipientAddressSelectionTable($mail_info, $addresses);

		$this->tpl->setContent($table_gui->getHTML());
	}
	
	/**
	 * Get the table to select addresses as recipients for an automail.
	 * 
	 * @param Array $a_mail_info Info about the automail to send.
	 * @param Array $a_addresses Adresses and Names for the selection.
	 * @return ilTable2GUI The table to be displayed for selection.
	 */
	protected function getRecipientAddressSelectionTable($a_mail_info, $a_addresses) {
		require_once("Services/Table/classes/class.ilTable2GUI.php");
		
		$table_gui = new ilTable2GUI($this);

		$table_gui->setFormName("recipients");
		$this->ctrl->setParameter($this, "auto_mail_id", $a_mail_info["id"]);
		$table_gui->setFormAction($this->ctrl->getFormAction($this));
		$this->ctrl->clearParameters($this);
		$table_gui->addColumn('');
		$table_gui->addColumn($this->lng->txt("name"), "name");
		$table_gui->addColumn($this->lng->txt("email"), 'email');
		$table_gui->setSelectAllCheckbox("recipients");
		$table_gui->setRowTemplate("tpl.recipient_address_selection_table_row.html", "Services/Mailing");
		$table_gui->addCommandButton("showAutoMailSendConfirmationAddresses", $this->lng->txt("send"));
		$table_gui->setTitle(sprintf($this->lng->txt("select_mail_recipients_for_auto_mail"), $a_mail_info["title"]));

		$table_gui->setData($a_addresses);
		
		return $table_gui;
	}

	/**
	 * Show confirmation for selected user ids as recipients for automail.
	 *
	 * Transforms $a_recipients (or POST) input and then calls generic
	 * showAutoMailSendConfirmation.
	 *
	 * Step 3 of automail sending workflow.
	 *
	 * @param Array $a_recipients A list of user_ids selected as recipients.
	 *							  If thats set to null takes POST["recipients"]
	 *							  instead.
	 */
	protected function showAutoMailSendConfirmationUsers($a_recipients = null) {
		$mail_id = $this->getAutoMailIdFromGET();

		if ($a_recipients === null) {
			$a_recipients = $_POST["recipients"];
		}

		if (count($a_recipients) == 0) {
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$users = $this->getAutoMails()->getRecipientUserIDs($mail_id);
			$this->showRecipientUserSelection($users, $mail_id);
			return;
		}

		$user_data = $this->getUserData($a_recipients);
		$recipients = array();

		foreach ($user_data as $user) {
			$recipients[] = array( "presentation" => sprintf("%s %s &lt;%s&gt;"
															, $user["firstname"]
															, $user["lastname"]
															, $user["email"])
								 , "user_id" => $user["id"]
								 , "email" => ""
								 , "name" => ""
								 );
		}

		$this->showAutoMailConfirmation($mail_id, $recipients);
	}

	/**
	 * Show confirmation for selected addresses as recipients for automail.
	 *
	 * Transforms $a_recipients (or POST) input and then calls generic
	 * showAutoMailSendConfirmation.
	 *
	 * Step 3 of automail sending workflow.
	 *
	 * @param Array $a_recipients A list of addresses selected as recipients.
	 *							  If thats set to null takes POST["recipients"]
	 *							  instead.
	 */
	protected function showAutoMailSendConfirmationAddresses($a_recipients = null) {
		$mail_id = $this->getAutoMailIdFromGET();

		if ($a_recipients === null) {
			$a_recipients = array();
			foreach ($_POST["recipients"] as $rec) {
				$spl = explode("%%%", $rec);
				$a_recipients[] = array( "name" => $spl[0]
									   , "email" => $spl[1]);
			}

		}

		if (count($a_recipients) == 0) {
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$users = $this->getAutoMails()->getRecipientAddresses($mail_id);
			$this->showRecipientAddressSelection($users, $mail_id);
			return;
		}

		$recipients = array();

		foreach ($a_recipients as $rec) {
			$recipients[] = array( "presentation" => sprintf("%s &lt;%s&gt;", $rec["name"], $rec["email"])
								 , "user_id" => ""
								 , "email" => $rec["email"]
								 , "name" => $rec["name"]
								 );
		}

		$this->showAutoMailConfirmation($mail_id, $recipients);
	}

	/**
	 * Get an Confirmation message for manual sending of auto message.
	 *
	 * Depends on the fields last_send and scheduled_for.
	 *
	 * @param Array $a_auto_mail_info Info array of the auto mail.
	 * @return string The confirmation message.
	 */
	protected function getAutoMailConfirmationMessage($a_auto_mail_info) {
		if ($a_auto_mail_info["last_send"] === null) {
			if($a_auto_mail_info["scheduled_for"]) {
				return sprintf( $this->lng->txt("auto_mail_confirmation_not_send_scheduled")
							  , $a_auto_mail_info["title"]
							  , ilDatePresentation::formatDate($a_auto_mail_info["scheduled_for"])
							  );
			}
			else {
				return sprintf($this->lng->txt("auto_mail_confirmation_not_send_maybe_later"), $a_auto_mail_info["title"]);
			}
		}

		return sprintf( $this->lng->txt("auto_mail_confirmation_already_send")
					  , $a_auto_mail_info["title"]
					  , ilDatePresentation::formatDate($a_auto_mail_info["last_send"])
					  );
	}

	/**
	 * Show generic confirmation for selected addresses or user_ids as recipients
	 * for automail.
	 *
	 * Shows an appropriate warning depending on weather the auto mail was already
	 * send or will be send.
	 *
	 * Step 3 of automail sending workflow.
	 *
	 * @param string $a_mail_id The id of the auto mail to send.
	 * @param Array $a_recipients A List of Arrays with keys presentation and address.
	 *							  presentation is the string to be put in ConfirmationGUI,
	 *							  address is either a user_id or an address.
	 */
	protected function showAutoMailConfirmation($a_mail_id, $a_recipients) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");

		$info = $this->getAutoMails()->getInfo($a_mail_id);

		$gui = new ilConfirmationGUI();
		$this->ctrl->setParameter($this, "auto_mail_id", $a_mail_id);
		$gui->setFormAction($this->ctrl->getFormAction($this, "completeSendAutoMail"));
		$this->ctrl->clearParameters($this);
		$gui->setHeaderText($this->getAutoMailConfirmationMessage($info));
		$gui->setCancel($this->lng->txt("cancel"), "showAutoMails");
		$gui->setConfirm($this->lng->txt("send"), "completeSendAutoMail");

		foreach ($a_recipients as $r) {
			// TODO: Remove this ugly piece of hack.
			$gui->addItem("recipients[]", $r["user_id"]."%%".$r["name"]."%%".$r["email"], $r["presentation"]);
		}

		$this->tpl->setContent($gui->getHTML());
	}

	/**
	 * Complete sending of auto_mail.
	 *
	 * Get id of auto mail and addresses of recipients from POST. Shows automails
	 * afterwards.
	 */
	protected function completeSendAutoMail() {
		$mail_id = $this->getAutoMailIdFromGET();

		$addresses = $_POST["recipients"];

		$_addresses = array();

		foreach ($addresses as $address) {
			// TODO: Remove this ugly piece of hack.
			$spl = explode("%%", $address);
			if ($spl[0]) {
				$_addresses[] = $spl[0];
			}
			else {
				$_addresses[] = array( "name" => $spl[1]
									 , "email" => $spl[2]
									 );
			}
		}

		$res = $this->getAutoMails()->send($mail_id, $_addresses, $this->getAutoMails()->getUserOccasion());

		if ($res === true) {
			ilUtil::sendSuccess($this->lng->txt("auto_mail_send_successfully"));
		}
		else {
			ilUtil::sendFailure($res);
		}

		$this->showAutoMails();
	}


	// MAIL TO MEMBERS

	/**
	 * Show a table to select recipients for Mail.
	 *
	 * Step 1 of mail to members workflow.
	 */
	protected function selectMailToMembersRecipients() {
		$user_ids = $this->getMemberUserIds();
		
		
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
	}
	
	/**
	 * Show a form to input the mails to members.
	 * 
	 * Step 2 of mail to members workflow.
	 */
	protected function showMailToMembersMailInput() {
		$recipients = $_POST["recipients"];
		
		if (count($recipients) == 0) {
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->selectMailToMembersRecipients();
			return;
		}

		$form = $this->getMailToMembersForm($recipients);

		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Send mail to members and show the mail log afterwards.
	 * 
	 * Step 3 if mail to members workflow.
	 */
	protected function sendMailToMembers() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		
		$recipients = $_POST["recipients"];
		if (!$recipients) {
			$this->selectMailToMembersRecipients();
			return;
		}
		
		$form = $this->getMailToMembersForm($recipients);
		
		$form->setValuesByPost();
		
		if (!$form->checkInput()) {
			$this->tpl->setContent($form->getHTML());
			return;
		}

		$this->send( $this->user->getId()
				   , $form->getInput("recipients")
				   , $form->getInput("subject")
				   , $form->getInput("message")
				   , $form->getInput("attachments")
				   );

		ilUtil::sendSuccess($this->lng->txt("mail_to_members_send_successfully"));
		$this->showLog();
	}
	
	/**
	 * Get the form to be displayed as mail to members input form.
	 * 
	 * @param Array $a_recipients The user_ids of the recipients of the mail.
	 * @return ilPropertyFormGUI The form to be taken as input form.
	 */
	protected function getMailToMembersForm($a_recipients) {
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("./Services/Form/classes/class.ilTextInputGUI.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("./Services/Form/classes/class.ilHiddenInputGUI.php");
		
		$user_data = $this->getUserData($a_recipients);
		
		$to = implode(", ", array_map(array($this, "userDataToString"), $user_data));
		$from = implode(", ", array_map( array($this, "userDataToString")
									   , $this->getUserData(array($this->user->getId()))
									   ));
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("mail_to_members"));
		
		$from_field = new ilNonEditableValueGUI($this->lng->txt("sender"), "from");
		$from_field->setValue($from);
		$form->addItem($from_field);
		
		$to_field = new ilNonEditableValueGUI($this->lng->txt("recipient"), "to");
		$to_field->setValue($to);
		$form->addItem($to_field);
		
		$about_field = new ilTextInputGUI($this->lng->txt("subject"), "subject");
		//gev-patch #2280 start
		$about_field->setRequired(true);
		//gev-patch end
		$form->addItem($about_field);
		
		$message_field = new ilTextAreaInputGUI($this->lng->txt("message"), "message");
		$message_field->setRows(10);
		//gev-patch #2280 start
		$message_field->setRequired(true);
		//gev-patch end
		$form->addItem($message_field);
		
		$attachment_select = $this->getAttachmentSelect();
		$form->addItem($attachment_select);
		
		foreach ($a_recipients as $recipient) {
			$recipient_hidden = new ilHiddenInputGUI("recipients[]");
			$recipient_hidden->setValue($recipient);
			$form->addItem($recipient_hidden);
		}

		$form->addCommandButton("sendMailToMembers", $this->lng->txt("send_mail"));
		
		return $form;
	}
	 
	
	/**
	 * Helper function to map user data array to string to be 
	 * displayed in "to"-field of mail to members.
	 *
	 * @param Array $a_user_data One user data entry
	 * @return string to be displayed.
	 */
	protected function userDataToString($a_user_data) {
		return $a_user_data["firstname"]." ".$a_user_data["lastname"];
	}
	
	/**
	 * Create an attachment selection multi select based on all
	 * available attachments.
	 *
	 * @return ilMultiSelectInputGUI The form-element for attachment selection.
	 */
	protected function getAttachmentSelect() {
		require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");

		$available = $this->getMailAttachments()->getList();

		if(count($available) == 0) {
			return null;
		}

		$options = array();
		foreach ($available as $av) {
			$options[$av] = $av;
		}

		$select = new ilMultiSelectInputGUI($this->lng->txt("attachments"), "attachments");
		$select->setOptions($options);
		$select->setWidth(500);
		$select->setInfo($this->lng->txt("mail_attachments_info"));

		return $select;
	}

	/**
	 * Send the given message and log it. 
	 * 
	 * TODO: This should definetly go elsewhere, since this is clearly no GUI-
	 *       logic.
	 *
	 * @param integer $a_from User id of the sender.
	 * @param Array $a_to User ids of the recipients.
	 * @param string $a_subject The subject of the message.
	 * @param string $a_message The message itself.
	 * @param Array $a_attachments A list of attached files.
	 **/
	protected function send($a_from, $a_to, $a_subject, $a_message, $a_attachments) {
		require_once("./Services/Mail/classes/class.ilMimeMail.php");
		
		$attachments = array_map(array($this, "mapAttachmentToRecord"), $a_attachments);
		
		// gev-patch start
		global $ilias, $ilSetting;
		$fn = $ilSetting->get("mail_system_sender_name");
		$fm = $ilias->getSetting("mail_external_sender_noreply");
		// gev-patch end
		
		foreach($a_to as $recipient) {
			$mail_data = array( 
						// gev-patch start
						//"from" => $this->mapUserIdToMailString($a_from)
						   "from" => $fn." <".$fm.">"
						// gev-patch end
						 , "to" => $this->mapUserIdToMailString($recipient)
						 , "cc" => array()
						 , "bcc" => array()
						 , "subject" => $a_subject
						 , "message_plain" => $a_message
						 , "attachments" => $attachments
						 );
			
			$mail = new ilMimeMail();
			$mail->From($mail_data["from"]);
			$mail->To($mail_data["to"]);
			$mail->Cc($mail_data["cc"]);
			$mail->Bcc($mail_data["bcc"]);
			$mail->Subject($mail_data["subject"]);
			$mail->Body($mail_data["message_plain"]);
			foreach ($mail_data["attachments"] as $attachment) {
				$mail->Attach($attachment["path"]);
			}
			$mail->Send();
			$this->log($mail_data, $this->lng->txt("send_by").": ".ilObjUser::_lookupFullname($a_from));
		}
	}
	
	/**
	 * Log a message to the mail log.
	 * 
	 * @param Array $a_mail The mail to be logged.
	 * @param string $a_occasion The occasion the mail was send.
	 */
	protected function log($a_mail, $a_occasion) {
		$this->getMailLog()->log($a_mail, $a_occasion);
	}
	
	/**
	 * Helper function for send to map user_id to Firstname Lastname <email-address>
	 *
	 * @param Integer $a_user_id The id of the user to map to string.
	 * @return String The mapped string.
	 */
	protected function mapUserIdToMailString($a_user_id) {
		return ilObjUser::_lookupFullname($a_user_id)." <".ilObjUser::_lookupEmail($a_user_id).">";
	}
	
	/**
	 * Helper function for send to map attachment name to attachment record.
	 *
	 * An attachment record contains the fields name, path and link. 
	 *
	 * @param string $a_filename The filename of the attachment.
	 * @return Array The record for the file with name.
	 */
	protected function mapAttachmentToRecord($a_filename) {
		$this->ctrl->setParameter($this, "filename", $a_filename);
		$ret =  array( "name" => $a_filename
					 , "path" => $this->getMailAttachments()->getPathTo($a_filename)
					 , "link" => $this->ctrl->getLinkTarget($this, "deliverAttachment")
					 );
		$this->ctrl->clearParameters($this);
		return $ret;
	}
	
	// MAIL LOG

	/**
	 * Show a table of all entries in the mail log.
	 */
	protected function showLog() {
		require_once("Services/Mailing/classes/class.ilMailLogTableGUI.php");

		$log_gui = new ilMailLogTableGUI($this->getMailLog(), $this, $this->ctrl->getCmd());

		$this->tpl->setContent($log_gui->getHTML());
	}

	/**
	 * Show a logged mail, where the id is in POST or GET.
	 *
	 * Redirects to showLog if no mail_id is found.
	 */
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
		
		$view_gui = new ilMailViewGUI( $mail["occasion"]." ".($this->lng->txt("mailing_on"))." ".$moment
									 , $this->ctrl->getLinkTarget($this, "showLog")
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

		$this->tpl->setContent($view_gui->getHTML());
	}

	/**
	 * Deliver an attachment of a log entry.
	 */
	protected function deliverMailLogAttachment() {
		$filename = $_GET["filename"];
		$hash = $_GET["hash"];

		$path = $this->getMailLog()->getPath($hash);

		$this->deliverAttachmentFile($filename, $path);
	}
	
	protected function resendMail() {
		if($_GET["log_id"] === null or !is_numeric($_GET["log_id"])) {
			$this->ctrl->redirect($this, "showLog");
			exit();
		}
		
		$mail_id = intval($_GET["log_id"]);
		$mail = $this->getMailLog()->getEntry($mail_id);
		
		$res = $this->getAutoMails()->send($mail["mail_id"], array($mail["recipient_id"]), $this->getAutoMails()->getUserOccasion());
		
		if ($res === true) {
			ilUtil::sendSuccess($this->lng->txt("auto_mail_send_successfully"));
		}
		else {
			ilUtil::sendFailure($res);
		}
		
		$this->showLog();
	}
}