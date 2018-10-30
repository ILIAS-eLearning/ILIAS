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

	/** @var \ilLogger */
	private $logger;

	/** @var \ilRbacSystem */
	protected $rbacsystem;

	/** @var \ilRbacReview */
	protected $rbacreview;

	/** @var \ilMailAddressTypeHelper */
	protected $typeHelper;

	/** @var \ilMailingLists */
	protected $lists;

	/**
	 * @param \ilGroupNameAsMailValidator|null $groupNameValidator
	 * @param \ilLogger|null                   $logger
	 * @param \ilRbacSystem|null               $rbacsystem
	 * @param \ilRbacReview|null               $rbacreview
	 * @param \ilMailAddressTypeHelper|null    $typeHelper
	 * @param \ilMailingLists|null             $lists
	 */
	public function __construct(
		\ilGroupNameAsMailValidator $groupNameValidator = null,
		\ilLogger $logger = null,
		\ilRbacSystem $rbacsystem = null,
		\ilRbacReview $rbacreview = null,
		\ilMailAddressTypeHelper $typeHelper = null,
		\ilMailingLists $lists = null
	)
	{
		global $DIC;

		if ($groupNameValidator === null) {
			$groupNameValidator = new \ilGroupNameAsMailValidator(\ilMail::ILIAS_HOST);
		}

		if ($logger === null) {
			$logger = \ilLoggerFactory::getLogger('mail');
		}

		if ($typeHelper === null) {
			$typeHelper = new \ilMailAddressTypeHelperImpl(\ilMail::ILIAS_HOST);
		}

		if ($rbacsystem === null) {
			$rbacsystem = $DIC->rbac()->system();
		}

		if ($rbacreview === null) {
			$rbacreview = $DIC->rbac()->review();
		}

		if ($lists === null) {
			$lists = new \ilMailingList($DIC->user());
		}

		$this->groupNameValidator = $groupNameValidator;
		$this->logger = $logger;
		$this->typeHelper = $typeHelper;
		$this->rbacsystem = $rbacsystem;
		$this->rbacreview = $rbacreview;
		$this->lists = $lists;
	}

	/**
	 * @param \ilMailAddress $address
	 * @return \ilMailAddressType
	 */
	public function getByPrefix(\ilMailAddress $address): \ilMailAddressType
	{
		switch (true) {
			case substr($address->getMailbox(), 0, 1) !== '#' && substr($address->getMailbox(), 0, 2) !== '"#':
				return new \ilMailLoginOrEmailAddressAddressType(
					$this->typeHelper,
					$address,
					$this->logger,
					$this->rbacsystem
				);

			case substr($address->getMailbox(), 0, 7) === '#il_ml_':
				return new \ilMailMailingListAddressType(
					$this->typeHelper,
					$address,
					$this->logger,
					$this->lists
				);

			case ($this->groupNameValidator->validate($address)):
				return new \ilMailGroupAddressType(
					$this->typeHelper,
					$address,
					$this->logger
				);

			default:
				return new \ilMailRoleAddressType(
					$this->typeHelper,
					$address,
					$this->logger,
					$this->rbacsystem,
					$this->rbacreview
				);
		}
	}
}
