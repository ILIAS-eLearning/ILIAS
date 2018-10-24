<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMailingListAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMailingListAddressType extends \ilBaseMailAddressType
{
	/**
	 * @var \ilMailingLists|null
	 */
	protected static $mailingLists;

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

		if (self::$mailingLists === null) {
			self::$mailingLists = new \ilMailingLists($DIC->user());
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function isValid(int $a_sender_id): bool
	{
		$valid = self::$mailingLists->mailingListExists($this->address->getMailbox());

		if (!$valid) {
			$this->errors = [
				['mail_no_valid_mailing_list', $this->address->getMailbox()]
			];
		}

		return $valid;
	}

	/**
	 * @inheritdoc
	 */
	public function resolve(): array
	{
		$usr_ids = [];

		if (self::$mailingLists->mailingListExists($this->address->getMailbox())) {
			foreach (self::$mailingLists->getCurrentMailingList()->getAssignedEntries() as $entry) {
				$usr_ids[] = $entry['usr_id'];
			}

			\ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Found the following user ids for address (mailing list title) '%s': %s",
				$this->address->getMailbox(), implode(', ', array_unique($usr_ids))
			));
		} else {
			\ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Did not find any user ids for address (mailing list title) '%s'", $this->address->getMailbox()
			));
		}

		return array_unique($usr_ids);
	}
}