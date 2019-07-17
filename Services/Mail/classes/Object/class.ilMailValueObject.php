<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailValueObject
{
	/** @var string */
	private $recipients;

	/** @var string */
	private $recipientsCC;

	/** @var string */
	private $recipientsBCC;

	/** @var string */
	private $subject;

	/** @var string */
	private $body;

	/** @var */
	private $attachment;

	/** @var bool */
	private $usePlaceholders;

	/** @var bool */
	private $saveInSentBox;

	/** @var string */
	private $from;

	/**
	 * @param string $from
	 * @param string $recipients
	 * @param string $recipientsCC
	 * @param string $recipientsBCC
	 * @param string $subject
	 * @param string $body
	 * @param $attachment
	 * @param bool $usePlaceholders
	 * @param bool $saveInSentBox
	 */
	public function __construct(
		string $from,
		string $recipients,
		string $recipientsCC,
		string $recipientsBCC,
		string $subject,
		string $body,
		$attachment,
		bool $usePlaceholders = false,
		bool $saveInSentBox = false
	) {
		$this->from             = $from;
		$this->recipients       = $recipients;
		$this->recipientsCC     = $recipientsCC;
		$this->recipientsBCC    = $recipientsBCC;
		$this->subject          = $subject;
		$this->body             = $body;
		$this->attachment       = $attachment;
		$this->usePlaceholders  = $usePlaceholders;
		$this->saveInSentBox        = $saveInSentBox;
	}

	/**
	 * @return string
	 */
	public function getRecipients(): string
	{
		return $this->recipients;
	}

	/**
	 * @return string
	 */
	public function getRecipientsCC(): string
	{
		return $this->recipientsCC;
	}

	/**
	 * @return string
	 */
	public function getRecipientsBCC(): string
	{
		return $this->recipientsBCC;
	}

	/**
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

	/**
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * @return mixed
	 */
	public function getAttachment()
	{
		return $this->attachment;
	}

	/**
	 * @return bool
	 */
	public function isUsingPlaceholders(): bool
	{
		return $this->usePlaceholders;
	}

	/**
	 * @return bool
	 */
	public function shouldSaveInSentBox(): bool
	{
		return $this->saveInSentBox;
	}

	/**
	 * @return string
	 */
	public function getFrom(): string
	{
		return $this->from;
	}
}
