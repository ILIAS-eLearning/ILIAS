<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntity
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntity
{
	/** @var int */
	protected $id = 0;

	/** @var int */
	protected $user_id = 0;

	/** @var string */
	protected $text = '';

	/** @var int */
	protected $timestamp = 0;

	/** @var string */
	protected $hash = '';

	/** @var string */
	protected $title = '';

	/** @var int */
	protected $document_id = 0;

	/** @var string */
	protected $criteria = '';

	/**
	 * @param string $hash
	 */
	public function setHash(string $hash)
	{
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * @param string $text
	 */
	public function setText(string $text)
	{
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * @param int $timestamp
	 */
	public function setTimestamp(int $timestamp)
	{
		$this->timestamp = $timestamp;
	}

	/**
	 * @return int
	 */
	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->user_id;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function setId(int $id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;
	}

	/**
	 * @return int
	 */
	public function getDocumentId(): int
	{
		return $this->document_id;
	}

	/**
	 * @param int $document_id
	 */
	public function setDocumentId(int $document_id)
	{
		$this->document_id = $document_id;
	}

	/**
	 * @return string
	 */
	public function getCriteria(): string 
	{
		return $this->criteria;
	}

	/**
	 * @param string $criteria
	 */
	public function setCriteria(string $criteria)
	{
		$this->criteria = $criteria;
	}
}
