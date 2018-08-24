<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailLoginOrEmailAddressAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLoginOrEmailAddressAddressType extends \ilBaseMailAddressType
{
	/**
	 * @inheritdoc
	 */
	protected function isValid(int $a_sender_id): bool
	{
		if ($this->address->getHost() == ilMail::ILIAS_HOST) {
			$usr_id = ilObjUser::getUserIdByLogin($this->address->getMailbox());
		} else {
			$usr_id = false;
		}

		if (!$usr_id && $this->address->getHost() == ilMail::ILIAS_HOST) {
			$this->errors[] = ['mail_recipient_not_found', $this->address->getMailbox()];
			return false;
		}

		if (
			$usr_id &&
			!$this->rbacsystem->checkAccessOfUser($usr_id, 'internal_mail', \ilMailGlobalServices::getMailObjectRefId())) {
			$this->errors[] = ['user_cant_receive_mail', $this->address->getMailbox()];
			return false;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function resolve(): array
	{
		if ($this->address->getHost() == \ilMail::ILIAS_HOST) {
			$address = $this->address->getMailbox();

		} else {
			$address = $this->address->getMailbox() . '@' . $this->address->getHost();
		}

		$usr_ids = array_filter([
			\ilObjUser::getUserIdByLogin($address)
		]);

		if (count($usr_ids) > 0) {
			\ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Found the following user ids for address (login) '%s': %s", $address,
				implode(', ', array_unique($usr_ids))
			));
		} else {
			if (strlen($address) > 0) {
				\ilLoggerFactory::getLogger('mail')->debug(sprintf(
					"Did not find any user account for address (login) '%s'", $address
				));
			}
		}

		return $usr_ids;
	}
}