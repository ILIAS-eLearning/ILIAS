<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailOnlyExternalAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOnlyExternalAddressList implements \ilMailAddressList
{
    /** @var \ilMailAddressList */
    protected $origin;

    /** @var string */
    protected $installationHost;

    /**
     * ilMailOnlyExternalAddressList constructor.
     * @param \ilMailAddressList $origin
     * @param string $installationHost
     */
    public function __construct(\ilMailAddressList $origin, string $installationHost)
    {
        $this->origin = $origin;
        $this->installationHost = $installationHost;
    }

    /**
     * @inheritdoc
     */
    public function value() : array
    {
        $addresses = $this->origin->value();

        $filteredAddresses = array_filter($addresses, function (\ilMailAddress $address) {
            if (\ilObjUser::_lookupId((string) $address)) {
                // Fixed mantis bug #5875
                return false;
            }

            if ($address->getHost() === $this->installationHost) {
                return false;
            }

            if ('#' === substr($address->getMailbox(), 0, 1)) {
                return false;
            }

            return true;
        });

        return $filteredAddresses;
    }
}
