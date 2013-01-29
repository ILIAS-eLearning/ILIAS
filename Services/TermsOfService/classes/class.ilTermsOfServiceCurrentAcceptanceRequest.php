<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceBaseRequest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceCurrentAcceptanceRequest extends ilTermsOfServiceBaseRequest
{
	/**
	 * @var int
	 */
	protected $user_id;

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
