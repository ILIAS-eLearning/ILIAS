<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDataGatewayFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactoryTest extends ilTermsOfServiceBaseTest
{
    public function testInstanceCanBeCreated() : void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $this->assertInstanceOf(ilTermsOfServiceDataGatewayFactory::class, $factory);
    }

    public function testExceptionIsRaisedWhenGatewayIsRequestedWithMissingDependencies() : void
    {
        $this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->getByName('PHP Unit');
    }

    public function testExceptionIsRaisedWhenUnknownDataGatewayIsRequested() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());
        $factory->getByName('PHP Unit');
    }

    public function testAcceptanceDatabaseGatewayIsReturnedWhenRequestedByName() : void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            ilTermsOfServiceAcceptanceDatabaseGateway::class,
            $factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway')
        );
    }

    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet() : void
    {
        $expected = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($expected);

        $this->assertSame($expected, $factory->getDatabaseAdapter());
    }
}
