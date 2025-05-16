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

class ilMailRoleAddressType extends ilBaseMailAddressType
{
    public function __construct(
        ilMailAddressTypeHelper $type_helper,
        ilMailAddress $address,
        protected ilRoleMailboxSearch $role_mailbox_search,
        ilLogger $logger,
        protected ilRbacSystem $rbacsystem,
        protected ilRbacReview $rbacreview
    ) {
        parent::__construct($type_helper, $address, $logger);
    }

    /**
     * @return list<int>
     */
    protected function getRoleIdsByAddress(ilMailAddress $address): array
    {
        $combined_address = (string) $address;

        return $this->role_mailbox_search->searchRoleIdsByAddressString($combined_address);
    }

    protected function maySendToGlobalRole(int $sender_id): bool
    {
        if ($sender_id === ANONYMOUS_USER_ID) {
            return true;
        }

        return $this->rbacsystem->checkAccessOfUser(
            $sender_id,
            'mail_to_global_roles',
            $this->type_helper->getGlobalMailSystemId()
        );
    }

    protected function isValid(int $sender_id): bool
    {
        $role_ids = $this->getRoleIdsByAddress($this->address);
        if (!$this->maySendToGlobalRole($sender_id)) {
            foreach ($role_ids as $role_id) {
                if ($this->rbacreview->isGlobalRole($role_id)) {
                    $this->pushError('mail_to_global_roles_not_allowed', [$this->address->getMailbox()]);
                    return false;
                }
            }
        }

        if ($role_ids === []) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        }

        if (count($role_ids) > 1) {
            $this->pushError('mail_multiple_role_recipients_found', [
                $this->address->getMailbox(),
                implode(',', $role_ids),
            ]);
            return false;
        }

        return true;
    }

    public function resolve(): array
    {
        $usr_ids = [];

        $role_ids = $this->getRoleIdsByAddress($this->address);

        if ($role_ids !== []) {
            $this->logger->debug(sprintf(
                "Found the following role ids for address '%s': %s",
                $this->address,
                implode(', ', array_unique($role_ids))
            ));

            foreach ($role_ids as $role_id) {
                foreach ($this->rbacreview->assignedUsers($role_id) as $usr_id) {
                    $usr_ids[] = $usr_id;
                }
            }

            if ($usr_ids !== []) {
                $this->logger->debug(sprintf(
                    "Found the following user ids for roles determined by address '%s': %s",
                    $this->address,
                    implode(', ', array_unique($usr_ids))
                ));
            } else {
                $this->logger->debug(sprintf(
                    "Did not find any assigned users for roles determined by '%s'",
                    $this->address
                ));
            }
        } else {
            $this->logger->debug(sprintf(
                "Did not find any role (and user ids) for address '%s'",
                $this->address
            ));
        }

        return array_unique($usr_ids);
    }
}
