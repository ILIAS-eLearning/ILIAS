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
 * Class ilMailLoginOrEmailAddressAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLoginOrEmailAddressAddressType extends ilBaseMailAddressType
{
    public function __construct(
        ilMailAddressTypeHelper $typeHelper,
        ilMailAddress $address,
        ilLogger $logger,
        protected ilRbacSystem $rbacsystem
    ) {
        parent::__construct($typeHelper, $address, $logger);
    }

    protected function isValid(int $senderId): bool
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

    public function resolve(): array
    {
        if ($this->address->getHost() === $this->typeHelper->getInstallationHost()) {
            $address = $this->address->getMailbox();
        } else {
            $address = (string) $this->address;
        }

        $usrIds = array_filter([
            $this->typeHelper->getUserIdByLogin($address),
        ]);

        if ($usrIds !== []) {
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
