<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTableDataProviderFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTableDataProviderFactoryTest extends \ilTermsOfServiceBaseTest
{
    /**
     * @return \ilTermsOfServiceTableDataProviderFactory
     */
    public function testInstanceCanBeCreated()
    {
        $factory = new \ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());

        $this->assertInstanceOf('ilTermsOfServiceTableDataProviderFactory', $factory);

        return $factory;
    }

    /**
     * @depends           testInstanceCanBeCreated
     * @param \ilTermsOfServiceTableDataProviderFactory $factory
     * @expectedException \InvalidArgumentException
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(
        \ilTermsOfServiceTableDataProviderFactory $factory
    ) {
        $this->assertException(\InvalidArgumentException::class);

        $factory->getByContext('PHP unit');
    }

    /**
     * @param \ilTermsOfServiceTableDataProviderFactory $factory
     * @depends           testInstanceCanBeCreated
     */
    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(
        \ilTermsOfServiceTableDataProviderFactory $factory
    ) {
        $db = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $factory->setDatabaseAdapter($db);

        $this->assertEquals($db, $factory->getDatabaseAdapter());
    }

    /**
     * @depends           testInstanceCanBeCreated
     * @param \ilTermsOfServiceTableDataProviderFactory $factory
     * @expectedException \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testExceptionIsRaisedWhenAcceptanceHistoryProviderIsRequestedWithoutCompleteFactoryConfiguration(
        \ilTermsOfServiceTableDataProviderFactory $factory
    ) {
        $this->assertException(\ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory->setDatabaseAdapter(null);
        $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);
    }

    /**
     * @param \ilTermsOfServiceTableDataProviderFactory $factory
     * @depends           testInstanceCanBeCreated
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testFactoryShouldReturnAcceptanceHistoryProviderWhenRequested(
        \ilTermsOfServiceTableDataProviderFactory $factory
    ) {
        $factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            'ilTermsOfServiceAcceptanceHistoryProvider',
            $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY)
        );
    }
}
