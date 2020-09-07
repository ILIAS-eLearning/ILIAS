<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTest extends \ilMailBaseTest
{
    const LOCAL_PART = 'phpunit';
    const DOMAIN_PART = 'ilias.de';

    /**
     * @return \ilMailAddress
     */
    public function testInstanceCanBeCreated()
    {
        $address = new \ilMailAddress(self::LOCAL_PART, self::DOMAIN_PART);

        $this->assertInstanceOf('ilMailAddress', $address);

        return $address;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param \ilMailAddress $address
     */
    public function testAddressShouldReturnMailboxAndHost(\ilMailAddress $address)
    {
        $this->assertEquals($address->getMailbox(), self::LOCAL_PART);
        $this->assertEquals($address->getHost(), self::DOMAIN_PART);
    }
}
