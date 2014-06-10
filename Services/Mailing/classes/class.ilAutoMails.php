<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilAutoMails.
*
* Bundles automails together and makes them accessible for a consumer. To
* create a concrete class reimplement the abstract functions at the head
* of the class definition.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

abstract class ilAutoMails {

	/**
	 *  Get the title of the auto mails bundled in the class.
	 *
	 * @return string The name of the auto mail bundle.
	 */
	abstract public function getTitle();

	/**
	 * Get a descriptional subtitle for the auto mails bundled in this class.
	 *
	 * @return string The subtitle of the auto mail bundle.
	 */
	abstract public function getSubtitle();

	/**
	 * Get an array containing all valid ids for automails in this bundle.
	 *
	 * Will be evaluated only once by this base class. Changes during runtime
	 * won't work.
	 *
	 * @return array Array containing the auto mail ids.
	 */
	abstract public function getIds();

	/**
	 * Get an ilAutomail-Object for the given id.
	 *
	 * This base class caches created automail objects, this method therefore
	 * will only be called once per id.
	 *
	 * Take care that the created automails have the id they are created for.
	 *
	 * @param string $a_id The id of the automail object to get.
	 * @return ilAutoMail The created automail object.
	 */
	abstract protected function createAutoMail($a_id);

	// Used for caching.
	private $auto_mails;
	private $ids;

	// ilMailLog-object

	private $logger;

	/**
	 * Set the ilMailLog-object for the gui.
	 *
	 * @param ilMailLog $a_mail_log The object to be used.
	 */
	protected function setMailLog(ilMailLog $a_mail_log) {
		$this->logger = $a_mail_log;
	}

	/**
	 * Get the ilAutoMails-object of the gui.
	 *
	 * Call initAutoMails if not done yet.
	 *
	 * @return ilMailAttachments The ilMailAttachments object of the GUI.
	 */
	protected function getMailLog() {
		if ($this->logger == null) {
			$this->initMailLog();

			if ($this->logger == null) {
				throw new Exception("Member logger still unitialized after ".
									"call to initMailLog. Did you forget ".
									"to call setMailLog in you implementation ".
									"of initMailLog?");
			}
		}

		return $this->logger;
	}


	protected $obj_id;

	/**
	 * Construct automail object.
	 *
	 * @param ilMailLog|null $a_logger Logger to be used or null if there
	 * 								   should be no logging.
	 * @param integer $a_obj_id The id of the object the automails are owned by.
	 */
	public function __construct($a_obj_id) {
		global $ilUser;

		$this->user = &$ilUser;

		$this->auto_mails = array();
		$this->ids = $this->getIds();

		$this->logger = $a_logger;

		$this->obj_id = $a_obj_id;
	}

	/**
	 * Get automail object with id.
	 *
	 * If automail is not already created calls createAutoMail.
	 *
	 * @param string $a_id The id of the automail object to get.
	 * @return ilAutoMail The created automail object.
	 */
	public function getAutoMail($a_id) {
		if (!in_array($a_id, $this->ids)) {
			throw new Exception("Unknown auto mail id: ".$a_id);
		}

		if (!array_key_exists($a_id, $this->auto_mails)) {
			$mail = $this->createAutoMail($a_id);

			if (!is_subclass_of($mail, "ilAutoMail")) {
				throw new Exception("Object created in response for automail id ".
									$a_id." is not an ilAutoMail object.");
			}

			if ($a_id != $mail->getId()) {
				throw new Exception("ID missmatch for automail: ".$a_mail_id." != ".$mail->getId());
			}

			$mail->setLogger($this->getMailLog());
			$this->auto_mails[$a_id] = $mail;
		}

		return $this->auto_mails[$a_id];
	}

	/**
	 * Get an array containing info about all available automails.
	 *
	 * Every info is a dict containing fields id, title, description,
	 * last_send, scheduled_for, users_only and has_recipients.
	 *
	 * ast_send is an ilDateTime object or null if auto mail wasn't send yet.
	 * scheduled_for is an ilDateTime object.
	 * users_only is a bool specifying weather only ILIAS users are possible
	 * recipients (true) or weather there are external recipients (false)
	 *
	 * @return array Array containing info about all automails bundled in this
	 *				 object.
	 */
	final public function getAllInfo() {
		$ret = array();

		foreach ($this->ids as $num => $id) {
			$ret[] = $this->getInfo($id);
		}
		
		return $ret;
	}

	/**
	 * Get info array for a single automail. Info array is layouted like
	 * described in getAllInfo.
	 *
	 * @param string $a_mail_id Id of automail to get info for.
	 * @return Array Info array for automail.
	 */
	final public function getInfo($a_mail_id) {
		$mail = $this->getAutoMail($a_mail_id);

		return array(
			  "id" => $a_mail_id
			, "title" => $mail->getTitle()
			, "description" => $mail->getDescription()
			, "last_send" => $mail->getLastSend()
			, "scheduled_for" => $mail->getScheduledFor()
			, "users_only" => $mail->getUsersOnly()
			, "has_recipients" => count($mail->getRecipientAddresses()) > 0
		);
	}


	/**
	 * Get the path to an attachment of that mail.
	 *
	 * @param string $a_mail_id The id of the mail where the file is attached.
	 * @param string $a_name The name of the attached file.
	 * @return string Path to the attachment file.
	 */
	final public function getAttachmentPath($a_mail_id, $a_name) {
		return $this->getAutoMail($a_mail_id)->getAttachmentPath($a_name);
	}

	/**
	 * Get a preview of the automail with id. Returns an array containing
	 * id, from, to, cc, bcc, subject, content, attachments and title.
	 * If user_id is null will use current user for user data in placeholders.
	 * If user_id is set will use that user, if it is null will use the
	 * current user.
	 *
	 * @param string $a_mail_id The id of the mail to get preview for.
	 * @param int $a_user_id The id of the user to fill placeholders with.
	 * @return Array Array containing the fields for the mail.
	 */
	final public function getPreview($a_mail_id, $a_user_id = null) {
		$recipient = $a_user_id===null ?
					 $this->user->getId() :
					 $a_user_id;

		return $this->getAutoMail($a_mail_id)->getMail($recipient);
	}

	/**
	 * Send automail with id now.
	 * If recipients is not null it should be an array containing mail
	 * adresses or user_ids. If recipients is empty will use all recipients
	 * of the automail.
	 * If occasion is null will use the title of the auto_mail as occasion
	 * for the logger.
	 *
	 * @param string $a_mail_id Id of the mail to send.
	 * @param Array|null $a_recipients Array containing user_ids or addresses
	 *								   to send the mail to.
	 * @param string $a_occassion The occassion of the mail sending, will be
	 *							  passed to the mail logger.
	 */
	final public function send($a_mail_id, $a_recipients = null, $a_occasion = null) {
		return $this->getAutoMail($a_mail_id)->send($a_recipients, $a_occasion);
	}

	/**
	 * Get user_ids of all possible recipients for the automail with id.
	 *
	 * Since an automail might also be sent to an external address not
	 * belonging to an ILIAS user, the result might not contain all
	 * possible recipients.
	 *
	 * @param string $a_mail_id The id of the mail to get recipients for.
	 * @return Array Array containing user_ids of possible recipients.
	 */
	final public function getRecipientUserIDs($a_mail_id) {
		return $this->getAutoMail($a_mail_id)->getRecipientUserIDs();
	}

	/**
	 * Get adresses of all possible recipients of the auto mail with id.
	 *
	 * @param string $a_mail_id The id of the mail to get recipients for.
	 * @return Array Array containing dictionary entries with keys "name"
	 *					   and "email".
	 */
	final public function getRecipientAddresses($a_mail_id) {
		return $this->getAutoMail($a_mail_id)->getRecipientAddresses();
	}

	/**
	 * Get an occasion string for the current user.
	 *
	 * Returns users login.
	 *
	 * @return string Occasion string.
	 */
	public function getUserOccasion() {
		return $this->user->getLogin();
	}
}

?>