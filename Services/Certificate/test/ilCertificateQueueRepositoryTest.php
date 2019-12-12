<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testEntryCanBeAddedToQueue()
    {
        $databaseMock = $this->getMockBuilder('ilDBInterface')
            ->getMock();

        $loggerMock = $this->getMockBuilder('ilLogger')
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
                    'id'                => array('integer', 20),
                    'obj_id'            => array('integer', 10),
                    'usr_id'            => array('integer', 500),
                    'adapter_class'     => array('text', 'SomeClass'),
                    'state'             => array('text', 'SomeState'),
                    'started_timestamp' => array('integer', $timestamp),
                    'template_id'       => array('integer', 10000)
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

    public function testRemoveFromQueue()
    {
        $databaseMock = $this->getMockBuilder('ilDBInterface')
            ->getMock();

        $loggerMock = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock->expects($this->atLeastOnce())
            ->method('info');

        $databaseMock->expects($this->once())
            ->method('quote')
            ->with(30, 'integer')
            ->willReturn(30);

        $databaseMock->expects($this->once())
            ->method('manipulate')
            ->with('DELETE FROM il_cert_cron_queue WHERE id = 30');

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $repository->removeFromQueue(30);
    }

    public function testFetchAllEntriesFromQueue()
    {
        $databaseMock = $this->getMockBuilder('ilDBInterface')
            ->getMock();

        $loggerMock = $this->getMockBuilder('ilLogger')
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
                    'id'                => 10,
                    'obj_id'            => 100,
                    'usr_id'            => 5000,
                    'adapter_class'     => 'SomeClass',
                    'state'             => 'SomeState',
                    'template_id'       => 1000,
                    'started_timestamp' => 123456789
                ),
                array(
                    'id'                => 20,
                    'obj_id'            => 100,
                    'usr_id'            => 5000,
                    'adapter_class'     => 'SomeClass',
                    'state'             => 'SomeState',
                    'template_id'       => 1000,
                    'started_timestamp' => 123456789
                )
            );

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $entries = $repository->getAllEntriesFromQueue();

        $this->assertEquals(10, $entries[0]->getId());
        $this->assertEquals(20, $entries[1]->getId());
    }
}
