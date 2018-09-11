<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeFactory
{
	/** @var \ilGroupNameAsMailValidator */
	private $groupNameValidator;

	/**
	 * @param \ilGroupNameAsMailValidator|null $groupNameValidator
	 */
	public function __construct(\ilGroupNameAsMailValidator $groupNameValidator = null)
	{
		if ($groupNameValidator === null) {
			$groupNameValidator = new \ilGroupNameAsMailValidator(\ilMail::ILIAS_HOST);
		}

		$this->groupNameValidator = $groupNameValidator;
	}

	/**
	 * @param \ilMailAddress $a_address
	 * @return \ilMailAddressType
	 */
	public function getByPrefix(\ilMailAddress $a_address): \ilMailAddressType
	{
		switch(true)
		{
			case substr($a_address->getMailbox(), 0, 1) != '#' && substr($a_address->getMailbox(), 0, 2) != '"#':
				return new \ilMailLoginOrEmailAddressAddressType($a_address);

			case substr($a_address->getMailbox(), 0, 7) == '#il_ml_':
				return new \ilMailMailingListAddressType($a_address);

			case ($this->groupNameValidator->validate($a_address)):
				return new \ilMailGroupAddressType($a_address);

			default:
				return new \ilMailRoleAddressType($a_address);
		}
	}
}
