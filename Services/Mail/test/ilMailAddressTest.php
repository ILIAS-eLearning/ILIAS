<?php declare(strict_types=1);

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
