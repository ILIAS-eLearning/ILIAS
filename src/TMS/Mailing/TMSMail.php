<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * everything a mail needs to know
 */
class TMSMail implements Mail {

	/**
	 * @var Recipient
	 */
	protected $recipient;

	/**
	 * @var string
	 */
	protected $template_ident;

	/**
	 * @var MailContext[]
	 */
	protected $contexts;

	/**
	 * @var Attachments | null
	 */
	protected $attachments;


	public function __construct(
		Recipient $recipient,
		$template_ident,
		array $contexts,
		Attachments $attachments = null
	) {
		assert('is_string($template_ident)');
		$this->recipient = $recipient;
		$this->template_ident = $template_ident;
		$this->contexts = $contexts;
		$this->attachments = $attachments;
	}

	/**
	 * @inheritdoc
	 */
	public function getRecipient() {
		return $this->recipient;
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateIdentifier() {
		return $this->template_ident;
	}

	/**
	 * @inheritdoc
	 */
	public function getContexts() {
		return $this->contexts;
	}

	/**
	 * @inheritdoc
	 */
	public function getAttachments() {
		return $this->attachments;
	}
}
