<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDataGatewayFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDataGatewayFactoryTest extends ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testInstanceCanBeCreated() : void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $this->assertInstanceOf('ilTermsOfServiceDataGatewayFactory', $factory);
    }

    /**
     *
     */
    public function testExceptionIsRaisedWhenGatewayIsRequestedWithMissingDependencies() : void
    {
        $this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->getByName('PHP Unit');
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ReflectionException
     */
    public function testExceptionIsRaisedWhenUnknownDataGatewayIsRequested() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());
        $factory->getByName('PHP Unit');
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ReflectionException
     */
    public function testAcceptanceDatabaseGatewayIsReturnedWhenRequestedByName() : void
    {
        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            'ilTermsOfServiceAcceptanceDatabaseGateway',
            $factory->getByName('ilTermsOfServiceAcceptanceDatabaseGateway')
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet() : void
    {
        $expected = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $factory = new ilTermsOfServiceDataGatewayFactory();
        $factory->setDatabaseAdapter($expected);

        $this->assertEquals($expected, $factory->getDatabaseAdapter());
    }
}