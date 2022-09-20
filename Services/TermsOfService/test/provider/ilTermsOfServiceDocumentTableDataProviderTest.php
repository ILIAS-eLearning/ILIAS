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
 * Class ilTermsOfServiceDocumentTableDataProviderTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTableDataProviderTest extends ilTermsOfServiceBaseTest
{
    public function testDocumentProviderCanBeCreatedByFactory(): ilTermsOfServiceTableDataProvider
    {
        $factory = new ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(ilDBInterface::class)->getMock());

        $provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_DOCUMENTS);

        $this->assertInstanceOf(ilTermsOfServiceDocumentTableDataProvider::class, $provider);
        $this->assertInstanceOf(ilTermsOfServiceTableDataProvider::class, $provider);

        return $provider;
    }

    /**
     * @depends testDocumentProviderCanBeCreatedByFactory
     */
    public function testListOfDocumentsCanBeRetrieved(ilTermsOfServiceDocumentTableDataProvider $provider): void
    {
        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $documentData = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
            [
                'id' => 3,
            ]
        ];

        $documentConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturn($documentData);

        $criterionConnector
            ->expects($this->exactly(count($documentData)))
            ->method('readSet')
            ->willReturnCallback(function (): array {
                return [];
            });

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register(new ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $data = $provider->getList([], []);

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('cnt', $data);
        $this->assertCount(3, $data['items']);
        $this->assertSame(3, $data['cnt']);
    }
}
