<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailLoginOrEmailAddressAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailLoginOrEmailAddressAddressType extends \ilBaseMailAddressType
{
    /** @var \ilRbacSystem */
    protected $rbacsystem;

    /**
     * ilMailMailingListAddressType constructor.
     * @param \ilMailAddressTypeHelper $typeHelper
     * @param \ilMailAddress           $address
     * @param \ilLogger                $logger
     * @param \ilRbacSystem            $rbacsystem
     */
    public function __construct(
        \ilMailAddressTypeHelper $typeHelper,
        \ilMailAddress $address,
        \ilLogger $logger,
        \ilRbacSystem $rbacsystem
    ) {
        parent::__construct($typeHelper, $address, $logger);

        $this->rbacsystem = $rbacsystem;
    }

    /**
     * @inheritdoc
     */
    protected function isValid(int $senderId) : bool
    {
        if ($this->address->getHost() == $this->typeHelper->getInstallationHost()) {
            $usrId = $this->typeHelper->getUserIdByLogin($this->address->getMailbox());
        } else {
            $usrId = false;
        }

        if (!$usrId && $this->address->getHost() == $this->typeHelper->getInstallationHost()) {
            $this->pushError('mail_recipient_not_found', [$this->address->getMailbox()]);
            return false;
        }

        if ($usrId && !$this->rbacsystem->checkAccessOfUser(
            $usrId,
            'internal_mail',
            $this->typeHelper->getGlobalMailSystemId()
        )) {
            if ($this->typeHelper->receivesInternalMailsOnly($usrId)) {
                $this->logger->debug(sprintf(
                    "Address '%s' not valid. Found id %s, but user can't use mail system and wants to receive emails only internally.",
                    $this->address->getMailbox(),
                    $usrId
                ));
                $this->pushError('user_cant_receive_mail', [$this->address->getMailbox()]);
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
        if ($this->address->getHost() == $this->typeHelper->getInstallationHost()) {
            $address = $this->address->getMailbox();
        } else {
            $address = (string) $this->address;
        }

        $usrIds = array_filter([
            $this->typeHelper->getUserIdByLogin($address)
        ]);

        if (count($usrIds) > 0) {
            $this->logger->debug(sprintf(
                "Found the following user ids for address (login) '%s': %s",
                $address,
                implode(', ', array_unique($usrIds))
            ));
        } else {
            if (strlen($address) > 0) {
                $this->logger->debug(sprintf(
                    "Did not find any user account for address (login) '%s'",
                    $address
                ));
            }
        }

        return $usrIds;
    }
}
