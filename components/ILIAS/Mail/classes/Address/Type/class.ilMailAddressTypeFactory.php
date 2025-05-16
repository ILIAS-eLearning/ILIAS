<?php

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

declare(strict_types=1);

class ilMailAddressTypeFactory
{
    private readonly ilGroupNameAsMailValidator $group_name_validator;
    private readonly ilLogger $logger;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilMailAddressTypeHelper $type_helper;
    protected ilMailingLists $lists;
    protected ilRoleMailboxSearch $role_mailbox_search;

    public function __construct(
        ?ilGroupNameAsMailValidator $group_name_validator = null,
        ?ilLogger $logger = null,
        ?ilRbacSystem $rbacsystem = null,
        ?ilRbacReview $rbacreview = null,
        ?ilMailAddressTypeHelper $type_helper = null,
        ?ilMailingLists $lists = null,
        ?ilRoleMailboxSearch $role_mailbox_search = null
    ) {
        global $DIC;

        $this->group_name_validator = $group_name_validator ?? new ilGroupNameAsMailValidator(ilMail::ILIAS_HOST);
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->type_helper = $type_helper ?? new ilMailAddressTypeHelperImpl(ilMail::ILIAS_HOST);
        $this->rbacsystem = $rbacsystem ?? $DIC->rbac()->system();
        $this->rbacreview = $rbacreview ?? $DIC->rbac()->review();
        $this->lists = $lists ?? new ilMailingLists($DIC->user());
        $this->role_mailbox_search = $role_mailbox_search ?? new ilRoleMailboxSearch(
            new ilMailRfc822AddressParserFactory(),
            $DIC->database()
        );
    }

    public function getByPrefix(ilMailAddress $address, bool $cached = true): ilMailAddressType
    {
        $address_type = match (true) {
            str_starts_with($address->getMailbox(), '#il_ml_') => new ilMailMailingListAddressType(
                $this->type_helper,
                $address,
                $this->logger,
                $this->lists
            ),
            !str_starts_with($address->getMailbox(), '#') && !str_starts_with(
                $address->getMailbox(),
                '"#'
            ) => new ilMailLoginOrEmailAddressAddressType(
                $this->type_helper,
                $address,
                $this->logger,
                $this->rbacsystem
            ),
            $this->group_name_validator->validate($address) => new ilMailGroupAddressType(
                $this->type_helper,
                $address,
                $this->logger
            ),
            default => new ilMailRoleAddressType(
                $this->type_helper,
                $address,
                $this->role_mailbox_search,
                $this->logger,
                $this->rbacsystem,
                $this->rbacreview
            ),
        };

        return new ilMailCachedAddressType($address_type, $cached);
    }
}
