<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/JSON/classes/class.ilJsonUtil.php';

/**
 * Let this class derive from a real http response class in future
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceJsonResponse
{
	const STATUS_SUCCESS = 1;
	const STATUS_FAILURE = 2;
	
	/**
	 * @var stdClass
	 */
	protected $body;

	/**
	 * 
	 */
	public function __construct()
	{
		$this->initHttpBody();
	}

	/**
	 * 
	 */
	protected function initHttpBody()
	{
		$this->body = new stdClass();
		$this->body->status = self::STATUS_SUCCESS;
		$this->body->body   = '';
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->body->status = $status;
	}

	/**
	 * @param string $body
	 */
	public function setBody($body)
	{
		$this->body->body = $body;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		header("Content-type: application/json; charset=UTF-8");
		echo ilJsonUtil::encode($this->body);
		exit();
	}
}