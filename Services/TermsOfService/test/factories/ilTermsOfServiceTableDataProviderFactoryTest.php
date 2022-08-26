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
 * Class ilTermsOfServiceTableDataProviderFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTableDataProviderFactoryTest extends ilTermsOfServiceBaseTest
{
    public function testInstanceCanBeCreated(): ilTermsOfServiceTableDataProviderFactory
    {
        $factory = new ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(ilTermsOfServiceTableDataProviderFactory::class, $factory);

        return $factory;
    }

    /**
     * @depends           testInstanceCanBeCreated
     */
    public function testExceptionIsRaisedWhenUnsupportedProviderIsRequested(
        ilTermsOfServiceTableDataProviderFactory $factory
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $factory->getByContext('PHP unit');
    }

    /**
     * @depends           testInstanceCanBeCreated
     */
    public function testFactoryShouldReturnDatabaseAdapterWhenDatabaseAdapterIsSet(
        ilTermsOfServiceTableDataProviderFactory $factory
    ): void {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $factory->setDatabaseAdapter($db);

        $this->assertSame($db, $factory->getDatabaseAdapter());
    }

    /**
     * @depends           testInstanceCanBeCreated
     */
    public function testExceptionIsRaisedWhenAcceptanceHistoryProviderIsRequestedWithoutCompleteFactoryConfiguration(
        ilTermsOfServiceTableDataProviderFactory $factory
    ): void {
        $this->expectException(ilTermsOfServiceMissingDatabaseAdapterException::class);

        $factory->setDatabaseAdapter(null);
        $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);
    }

    /**
     * @depends           testInstanceCanBeCreated
     */
    public function testFactoryShouldReturnAcceptanceHistoryProviderWhenRequested(
        ilTermsOfServiceTableDataProviderFactory $factory
    ): void {
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->assertInstanceOf(
            ilTermsOfServiceAcceptanceHistoryProvider::class,
            $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY)
        );
    }
}
