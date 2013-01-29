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
	protected $timestamp = 0;

	/**
	 * @var string
	 */
	protected $path_to_file = '';

	/**
	 * @param string $path_to_file
	 */
	public function setPathToFile($path_to_file)
	{
		$this->path_to_file = $path_to_file;
	}

	/**
	 * @return string
	 */
	public function getPathToFile()
	{
		return $this->path_to_file;
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
