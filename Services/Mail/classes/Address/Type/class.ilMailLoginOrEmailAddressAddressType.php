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
		/** @var $rbacsystem ilRbacSystem */
		global $rbacsystem;

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
		if($usr_id && !$rbacsystem->checkAccessOfUser($usr_id, 'internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			$this->errors[] = array('user_cant_receive_mail', $this->address->getMailbox());
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve()
	{
		if($this->address->getHost() == ilMail::ILIAS_HOST)
		{
			return array_filter(array(ilObjUser::getUserIdByLogin($this->address->getMailbox())));
		}
		else
		{
			return array_filter(array(ilObjUser::getUserIdByLogin($this->address->getMailbox() . '@'. $this->address->getHost())));
		}
	}
}