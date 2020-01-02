<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentTableDataProviderTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTableDataProviderTest extends \ilTermsOfServiceBaseTest
{
    /**
     * @return \ilTermsOfServiceDocumentTableDataProvider
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testDocumentProviderCanBeCreatedByFactory()
    {
        $factory = new \ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());

        $provider = $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_DOCUMENTS);

        $this->assertInstanceOf(\ilTermsOfServiceDocumentTableDataProvider::class, $provider);
        $this->assertInstanceOf(\ilTermsOfServiceTableDataProvider::class, $provider);

        return $provider;
    }

    /**
     * @depends testDocumentProviderCanBeCreatedByFactory
     * @param ilTermsOfServiceDocumentTableDataProvider $provider
     */
    public function testListOfDocumentsCanBeRetrieved(\ilTermsOfServiceDocumentTableDataProvider $provider)
    {
        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(\arConnector::class)->getMock();

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
            ->willReturnCallback(function () {
                return [];
            });

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);
        \arConnectorMap::register(new \ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $data = $provider->getList([], []);

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('cnt', $data);
        $this->assertCount(3, $data['items']);
        $this->assertEquals(3, $data['cnt']);
    }
}
