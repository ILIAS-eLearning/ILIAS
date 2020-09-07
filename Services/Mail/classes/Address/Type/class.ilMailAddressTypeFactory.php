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

    /** @var \ilRoleMailboxSearch */
    protected $roleMailboxSearch;

    /**
     * @param \ilGroupNameAsMailValidator|null $groupNameValidator
     * @param \ilLogger|null                   $logger
     * @param \ilRbacSystem|null               $rbacsystem
     * @param \ilRbacReview|null               $rbacreview
     * @param \ilMailAddressTypeHelper|null    $typeHelper
     * @param \ilMailingLists|null             $lists
     * @param \ilRoleMailboxSearch|null        $roleMailboxSearch
     */
    public function __construct(
        \ilGroupNameAsMailValidator $groupNameValidator = null,
        \ilLogger $logger = null,
        \ilRbacSystem $rbacsystem = null,
        \ilRbacReview $rbacreview = null,
        \ilMailAddressTypeHelper $typeHelper = null,
        \ilMailingLists $lists = null,
        \ilRoleMailboxSearch $roleMailboxSearch = null
    ) {
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
            $lists = new \ilMailingLists($DIC->user());
        }

        if ($roleMailboxSearch === null) {
            $roleMailboxSearch = new \ilRoleMailboxSearch(new \ilMailRfc822AddressParserFactory(), $DIC->database());
        }

        $this->groupNameValidator = $groupNameValidator;
        $this->logger = $logger;
        $this->typeHelper = $typeHelper;
        $this->rbacsystem = $rbacsystem;
        $this->rbacreview = $rbacreview;
        $this->lists = $lists;
        $this->roleMailboxSearch = $roleMailboxSearch;
    }

    /**
     * @param \ilMailAddress $address
     * @param bool           $cached
     * @return \ilMailAddressType
     */
    public function getByPrefix(\ilMailAddress $address, bool $cached = true) : \ilMailAddressType
    {
        switch (true) {
            case substr($address->getMailbox(), 0, 1) !== '#' && substr($address->getMailbox(), 0, 2) !== '"#':
                $addressType = new \ilMailLoginOrEmailAddressAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger,
                    $this->rbacsystem
                );
                break;

            case substr($address->getMailbox(), 0, 7) === '#il_ml_':
                $addressType = new \ilMailMailingListAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger,
                    $this->lists
                );
                break;

            case ($this->groupNameValidator->validate($address)):
                $addressType = new \ilMailGroupAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger
                );
                break;

            default:
                $addressType = new \ilMailRoleAddressType(
                    $this->typeHelper,
                    $address,
                    $this->roleMailboxSearch,
                    $this->logger,
                    $this->rbacsystem,
                    $this->rbacreview
                );
                break;
        }

        return new \ilMailCachedAddressType($addressType, $cached);
    }
}
