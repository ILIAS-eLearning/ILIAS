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
		global $DIC;

		if(self::$maling_lists === null)
		{
			require_once 'Services/Contact/classes/class.ilMailingLists.php';
			self::$maling_lists = new ilMailingLists($DIC->user());
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
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Mailing list not  valid: '%s'", $this->address->getMailbox()
			));
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

			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Found the following user ids for address (mailing list title) '%s': %s", $this->address->getMailbox(), implode(', ', array_unique($usr_ids))
			));
		}
		else
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Did not find any user ids for address (mailing list title) '%s'", $this->address->getMailbox()
			));
		}

		return array_unique($usr_ids);
	}
}