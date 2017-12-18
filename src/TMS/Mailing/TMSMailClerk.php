<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * The Clerk uses the MailContentBuilder to assembles the actual mail-bodies,
 * factorizes the mail with the data from Recipient and gives the mails
 * to the sender.
 * Finally, a log entry is written.
 *
 */
class TMSMailClerk {

	/**
	 * @var MailContentBuilder
	 */
	protected $content_builder;

	/**
	 * @var LoggingDB
	 */
	protected $logger;

	/**
	 * @var PHPMail
	 */
	protected $sender;

	/**
	 * @var Recipient
	 */
	protected $from;


	public function __construct(
		MailContentBuilder $content_builder,
		LoggingDB $logger,
		Recipient $from
	) {
		$this->content_builder = $content_builder;
		$this->logger = $logger;
		$this->from = $from;
		$this->initSender();
	}

	/**
	 * Initialize PHPMailer.
	 *
	 * @return void
	 */
	private function initSender() {
		$this->sender = new \PHPMailer();
		$this->sender->CharSet = "utf-8";
		$this->sender->isHTML(true);
	}

	/**
	 * Send mails.
	 *
	 * @param TMSMail[] $mails
	 * @param string $event
	 * @return void
	 */
	public function process($mails, $event) {

		$mail_from_address = $this->from->getMailAddress();
		$mail_from_name = $this->from->getUserName();

		foreach ($mails as $mail) {
			$do_send = true;
			$err = array();

			$recipient = $mail->getRecipient();
			$contexts = $mail->getContexts();
			$attachments = $mail->getAttachments();
			$template_ident = $mail->getTemplateIdentifier();

			$builder =  $this->content_builder->withData($template_ident, $contexts);

			$subject = $builder->getSubject();
			$msg_html = $builder->getMessage();
			$msg_plain = $builder->getPlainMessage();
			$embedded = $builder->getEmbeddedImages();

			$mail_to_address = $recipient->getMailAddress();
			$mail_to_name = $recipient->getUserName();

			if(is_null($mail_to_address)) {
				$err = array('There was no mail address given.', 'Mail was not sent');
				$mail_to_address = '';
				$do_send = false;
			}
			if($recipient->isInactiveUser()) {
				$err = array('The user is inactive.');
				$do_send = false;
			}

			if($do_send) {
				$this->sender->setFrom($mail_from_address, $mail_from_name);
				$this->sender->ClearAllRecipients(); //only send to one recipient!
				$this->sender->ClearCCs();
				$this->sender->ClearBCCs();
				$this->sender->clearAttachments();
				$this->sender->addAddress($mail_to_address, $mail_to_name);
				$this->sender->Subject = $subject;
				$this->sender->Body = $msg_html;
				$this->sender->AltBody = $msg_plain;
				foreach ($embedded as $embed) {
					list($path, $file) = $embed;
					$this->sender->AddEmbeddedImage($path, $file);
				}
				if($attachments !== null) {
					foreach($attachments->getAttachments() as $attachment) {
						$this->sender->addAttachment($attachment->getAttachmentPath());
					}
				}
				if(! $this->sender->Send()) {
					$err[] = $this->sender->ErrorInfo;
				};
			}

			$mail_to_usr_id = $recipient->getUserId();
			$mail_to_usr_login = $recipient->getUserLogin();

			$crs_ref_id = null;
			foreach ($contexts as $context) {
				if(get_class($context) === 'ilTMSMailContextCourse') {
					$crs_ref_id = $context->getCourseRefId();
				}
			}

			$this->logger->log(
				$event,
				$template_ident,
				$mail_to_address,
				$mail_to_name,
				$mail_to_usr_id,
				$mail_to_usr_login,
				$crs_ref_id,
				(string)$subject,
				(string)$msg_plain,
				implode(PHP_EOL, $err)
			);
		}
	}
}
