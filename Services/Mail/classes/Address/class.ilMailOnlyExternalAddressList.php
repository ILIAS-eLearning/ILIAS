<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailOnlyExternalAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOnlyExternalAddressList implements ilMailAddressList
{
    protected ilMailAddressList $origin;
    protected string $installationHost;
    /** @var callable */
    protected $getUsrIdByLoginCallable;

    /**
     * @param callable $getUsrIdByLoginCallable A callable which accepts a string as argument
     *                                          and returns an integer >= 0
     */
    public function __construct(
        ilMailAddressList $origin,
        string $installationHost,
        callable $getUsrIdByLoginCallable
    ) {
        $this->origin = $origin;
        $this->installationHost = $installationHost;
        $this->getUsrIdByLoginCallable = $getUsrIdByLoginCallable;
    }

    public function value() : array
    {
        $addresses = $this->origin->value();

        $filteredAddresses = array_filter($addresses, function (ilMailAddress $address) : bool {
            $c = $this->getUsrIdByLoginCallable;
            if ($c((string) $address)) {
                // Fixed mantis bug #5875
                return false;
            }

            if ($address->getHost() === $this->installationHost) {
                return false;
            }

            if (strpos($address->getMailbox(), '#') === 0) {
                return false;
            }

            return true;
        });

        return $filteredAddresses;
    }
}
