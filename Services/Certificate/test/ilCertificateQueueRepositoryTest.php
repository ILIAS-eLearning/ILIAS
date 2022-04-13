<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueRepositoryTest extends ilCertificateBaseTestCase
{
    public function testEntryCanBeAddedToQueue() : void
    {
        $databaseMock = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $loggerMock = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $timestamp = time();

        $databaseMock->expects($this->once())
            ->method('nextId')
            ->willReturn(20);

        $loggerMock->expects($this->atLeastOnce())
            ->method('info');

        $loggerMock->expects($this->atLeastOnce())
            ->method('debug');

        $databaseMock->expects($this->once())
            ->method('insert')
            ->with(
                'il_cert_cron_queue',
                array(
                    'id' => array('integer', 20),
                    'obj_id' => array('integer', 10),
                    'usr_id' => array('integer', 500),
                    'adapter_class' => array('text', 'SomeClass'),
                    'state' => array('text', 'SomeState'),
                    'started_timestamp' => array('integer', $timestamp),
                    'template_id' => array('integer', 10000)
                )
            );

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $queueEntry = new ilCertificateQueueEntry(
            10,
            500,
            'SomeClass',
            'SomeState',
            10000,
            $timestamp
        );

        $repository->addToQueue($queueEntry);
    }

    public function testRemoveFromQueue() : void
    {
        $databaseMock = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $loggerMock = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock->expects($this->atLeastOnce())
            ->method('info');

        $databaseMock->expects($this->once())
            ->method('quote')
            ->with(30, 'integer')
            ->willReturn('30');

        $databaseMock->expects($this->once())
            ->method('manipulate')
            ->with('DELETE FROM il_cert_cron_queue WHERE id = 30');

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $repository->removeFromQueue(30);
    }

    public function testFetchAllEntriesFromQueue() : void
    {
        $databaseMock = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $loggerMock = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock->expects($this->atLeastOnce())
            ->method('info');

        $loggerMock->expects($this->atLeastOnce())
            ->method('debug');

        $databaseMock->expects($this->once())
            ->method('query');

        $databaseMock->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 10,
                    'obj_id' => 100,
                    'usr_id' => 5000,
                    'adapter_class' => 'SomeClass',
                    'state' => 'SomeState',
                    'template_id' => 1000,
                    'started_timestamp' => 123456789
                ),
                array(
                    'id' => 20,
                    'obj_id' => 100,
                    'usr_id' => 5000,
                    'adapter_class' => 'SomeClass',
                    'state' => 'SomeState',
                    'template_id' => 1000,
                    'started_timestamp' => 123456789
                )
            );

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $entries = $repository->getAllEntriesFromQueue();

        $this->assertSame(10, $entries[0]->getId());
        $this->assertSame(20, $entries[1]->getId());
    }
}
