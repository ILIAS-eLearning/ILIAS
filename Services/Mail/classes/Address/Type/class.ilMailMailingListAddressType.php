<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Type/class.ilBaseMailAddressType.php';

/**
 * Class ilMailMailingListAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMailingListAddressType extends ilBaseMailAddressType
{
	/**
	 * @var ilMailingLists|null
	 */
	protected static $maling_lists;

	/**
	 *
	 */
	protected function init()
	{
		parent::init();
		self::initMailingLists();
	}

	/**
	 * 
	 */
	protected static function initMailingLists()
	{
		/** @var $ilUser ilObjUser */
		global $ilUser;

		if(self::$maling_lists === null)
		{
			require_once 'Services/Contact/classes/class.ilMailingLists.php';
			self::$maling_lists = new ilMailingLists($ilUser);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function isValid($a_sender_id)
	{
		$valid = self::$maling_lists->mailingListExists($this->address->getMailbox());

		if(!$valid)
		{
			$this->errors = array(
				array('mail_no_valid_mailing_list', $this->address->getMailbox())
			);
		}

		return $valid;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve()
	{
		$usr_ids = array();

		if(self::$maling_lists->mailingListExists($this->address->getMailbox()))
		{
			foreach(self::$maling_lists->getCurrentMailingList()->getAssignedEntries() as $entry)
			{
				$usr_ids[] = $entry['usr_id'];
			}
		}

		return array_unique($usr_ids);
	}
}