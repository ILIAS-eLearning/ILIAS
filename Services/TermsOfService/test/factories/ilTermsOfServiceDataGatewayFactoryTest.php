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
 * Class ilTermsOfServiceDataGatewayFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactoryTest extends ilTermsOfServiceBaseTest
{
    public function testInstanceCanBeCreated(): void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $this->assertInstanceOf(ilTermsOfServiceDataGatewayFactory::class, $factory);
    }

    public function testExceptionIsRaisedWhenGatewayIsRequestedWithMissingDependencies(): void
    {
        $this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->getByName('PHP Unit');
    }

    public function testExceptionIsRaisedWhenUnknownDataGatewayIsRequested(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());
        $factory->getByName('PHP Unit');
    }

    public function testAcceptanceDatabaseGatewayIsReturnedWhenRequestedByName(): void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            ilTermsOfServiceAcceptanceDatabaseGateway::class,
            $factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway')
        );
    }

    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(): void
    {
        $expected = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($expected);

        $this->assertSame($expected, $factory->getDatabaseAdapter());
    }
}
