<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressListImpl
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressListImpl implements ilMailAddressList
{
    /** @var \ilMailAddress */
    protected $addresses = [];

    /**
     * ilMailAddressListImpl constructor.
     * @param \ilMailAddress[] $addresses
     */
    public function __construct(array $addresses)
    {
        // Ensure valid types in array
        array_walk($addresses, function (\ilMailAddress $address) {
        });

        $this->addresses = $addresses;
    }

    /**
     * @inheritdoc
     */
    public function value() : array
    {
        return $this->addresses;
    }
}
