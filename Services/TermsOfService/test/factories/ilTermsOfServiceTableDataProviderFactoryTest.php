<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTableDataProviderFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTableDataProviderFactoryTest extends ilTermsOfServiceBaseTest
{
    /**
     * @return ilTermsOfServiceTableDataProviderFactory
     * @throws ReflectionException
     */
    public function testInstanceCanBeCreated() : ilTermsOfServiceTableDataProviderFactory
    {
        $factory = new ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf('ilTermsOfServiceTableDataProviderFactory', $factory);

        return $factory;
    }

    /**
     * @depends           testInstanceCanBeCreated
     * @param ilTermsOfServiceTableDataProviderFactory $factory
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(
        ilTermsOfServiceTableDataProviderFactory $factory
    ) : void {
        $this->expectException(InvalidArgumentException::class);

        $factory->getByContext('PHP unit');
    }

    /**
     * @param ilTermsOfServiceTableDataProviderFactory $factory
     * @depends           testInstanceCanBeCreated
     * @throws ReflectionException
     */
    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(
        ilTermsOfServiceTableDataProviderFactory $factory
    ) : void {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $factory->setDatabaseAdapter($db);

        $this->assertEquals($db, $factory->getDatabaseAdapter());
    }

    /**
     * @depends           testInstanceCanBeCreated
     * @param ilTermsOfServiceTableDataProviderFactory $factory
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testExceptionIsRaisedWhenAcceptanceHistoryProviderIsRequestedWithoutCompleteFactoryConfiguration(
        ilTermsOfServiceTableDataProviderFactory $factory
    ) : void {
        $this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory->setDatabaseAdapter(null);
        $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);
    }

    /**
     * @param ilTermsOfServiceTableDataProviderFactory $factory
     * @depends           testInstanceCanBeCreated
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ReflectionException
     */
    public function testFactoryShouldReturnAcceptanceHistoryProviderWhenRequested(
        ilTermsOfServiceTableDataProviderFactory $factory
    ) : void {
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            'ilTermsOfServiceAcceptanceHistoryProvider',
            $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY)
        );
    }
}
