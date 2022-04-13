<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTest extends ilMailBaseTest
{
    private const LOCAL_PART = 'phpunit';
    private const DOMAIN_PART = 'ilias.de';

    public function testInstanceCanBeCreated() : ilMailAddress
    {
        $address = new ilMailAddress(self::LOCAL_PART, self::DOMAIN_PART);

        $this->assertInstanceOf('ilMailAddress', $address);

        return $address;
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testAddressShouldReturnMailboxAndHost(ilMailAddress $address) : void
    {
        $this->assertSame(self::LOCAL_PART, $address->getMailbox());
        $this->assertSame(self::DOMAIN_PART, $address->getHost());
    }
}
