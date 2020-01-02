<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailRoleAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailRoleAddressType extends \ilBaseMailAddressType
{
    /** @var \ilRbacSystem */
    protected $rbacsystem;

    /** @var \ilRbacReview */
    protected $rbacreview;

    /** @var \ilRoleMailboxSearch */
    protected $roleMailboxSearch;

    /**
     * ilMailRoleAddressType constructor.
     * @param \ilMailAddressTypeHelper $typeHelper
     * @param \ilMailAddress           $address
     * @param \ilRoleMailboxSearch     $roleMailboxSearch
     * @param \ilLogger                $logger
     * @param \ilRbacSystem            $rbacsystem
     * @param \ilRbacReview            $rbacreview
     */
    public function __construct(
        \ilMailAddressTypeHelper $typeHelper,
        \ilMailAddress $address,
        \ilRoleMailboxSearch $roleMailboxSearch,
        \ilLogger $logger,
        \ilRbacSystem $rbacsystem,
        \ilRbacReview $rbacreview
    ) {
        parent::__construct($typeHelper, $address, $logger);

        $this->roleMailboxSearch = $roleMailboxSearch;
        $this->rbacsystem = $rbacsystem;
        $this->rbacreview = $rbacreview;
    }

    /**
     * @param \ilMailAddress $address
     * @return int[]
     */
    protected function getRoleIdsByAddress(\ilMailAddress $address) : array
    {
        $combinedAddress = (string) $address;

        $roleIds = $this->roleMailboxSearch->searchRoleIdsByAddressString($combinedAddress);

        return $roleIds;
    }

    /**
     * @param int $senderId
     * @return bool
     */
    protected function maySendToGlobalRole(int $senderId) : bool
    {
        if ($senderId == ANONYMOUS_USER_ID) {
            return true;
        }

        $maySendToGlobalRoles = $this->rbacsystem->checkAccessOfUser(
            $senderId,
            'mail_to_global_roles',
            $this->typeHelper->getGlobalMailSystemId()
        );

        return $maySendToGlobalRoles;
    }

    /**
     * @inheritdoc
     */
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

        if (count($roleIds) == 0) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        } else {
            if (count($roleIds) > 1) {
                $this->pushError('mail_multiple_role_recipients_found', [
                    $this->address->getMailbox(),
                    implode(',', $roleIds),
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function resolve() : array
    {
        $usrIds = [];

        $roleIds = $this->getRoleIdsByAddress($this->address);

        if (count($roleIds) > 0) {
            $this->logger->debug(sprintf(
                "Found the following role ids for address '%s': %s",
                (string) $this->address,
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
                    (string) $this->address,
                    implode(', ', array_unique($usrIds))
                ));
            } else {
                $this->logger->debug(sprintf(
                    "Did not find any assigned users for roles determined by '%s'",
                    (string) $this->address
                ));
            }
        } else {
            $this->logger->debug(sprintf(
                "Did not find any role (and user ids) for address '%s'",
                (string) $this->address
            ));
        }

        return array_unique($usrIds);
    }
}
