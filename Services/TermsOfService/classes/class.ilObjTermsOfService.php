<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilObjTermsOfService extends ilObject
{
	/**
	 * @param int  $a_id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = 'tos';
		parent::__construct($a_id, $a_call_by_reference);
	}

	/**
	 *
	 */
	public function resetAll()
	{
		/**
		 * @var $ilDB      ilDB
		 * @var $ilSetting ilSetting
		 */
		global $ilDB, $ilSetting;

		$ilDB->manipulate('UPDATE usr_data SET agree_date = NULL');

		$ilSetting->set('tos_last_reset', time());
	}

	/**
	 * @return ilDateTime
	 */
	public function getLastResetDate()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		return new ilDateTime($ilSetting->get('tos_last_reset'), IL_CAL_UNIX);
	}

	/**
	 * @param bool $status
	 */
	public function saveStatus($status)
	{
		ilTermsOfServiceHelper::setStatus((bool)$status);
	}

	/**
	 * @return bool
	 */
	public function getStatus()
	{
		return ilTermsOfServiceHelper::isEnabled();
	}
}
