<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryProviderTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryProviderTest extends \ilTermsOfServiceBaseTest
{
    /**
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function testHistoryProviderCanBeCreatedByFactory()
    {
        $factory = new \ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($this->getMockBuilder(\ilDBInterface::class)->getMock());

        $provider = $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

        $this->assertInstanceOf(\ilTermsOfServiceAcceptanceHistoryProvider::class, $provider);
        $this->assertInstanceOf(\ilTermsOfServiceTableDatabaseDataProvider::class, $provider);
        $this->assertInstanceOf(\ilTermsOfServiceTableDataProvider::class, $provider);
    }

    /**
     *
     */
    public function testListCanBeRetrieved()
    {
        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $result = $this->getMockBuilder(\ilDBStatement::class)->getMock();

        $factory = new \ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($database);

        $provider = $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

        $database
            ->expects($this->exactly(2))
            ->method('query')
            ->with($this->stringContains('SELECT'))
            ->will($this->returnValue($result));

        $database
            ->expects($this->exactly(4))
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls(['phpunit'], ['phpunit'], [], ['cnt' => 2]));

        $database
            ->expects($this->any())
            ->method('like')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                $this->isType('string')
            )->will($this->returnArgument(2));

        $database
            ->expects($this->any())
            ->method('quote')
            ->with($this->anything(), $this->isType('string'))
            ->will($this->returnArgument(0));

        $data = $provider->getList(
            [
                'limit' => 5,
                'order_field' => 'ts'
            ],
            [
                'query' => 'phpunit',
                'period' => [
                    'start' => time(),
                    'end' => time()
                ]
            ]
        );

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('cnt', $data);
        $this->assertCount(2, $data['items']);
        $this->assertEquals(2, $data['cnt']);
    }

    /**
     *
     */
    public function testRetrievingListThrowsExceptionsWhenInvalidArgumentsArePassed()
    {
        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();

        $factory = new \ilTermsOfServiceTableDataProviderFactory();
        $factory->setDatabaseAdapter($database);

        $provider = $factory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

        try {
            $provider->getList(array('limit' => 'phpunit'), array());
            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $provider->getList(array('limit' => 5, 'offset' => 'phpunit'), array());
            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $provider->getList(array('order_field' => 'phpunit'), array());
            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $provider->getList(array('order_field' => 5), array());
            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $provider->getList(array('order_field' => 'ts', 'order_direction' => 'phpunit'), array());
            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
        }
    }
}
