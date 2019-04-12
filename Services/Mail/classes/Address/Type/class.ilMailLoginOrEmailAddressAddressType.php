<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Type/class.ilBaseMailAddressType.php';

/**
 * Class ilMailLoginOrEmailAddressAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLoginOrEmailAddressAddressType extends ilBaseMailAddressType
{
	/**
	 * {@inheritdoc}
	 */
	protected function isValid($a_sender_id)
	{

		if($this->address->getHost() == ilMail::ILIAS_HOST)
		{
			$usr_id = ilObjUser::getUserIdByLogin($this->address->getMailbox());
		}
		else
		{
			$usr_id = false;
		}

		if(!$usr_id && $this->address->getHost() == ilMail::ILIAS_HOST)
		{
			$this->errors[] = array('mail_recipient_not_found', $this->address->getMailbox());
			return false;
		}

		require_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		if($usr_id && !$this->rbacsystem->checkAccessOfUser($usr_id, 'internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			if ($this->receivesInternalMailsOnly($usr_id)) {
				ilLoggerFactory::getLogger('mail')->debug(sprintf(
					"Address '%s' not valid. Found id %s, but user can't use mail system.",
					$this->address->getMailbox(), $usr_id
				));
				$this->errors[] = array('user_cant_receive_mail', $this->address->getMailbox());
				return false;
			}
		}

		return true;
	}

	/**
	 * @param integer $usrId
	 * @return bool
	 */
	private function receivesInternalMailsOnly($usrId)
	{
		$options = new \ilMailOptions($usrId);

		return (int)$options->getIncomingType() === (int)\ilMailOptions::INCOMING_LOCAL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve()
	{
		if($this->address->getHost() == ilMail::ILIAS_HOST)
		{
			$address = $this->address->getMailbox();
			
		}
		else
		{
			$address = $this->address->getMailbox() . '@'. $this->address->getHost();
		}

		$usr_ids = array_filter(array(ilObjUser::getUserIdByLogin($address)));

		if(count($usr_ids) > 0)
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Found the following user ids for address (login) '%s': %s", $address, implode(', ', array_unique($usr_ids))
			));
		}
		else if(strlen($address) > 0)
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				"Did not find any user account for address (login) '%s'", $address
			));
		}

		return $usr_ids;
	}
}