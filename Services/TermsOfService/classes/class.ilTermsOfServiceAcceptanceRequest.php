<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceBaseRequest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceRequest extends ilTermsOfServiceBaseRequest
{
	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $timestamp;

	/**
	 * @var ilTermsOfServiceSignableDocument
	 */
	protected $document;

	/**
	 * @param ilTermsOfServiceSignableDocument $document
	 */
	public function setDocument(ilTermsOfServiceSignableDocument $document)
	{
		$this->document = $document;
	}

	/**
	 * @return ilTermsOfServiceSignableDocument
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * @param int $timestamp
	 */
	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	/**
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->user_id;
	}
}
