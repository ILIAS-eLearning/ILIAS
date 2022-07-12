<?php declare(strict_types=1);

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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueRepositoryTest extends ilCertificateBaseTestCase
{
    public function testEntryCanBeAddedToQueue() : void
    {
        $databaseMock = $this->createMock(ilDBInterface::class);

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
                [
                    'id' => ['integer', 20],
                    'obj_id' => ['integer', 10],
                    'usr_id' => ['integer', 500],
                    'adapter_class' => ['text', 'SomeClass'],
                    'state' => ['text', 'SomeState'],
                    'started_timestamp' => ['integer', $timestamp],
                    'template_id' => ['integer', 10000]
                ]
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
        $databaseMock = $this->createMock(ilDBInterface::class);

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
        $databaseMock = $this->createMock(ilDBInterface::class);

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
                [
                    'id' => 10,
                    'obj_id' => 100,
                    'usr_id' => 5000,
                    'adapter_class' => 'SomeClass',
                    'state' => 'SomeState',
                    'template_id' => 1000,
                    'started_timestamp' => 123456789
                ],
                [
                    'id' => 20,
                    'obj_id' => 100,
                    'usr_id' => 5000,
                    'adapter_class' => 'SomeClass',
                    'state' => 'SomeState',
                    'template_id' => 1000,
                    'started_timestamp' => 123456789
                ]
            );

        $repository = new ilCertificateQueueRepository($databaseMock, $loggerMock);

        $entries = $repository->getAllEntriesFromQueue();

        $this->assertSame(10, $entries[0]->getId());
        $this->assertSame(20, $entries[1]->getId());
    }
}
