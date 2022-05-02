<?php declare(strict_types=1);

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
 * Class ilMailRoleAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRoleAddressType extends ilBaseMailAddressType
{
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilRoleMailboxSearch $roleMailboxSearch;

    public function __construct(
        ilMailAddressTypeHelper $typeHelper,
        ilMailAddress $address,
        ilRoleMailboxSearch $roleMailboxSearch,
        ilLogger $logger,
        ilRbacSystem $rbacsystem,
        ilRbacReview $rbacreview
    ) {
        parent::__construct($typeHelper, $address, $logger);

        $this->roleMailboxSearch = $roleMailboxSearch;
        $this->rbacsystem = $rbacsystem;
        $this->rbacreview = $rbacreview;
    }

    /**
     * @return int[]
     */
    protected function getRoleIdsByAddress(ilMailAddress $address) : array
    {
        $combinedAddress = (string) $address;

        $roleIds = $this->roleMailboxSearch->searchRoleIdsByAddressString($combinedAddress);

        return $roleIds;
    }

    protected function maySendToGlobalRole(int $senderId) : bool
    {
        if ($senderId === ANONYMOUS_USER_ID) {
            return true;
        }

        $maySendToGlobalRoles = $this->rbacsystem->checkAccessOfUser(
            $senderId,
            'mail_to_global_roles',
            $this->typeHelper->getGlobalMailSystemId()
        );

        return $maySendToGlobalRoles;
    }

    protected function isValid(int $senderId) : bool
    {
        $roleIds = $this->getRoleIdsByAddress($this->address);
        if (!$this->maySendToGlobalRole($senderId)) {
            foreach ($roleIds as $roleId) {
                if ($this->rbacreview->isGlobalRole($roleId)) {
                    $this->pushError('mail_to_global_roles_not_allowed', [$this->address->getMailbox()]);
                    return false;
                }
            }
        }

        if ($roleIds === []) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        }

        if (count($roleIds) > 1) {
            $this->pushError('mail_multiple_role_recipients_found', [
                $this->address->getMailbox(),
                implode(',', $roleIds),
            ]);
            return false;
        }

        return true;
    }

    public function resolve() : array
    {
        $usrIds = [];

        $roleIds = $this->getRoleIdsByAddress($this->address);

        if (count($roleIds) > 0) {
            $this->logger->debug(sprintf(
                "Found the following role ids for address '%s': %s",
                $this->address,
                implode(', ', array_unique($roleIds))
            ));

            foreach ($roleIds as $roleId) {
                foreach ($this->rbacreview->assignedUsers($roleId) as $usrId) {
                    $usrIds[] = $usrId;
                }
            }

            if (count($usrIds) > 0) {
                $this->logger->debug(sprintf(
                    "Found the following user ids for roles determined by address '%s': %s",
                    $this->address,
                    implode(', ', array_unique($usrIds))
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

        return array_unique($usrIds);
    }
}
