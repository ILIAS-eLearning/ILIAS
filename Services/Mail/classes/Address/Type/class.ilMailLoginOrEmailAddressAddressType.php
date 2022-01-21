<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailLoginOrEmailAddressAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLoginOrEmailAddressAddressType extends ilBaseMailAddressType
{
    protected ilRbacSystem $rbacsystem;

    public function __construct(
        ilMailAddressTypeHelper $typeHelper,
        ilMailAddress $address,
        ilLogger $logger,
        ilRbacSystem $rbacsystem
    ) {
        parent::__construct($typeHelper, $address, $logger);
        $this->rbacsystem = $rbacsystem;
    }

    protected function isValid(int $senderId) : bool
    {
        if ($this->address->getHost() === $this->typeHelper->getInstallationHost()) {
            $usrId = $this->typeHelper->getUserIdByLogin($this->address->getMailbox());
        } else {
            $usrId = false;
        }

        if (!$usrId && $this->address->getHost() === $this->typeHelper->getInstallationHost()) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        }

        if (
            $usrId &&
            $this->typeHelper->receivesInternalMailsOnly($usrId) &&
            !$this->rbacsystem->checkAccessOfUser(
                $usrId,
                'internal_mail',
                $this->typeHelper->getGlobalMailSystemId()
            )
        ) {
            $this->logger->debug(sprintf(
                "Address '%s' not valid. Found id %s, " .
                "but user can't use mail system and wants to receive emails only internally.",
                $this->address->getMailbox(),
                $usrId
            ));
            $this->pushError('user_cant_receive_mail', [$this->address->getMailbox()]);
            return false;
        }

        return true;
    }

    public function resolve() : array
    {
        if ($this->address->getHost() === $this->typeHelper->getInstallationHost()) {
            $address = $this->address->getMailbox();
        } else {
            $address = (string) $this->address;
        }

        $usrIds = array_filter([
            $this->typeHelper->getUserIdByLogin($address),
        ]);

        if (count($usrIds) > 0) {
            $this->logger->debug(sprintf(
                "Found the following user ids for address (login) '%s': %s",
                $address,
                implode(', ', array_unique($usrIds))
            ));
        } elseif ($address !== '') {
            $this->logger->debug(sprintf(
                "Did not find any user account for address (login) '%s'",
                $address
            ));
        }

        return $usrIds;
    }
}
