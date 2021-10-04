<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressListImpl
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressListImpl implements ilMailAddressList
{
    /** @var ilMailAddress[] */
    protected array $addresses = [];

    /**
     * @param ilMailAddress[] $addresses
     */
    public function __construct(array $addresses)
    {
        // Ensure valid types in array
        array_walk($addresses, static function (ilMailAddress $address) : void {
        });

        $this->addresses = $addresses;
    }

    public function value() : array
    {
        return $this->addresses;
    }
}
