<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilMailAddressTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeFactory
{
    private ilGroupNameAsMailValidator $groupNameValidator;
    private ilLogger $logger;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilMailAddressTypeHelper $typeHelper;
    protected ilMailingLists $lists;
    protected ilRoleMailboxSearch $roleMailboxSearch;

    public function __construct(
        ilGroupNameAsMailValidator $groupNameValidator = null,
        ilLogger $logger = null,
        ilRbacSystem $rbacsystem = null,
        ilRbacReview $rbacreview = null,
        ilMailAddressTypeHelper $typeHelper = null,
        ilMailingLists $lists = null,
        ilRoleMailboxSearch $roleMailboxSearch = null
    ) {
        global $DIC;

        if ($groupNameValidator === null) {
            $groupNameValidator = new ilGroupNameAsMailValidator(ilMail::ILIAS_HOST);
        }

        if ($logger === null) {
            $logger = ilLoggerFactory::getLogger('mail');
        }

        if ($typeHelper === null) {
            $typeHelper = new ilMailAddressTypeHelperImpl(ilMail::ILIAS_HOST);
        }

        if ($rbacsystem === null) {
            $rbacsystem = $DIC->rbac()->system();
        }

        if ($rbacreview === null) {
            $rbacreview = $DIC->rbac()->review();
        }

        if ($lists === null) {
            $lists = new ilMailingLists($DIC->user());
        }

        if ($roleMailboxSearch === null) {
            $roleMailboxSearch = new ilRoleMailboxSearch(new ilMailRfc822AddressParserFactory(), $DIC->database());
        }

        $this->groupNameValidator = $groupNameValidator;
        $this->logger = $logger;
        $this->typeHelper = $typeHelper;
        $this->rbacsystem = $rbacsystem;
        $this->rbacreview = $rbacreview;
        $this->lists = $lists;
        $this->roleMailboxSearch = $roleMailboxSearch;
    }

    public function getByPrefix(ilMailAddress $address, bool $cached = true): ilMailAddressType
    {
        switch (true) {
            case strpos($address->getMailbox(), '#') !== 0 && strpos($address->getMailbox(), '"#') !== 0:
                $addressType = new ilMailLoginOrEmailAddressAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger,
                    $this->rbacsystem
                );
                break;

            case strpos($address->getMailbox(), '#il_ml_') === 0:
                $addressType = new ilMailMailingListAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger,
                    $this->lists
                );
                break;

            case ($this->groupNameValidator->validate($address)):
                $addressType = new ilMailGroupAddressType(
                    $this->typeHelper,
                    $address,
                    $this->logger
                );
                break;

            default:
                $addressType = new ilMailRoleAddressType(
                    $this->typeHelper,
                    $address,
                    $this->roleMailboxSearch,
                    $this->logger,
                    $this->rbacsystem,
                    $this->rbacreview
                );
                break;
        }

        return new ilMailCachedAddressType($addressType, $cached);
    }
}
