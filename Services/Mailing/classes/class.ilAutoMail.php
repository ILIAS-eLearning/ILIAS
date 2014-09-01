<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilAutoMail.
*
* Represents an auto mail that could be send for an ilias object.
*
* Define it's properties by implementing the abstract methods on top
* of the class definition.
*
* TODO: Uses PHPMailer for sending mail atm. This should be replaced in
*       further versions to support ilias internal mail delivery.
*
*       The sending mechanism for mails should be factored out anyway...
*
*       The array mail_data should be replaced by objects of a new class
*       representing one mail.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

abstract class ilAutoMail {
	/**
	 * Get the title of the automail.
	 *
	 * @return string The title.
	 */
	abstract public function getTitle();

	/**
	 * Get a (short) description for the automail.
	 *
	 * @return string The description.
	 */
	abstract public function getDescription();

	/**
	 * Get the moment when the automail was send the last time.
	 *
	 * Returns null if auto mail was not send yet.
	 *
	 * @return ilDateTime|null The moment when the mail was send.
	 */
	abstract public function getLastSend();

	/**
	 * Get the moment where the automail is scheduled to be send.
	 *
	 * Returns null if the sending of the mail is not scheduled for
	 * sending.
	 *
	 * @return ilDateTime|null The moment the mail is scheduled to
	 * 						   be send at.
	 */
	abstract public function getScheduledFor();

	/**
	 * Are there only users in the designated recipients of the automail.
	 *
	 * Is used by MailingGUI to determine how to handle the sending of the
	 * automail.
	 *
	 * @return bool true if only ilias-users are possible recipients
	 *				for the automail, false otherwise.
	 */
	abstract public function getUsersOnly();

	/**
	 * Get user_ids of all designated recipients for the automail.
	 *
	 * Since an automail might also be sent to an external address not
	 * belonging to an ILIAS user, the result might not contain all
	 * possible recipients. You may want to check getUsersOnly.
	 *
	 * @return Array Array containing user_ids of all designated recipients.
	 */
	abstract public function getRecipientUserIDs();

	/**
	 * Get the adresses and names of all possible recipients of the auto mail.
	 *
	 * @return Array Array containing dictionary entries with keys "name"
	 *				 and "email".
	 */
	abstract public function getRecipientAddresses();

	/**
	 * Get the concrete mail for recipient, either given by a user_id
	 * or a mail address.
	 *
	 *
	 * @return Array|null Dictionary containing fields from, to, cc, bcc, subject,
	 *				 	  message_plain, message_html, attachments or null if theres
	 *					  no mail for user.
	 */
	abstract public function getMail($a_recipient);

	/**
	 * Get the path to an attachment of the mail.
	 *
	 * @param string $a_name Name of the attached file.
	 * @return string Path to the file.
	 */
	abstract public function getAttachmentPath($a_name);

	protected $id;
	protected $logger;

	/**
	 * Constuct the auto mail.
	 *
	 * Make sure that the given id of the mail corresponds to the one
	 * in its ilAutoMails object.
	 *
	 * @param string $a_id The id of the mail.
	 */
	public function __construct($a_id) {
		global $ilUser, $lng;

		$this->user = &$ilUser;
		$this->lng = $lng;

		$this->id = $a_id;
		$this->logger = null;
	}

	/**
	 * Get the id of this mail.
	 *
	 * @return string The id.
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set a logger to be used when sending the automail.
	 *
	 * @param ilMailLog $a_logger The logger to be used.
	 */
	public function setLogger(ilMailLog $a_logger) {
		$this->logger = $a_logger;
	}

	/**
	 * Send automail now.
	 *
	 * If $a_recipients is null will send the automail to all its designated
	 * recipients. If recipient is not null it should be an array containing
	 * names and email adresses or user_ids.
	 *
	 * The occasion will be passed to the logging. If occasion is null will
	 * use the title of this mail as occasion.
	 *
	 * @param array|null $a_recipient The recipients to send the mail to.
	 * @param string|null $a_occasion The occasion to be passed to the mail
	 *								  logger.
	 * @return true|string Returns true on success or an error message
	 * 					   otherwise.
	 */
	public function send($a_recipients = null, $a_occasion = null) {
		// I really don't like that dependency...
		require_once ("./Services/MailTemplates/lib/phpmailer/class.phpmailer.php");

		global $ilUser, $ilLog;

		if ($a_recipients === null) {
			$a_recipients = $this->getUsersOnly()
							? $this->getRecipientUserIDs()
							: $this->getRecipientAddresses();
		}

		if ($a_occasion === null) {
			$a_occasion = $this->getTitle();
		}
		
		$no_mails = false;

		foreach ($a_recipients as $recipient) {
			$mail_data = $this->getMail($recipient);

			if ($mail_data === null) {
				$no_mails = true;
				continue;
			}

			// This part looks really crappy...
			// Another reason to factor that stuff out...
			$mail = new PHPMailer();
			
			$addr = $this->splitAddress($mail_data["from"]);
			$mail->From = $addr[2];
			$mail->FromName = $addr[1];
						
			// Use php mail function
			$mail->IsMail();
			
			$addr = $this->splitAddress($mail_data["to"]);
			$mail->AddAddress($addr[2], $addr[1]);
			
			foreach ($mail_data["cc"] as $cc) {
				$addr = $this->splitAddress($cc);
				$mail->AddCC($addr[2], $addr[1]);
			}
			foreach ($mail_data["bcc"] as $bcc) {
				$addr = $this->splitAddress($bcc);
				$mail->AddBCC($addr[2], $addr[1]);
			}

			$mail->Subject = $mail_data["subject"];

			$msg_html = $mail_data["message_html"];
			$msg_plain = $mail_data["message_plain"];
			
			if ($mail_data["frame_html"]) {
				$msg_html = str_ireplace("[content]", $msg_html, $mail_data["frame_html"]);
			}
			if ($mail_data["frame_plain"]) {
				$msg_plain = str_ireplace("[content]", $msg_plain, $mail_data["frame_plain"]);
			}
			if ($mail_data["image_path"]) {
				$msg_html = str_ireplace("[image]", '<img src="cid:frame_image_path" style="'.$mail_data["image_style"].'" />', $msg_html);
				$mail->AddEmbeddedImage($mail_data["image_path"], "frame_image_path");
			}

			if (strlen($mail_data["message_html"]) > 0) {
				$mail->Body = "<div style='font-size: .8em'>".$msg_html."</div>";
				$mail->isHTML(true);
				$mail->AltBody = $msg_plain;
			}
			else {
				$mail->Body = $msg_plain;
				$mail->isHTML(false);
			}

			$mail->CharSet = "utf-8";

			foreach ($mail_data["attachments"] as $attachment) {
				$mail->AddAttachment($attachment["path"], $attachment["name"]);
			}

			if (!$mail->Send()) {
				$ilLog->write("ilMail::send: PHPMailer-error when sending ".$this->getId().": ".$mail->ErrorInfo);
			}

			$this->log($mail_data, $a_occasion);
		}

		if ($no_mails) {
			return $this->lng->txt("no_mail_for_some_users");
		}

		return true;
	}
	
	// Helper for using php-mailer. Should be removed in the future.
	private function splitAddress($addr) {
		$ret = array();
		if (preg_match('/^(.*)<(.*)>$/', $addr, $ret) === 1) {
			return $ret;
		}
		else {
			return array(0 => $addr, 1 => "", 2 => $addr);
		}
	}

	/**
	 * Log the given mail with indicated occasion.
	 *
	 * If a logger was set uses that, else does nothing.
	 *
	 * @param Array $a_mail The mail to be logged, formatted like defined in
	 * 						getMail.
	 * @param string $a_occasion The occasion of the mail sending passed to
	 *                           the logger.
	 */
	protected function log($a_mail, $a_occasion) {
		if ($this->logger !== null) {
			$this->logger->log($a_mail, $a_occasion);
		}
	}
}

?>