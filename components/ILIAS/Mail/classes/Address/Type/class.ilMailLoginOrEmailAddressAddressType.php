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

class ilMailLoginOrEmailAddressAddressType extends ilBaseMailAddressType
{
    public function __construct(
        ilMailAddressTypeHelper $type_helper,
        ilMailAddress $address,
        ilLogger $logger,
        protected ilRbacSystem $rbacsystem
    ) {
        parent::__construct($type_helper, $address, $logger);
    }

    protected function isValid(int $sender_id): bool
    {
        if ($this->address->getHost() === $this->type_helper->getInstallationHost()) {
            $usr_id = $this->type_helper->getUserIdByLogin($this->address->getMailbox());
        } else {
            $usr_id = false;
        }

        if (!$usr_id && $this->address->getHost() === $this->type_helper->getInstallationHost()) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        }

        if (
            $usr_id &&
            $this->type_helper->receivesInternalMailsOnly($usr_id) &&
            !$this->rbacsystem->checkAccessOfUser(
                $usr_id,
                'internal_mail',
                $this->type_helper->getGlobalMailSystemId()
            )
        ) {
            $this->logger->debug(sprintf(
                "Address '%s' not valid. Found id %s, " .
                "but user can't use mail system and wants to receive emails only internally.",
                $this->address->getMailbox(),
                $usr_id
            ));
            $this->pushError('user_cant_receive_mail', [$this->address->getMailbox()]);
            return false;
        }

        return true;
    }

    public function resolve(): array
    {
        if ($this->address->getHost() === $this->type_helper->getInstallationHost()) {
            $address = $this->address->getMailbox();
        } else {
            $address = (string) $this->address;
        }

        $usr_ids = array_filter([
            $this->type_helper->getUserIdByLogin($address),
        ]);

        if ($usr_ids !== []) {
            $this->logger->debug(sprintf(
                "Found the following user ids for address (login) '%s': %s",
                $address,
                implode(', ', array_unique($usr_ids))
            ));
        } elseif ($address !== '') {
            $this->logger->debug(sprintf(
                "Did not find any user account for address (login) '%s'",
                $address
            ));
        }

        return $usr_ids;
    }
}
